<?php

namespace App\Services;

use App\Models\Vocabulary;
use App\Models\VocabularyProgress;
use App\Models\VocabularyReviewSchedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VocabularyReviewScheduler
{
    public const DAILY_NEW_LIMIT = 5;

    public const QUEUE_LIMIT = 20;

    private const MAX_INTERVAL_DAYS = 3650;

    /** @return Collection<int, Vocabulary> */
    public function queue(?Carbon $at = null): Collection
    {
        $at ??= now();

        $scheduled = VocabularyReviewSchedule::query()
            ->with(['progress.vocabulary.topic', 'progress.vocabulary.progress.reviewSchedule'])
            ->where('due_at', '<=', $at)
            ->whereHas('progress.vocabulary', fn (Builder $query) => $this->activeVocabulary($query))
            ->orderBy('due_at')
            ->limit(self::QUEUE_LIMIT)
            ->get()
            ->map(fn (VocabularyReviewSchedule $schedule): Vocabulary => $schedule->progress->vocabulary)
            ->values();

        $remaining = self::QUEUE_LIMIT - $scheduled->count();
        if ($remaining <= 0) {
            return $scheduled;
        }

        $legacy = $this->activeVocabularyQuery()
            ->with(['topic', 'progress.reviewSchedule'])
            ->whereHas('progress', fn (Builder $query) => $query
                ->whereIn('state', ['learning', 'review'])
                ->whereDoesntHave('reviewSchedule'))
            ->orderBy('term')
            ->limit($remaining)
            ->get();

        $remaining -= $legacy->count();
        if ($remaining <= 0) {
            return $scheduled->concat($legacy)->values();
        }

        $new = $this->activeVocabularyQuery()
            ->with(['topic', 'progress.reviewSchedule'])
            ->where(function (Builder $query): void {
                $query->whereDoesntHave('progress')
                    ->orWhereHas('progress', fn (Builder $progress) => $progress
                        ->where('state', 'new')
                        ->whereDoesntHave('reviewSchedule'));
            })
            ->orderBy('term')
            ->limit(min(self::DAILY_NEW_LIMIT, $remaining))
            ->get();

        return $scheduled->concat($legacy)->concat($new)->values();
    }

    /** @return array{due: int, new: int} */
    public function summary(?Carbon $at = null): array
    {
        $at ??= now();

        $scheduledDue = VocabularyReviewSchedule::query()
            ->where('due_at', '<=', $at)
            ->whereHas('progress.vocabulary', fn (Builder $query) => $this->activeVocabulary($query))
            ->count();

        $legacyDue = $this->activeVocabularyQuery()
            ->whereHas('progress', fn (Builder $query) => $query
                ->whereIn('state', ['learning', 'review'])
                ->whereDoesntHave('reviewSchedule'))
            ->count();

        $new = $this->activeVocabularyQuery()
            ->where(function (Builder $query): void {
                $query->whereDoesntHave('progress')
                    ->orWhereHas('progress', fn (Builder $progress) => $progress
                        ->where('state', 'new')
                        ->whereDoesntHave('reviewSchedule'));
            })
            ->count();

        return [
            'due' => $scheduledDue + $legacyDue,
            'new' => $new,
        ];
    }

    public function recordRating(Vocabulary $vocabulary, string $rating, ?Carbon $reviewedAt = null): VocabularyReviewSchedule
    {
        if (! in_array($rating, VocabularyReviewSchedule::RATINGS, true)) {
            throw new InvalidArgumentException('Unsupported vocabulary review rating.');
        }

        $reviewedAt ??= now();

        return DB::transaction(function () use ($vocabulary, $rating, $reviewedAt): VocabularyReviewSchedule {
            Vocabulary::query()->whereKey($vocabulary->id)->lockForUpdate()->firstOrFail();

            $progress = VocabularyProgress::query()->firstOrCreate(
                ['vocabulary_id' => $vocabulary->id],
                ['state' => 'new'],
            );
            $progress = VocabularyProgress::query()->lockForUpdate()->findOrFail($progress->id);
            $schedule = VocabularyReviewSchedule::query()
                ->where('vocabulary_progress_id', $progress->id)
                ->lockForUpdate()
                ->first();

            $currentInterval = $schedule?->interval_days ?? 0;
            $interval = $this->nextInterval($currentInterval, $rating);
            $dueAt = $rating === 'again'
                ? $reviewedAt->copy()->addMinutes(10)
                : $reviewedAt->copy()->addDays($interval);

            $progress->fill([
                'state' => $rating === 'again' ? 'learning' : $this->stateForInterval($interval),
                'correct_count' => $progress->correct_count + ($rating === 'again' ? 0 : 1),
                'incorrect_count' => $progress->incorrect_count + ($rating === 'again' ? 1 : 0),
                'last_reviewed_at' => $reviewedAt,
            ])->save();

            $schedule ??= new VocabularyReviewSchedule([
                'vocabulary_progress_id' => $progress->id,
            ]);
            $schedule->fill([
                'due_at' => $dueAt,
                'interval_days' => $interval,
                'streak' => $rating === 'again' ? 0 : (int) $schedule->streak + 1,
                'lapses' => (int) $schedule->lapses + ($rating === 'again' ? 1 : 0),
                'last_rating' => $rating,
            ])->save();

            return $schedule->load(['progress.vocabulary']);
        });
    }

    private function activeVocabulary(Builder $query): Builder
    {
        return $query
            ->active()
            ->whereHas('topic', fn (Builder $topic) => $topic->active());
    }

    private function activeVocabularyQuery(): Builder
    {
        return $this->activeVocabulary(Vocabulary::query());
    }

    private function nextInterval(int $currentInterval, string $rating): int
    {
        $interval = match ($rating) {
            'again' => 0,
            'hard' => $currentInterval === 0 ? 1 : max($currentInterval + 1, (int) ceil($currentInterval * 1.2)),
            'good' => $currentInterval === 0 ? 1 : max($currentInterval + 1, (int) ceil($currentInterval * 2)),
            'easy' => $currentInterval === 0 ? 4 : max($currentInterval + 2, (int) ceil($currentInterval * 2.5)),
        };

        return min(self::MAX_INTERVAL_DAYS, $interval);
    }

    private function stateForInterval(int $interval): string
    {
        return match (true) {
            $interval >= 21 => 'learned',
            $interval >= 7 => 'review',
            default => 'learning',
        };
    }
}
