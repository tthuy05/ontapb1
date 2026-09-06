<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttemptAnswerRequest;
use App\Http\Requests\AttemptSubmitRequest;
use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Exam;
use App\Models\Exercise;
use App\Models\ExerciseQuestion;
use App\Models\Question;
use App\Services\ExamCompositionService;
use App\Services\ScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttemptController extends Controller
{
    public function __construct(
        private readonly ScoringService $scoring,
        private readonly ExamCompositionService $composition,
    ) {}

    public function storeForExercise(Exercise $exercise): RedirectResponse
    {
        $exercise->load([
            'topic',
            'exerciseQuestions' => fn ($query) => $query->with([
                'question.topic', 'question.passage', 'question.listeningContent', 'question.options',
            ]),
        ]);

        abort_unless($this->exerciseIsStartable($exercise), 404);

        $startedAt = now();
        $attempt = DB::transaction(function () use ($exercise, $startedAt): Attempt {
            $attempt = Attempt::query()->create([
                'exercise_id' => $exercise->id,
                'status' => 'in_progress',
                'started_at' => $startedAt,
                'expires_at' => $exercise->time_limit_seconds === null
                    ? null
                    : $startedAt->copy()->addSeconds($exercise->time_limit_seconds),
                'max_score' => $exercise->exerciseQuestions->sum(fn (ExerciseQuestion $item): float => (float) $item->points),
                'configuration_snapshot' => $this->exerciseSnapshot($exercise),
            ]);

            foreach ($exercise->exerciseQuestions as $item) {
                $question = $item->question;
                $attempt->answers()->create([
                    'question_id' => $question->id,
                    'question_position' => $item->position,
                    'question_snapshot' => $this->questionSnapshot($question),
                    'context_snapshot' => $this->contextSnapshot($question),
                    'max_points' => $item->points,
                    'save_version' => 0,
                ]);
            }

            return $attempt;
        });

        return redirect()->route('attempts.show', $attempt)->with('status', 'Practice attempt started.');
    }

    public function storeForExam(Exam $exam): RedirectResponse
    {
        $this->composition->load($exam);
        abort_unless($exam->status === 'active' && $this->composition->activationErrors($exam) === [], 404);

        $startedAt = now();
        $attempt = DB::transaction(function () use ($exam, $startedAt): Attempt {
            $snapshot = $this->composition->snapshot($exam);
            $attempt = Attempt::query()->create([
                'exam_id' => $exam->id,
                'status' => 'in_progress',
                'started_at' => $startedAt,
                'expires_at' => $exam->time_limit_seconds === null
                    ? null
                    : $startedAt->copy()->addSeconds($exam->time_limit_seconds),
                'max_score' => $exam->sections->sum(
                    fn (\App\Models\ExamSection $section): float => (float) $section->items->sum(
                        fn ($item): float => (float) $item->points,
                    ),
                ),
                'configuration_snapshot' => $snapshot,
            ]);

            foreach ($exam->sections as $section) {
                foreach ($section->items as $item) {
                    $question = $item->question;
                    $attempt->answers()->create([
                        'question_id' => $question->id,
                        'section_position' => $section->position,
                        'question_position' => $item->position,
                        'question_snapshot' => $this->questionSnapshot($question, $item->id),
                        'context_snapshot' => $this->contextSnapshot($question),
                        'max_points' => $item->points,
                        'save_version' => 0,
                    ]);
                }
            }

            return $attempt;
        });

        $firstSection = $exam->sections->first();

        return redirect()->route('attempts.sections.show', [$attempt, $firstSection->position])
            ->with('status', 'Mock exam started.');
    }

    public function show(Attempt $attempt): View|RedirectResponse
    {
        $this->assertSupportedAttempt($attempt);

        if ($attempt->status === 'submitted') {
            return redirect()->route('attempts.result', $attempt);
        }

        if ($attempt->status !== 'in_progress') {
            abort(404);
        }

        if ($attempt->expires_at !== null && now()->greaterThanOrEqualTo($attempt->expires_at)) {
            $this->finalize($attempt, 'deadline');

            return redirect()->route('attempts.result', $attempt);
        }

        if ($attempt->exam_id !== null) {
            $snapshot = is_array($attempt->configuration_snapshot) ? $attempt->configuration_snapshot : [];
            $firstSection = collect($snapshot['sections'] ?? [])->sortBy('position')->first();
            abort_unless(is_array($firstSection), 404);

            return redirect()->route('attempts.sections.show', [$attempt, (int) $firstSection['position']]);
        }

        $attempt->load('answers');

        return view('attempts.show', [
            'attempt' => $attempt,
            'items' => $attempt->answers->map(fn (AttemptAnswer $answer): array => $this->takingItem($answer))->all(),
            'serverNow' => now(),
        ]);
    }

    public function showSection(Attempt $attempt, int $sectionPosition): View|RedirectResponse
    {
        $this->assertSupportedAttempt($attempt);
        abort_unless($attempt->exam_id !== null, 404);

        if ($attempt->status === 'submitted') {
            return redirect()->route('attempts.result', $attempt);
        }
        if ($attempt->status !== 'in_progress') {
            abort(404);
        }
        if ($attempt->expires_at !== null && now()->greaterThanOrEqualTo($attempt->expires_at)) {
            $this->finalize($attempt, 'deadline');

            return redirect()->route('attempts.result', $attempt);
        }

        $snapshot = is_array($attempt->configuration_snapshot) ? $attempt->configuration_snapshot : [];
        $sections = collect($snapshot['sections'] ?? [])->sortBy('position')->values();
        $section = $sections->first(fn (array $item): bool => (int) ($item['position'] ?? -1) === $sectionPosition);
        abort_unless(is_array($section), 404);

        $attempt->load('answers');
        $items = $attempt->answers
            ->where('section_position', $sectionPosition)
            ->map(fn (AttemptAnswer $answer): array => $this->takingItem($answer))
            ->values()
            ->all();
        abort_unless($items !== [], 404);

        $sectionIndex = $sections->search(fn (array $item): bool => (int) ($item['position'] ?? -1) === $sectionPosition);
        $previousSection = $sectionIndex > 0 ? (int) $sections[$sectionIndex - 1]['position'] : null;
        $nextSection = $sectionIndex < $sections->count() - 1 ? (int) $sections[$sectionIndex + 1]['position'] : null;

        return view('attempts.exam-section', [
            'attempt' => $attempt,
            'section' => $section,
            'items' => $items,
            'sectionIndex' => $sectionIndex,
            'sectionCount' => $sections->count(),
            'previousSection' => $previousSection,
            'nextSection' => $nextSection,
            'serverNow' => now(),
        ]);
    }

    public function updateAnswer(
        AttemptAnswerRequest $request,
        Attempt $attempt,
        AttemptAnswer $attemptAnswer,
    ): JsonResponse {
        $this->assertSupportedAttempt($attempt);

        if ($attemptAnswer->attempt_id !== $attempt->id) {
            abort(404);
        }

        $result = DB::transaction(function () use ($request, $attempt, $attemptAnswer): array|JsonResponse {
            $lockedAttempt = Attempt::query()->lockForUpdate()->findOrFail($attempt->id);
            if ($lockedAttempt->status !== 'in_progress') {
                return response()->json(['message' => 'This attempt has already been submitted.'], 409);
            }
            if ($lockedAttempt->expires_at !== null && now()->greaterThanOrEqualTo($lockedAttempt->expires_at)) {
                return response()->json(['message' => 'The practice deadline has passed.', 'deadline_reached' => true], 409);
            }

            $answer = $lockedAttempt->answers()->lockForUpdate()->findOrFail($attemptAnswer->id);
            $expectedVersion = (int) $request->validated('save_version');
            if ($expectedVersion !== (int) $answer->save_version) {
                return response()->json([
                    'message' => 'A newer answer is already saved. Reload this attempt before changing it.',
                    'save_version' => $answer->save_version,
                ], 409);
            }

            $response = $request->validated('response');
            $snapshot = is_array($answer->question_snapshot) ? $answer->question_snapshot : [];
            if (! $this->scoring->isAllowedResponse($response, $snapshot)) {
                throw ValidationException::withMessages(['response' => 'Choose one of the available options.']);
            }

            $normalized = $this->scoring->normalizeResponse($response, $snapshot);
            $answer->update([
                'response' => ['value' => $normalized],
                'answered_at' => now(),
                'save_version' => $answer->save_version + 1,
            ]);

            return [
                'saved_at' => $answer->fresh()->updated_at?->toIso8601String(),
                'save_version' => $answer->save_version,
                'response' => $normalized,
            ];
        });

        return $result instanceof JsonResponse ? $result : response()->json($result);
    }

    public function submit(AttemptSubmitRequest $request, Attempt $attempt): RedirectResponse
    {
        $this->assertSupportedAttempt($attempt);
        $this->saveSubmittedAnswers($attempt, $request->all());
        $deadline = $attempt->expires_at !== null && now()->greaterThanOrEqualTo($attempt->expires_at);
        $this->finalize($attempt, $deadline ? 'deadline' : 'manual');

        return redirect()->route('attempts.result', $attempt)->with('status', 'Practice attempt submitted.');
    }

    private function saveSubmittedAnswers(Attempt $attempt, array $input): void
    {
        DB::transaction(function () use ($attempt, $input): void {
            $lockedAttempt = Attempt::query()->lockForUpdate()->findOrFail($attempt->id);
            if ($lockedAttempt->status !== 'in_progress') {
                return;
            }

            foreach ($lockedAttempt->answers()->lockForUpdate()->get() as $answer) {
                $response = $input['answer_'.$answer->id] ?? null;
                if (! is_string($response) || $response === '') {
                    continue;
                }

                $snapshot = is_array($answer->question_snapshot) ? $answer->question_snapshot : [];
                if (! $this->scoring->isAllowedResponse($response, $snapshot)) {
                    throw ValidationException::withMessages(['answers' => 'One submitted answer is not available for this attempt.']);
                }

                $normalized = $this->scoring->normalizeResponse($response, $snapshot);
                if ($normalized === data_get($answer->response, 'value')) {
                    continue;
                }

                $answer->update([
                    'response' => ['value' => $normalized],
                    'answered_at' => now(),
                    'save_version' => $answer->save_version + 1,
                ]);
            }
        });
    }

    private function finalize(Attempt $attempt, string $reason): void
    {
        DB::transaction(function () use ($attempt, $reason): void {
            $lockedAttempt = Attempt::query()->lockForUpdate()->findOrFail($attempt->id);
            if ($lockedAttempt->status === 'submitted') {
                return;
            }
            if ($lockedAttempt->status !== 'in_progress') {
                abort(409, 'This attempt cannot be finalized.');
            }

            $answers = $lockedAttempt->answers()->lockForUpdate()->get();
            $scoreAwarded = 0.0;
            $maxScore = 0.0;
            $correct = 0;
            $incorrect = 0;
            $unanswered = 0;

            foreach ($answers as $answer) {
                $score = $this->scoring->score($answer);
                $answer->update([
                    'response' => $score['response'] === null ? null : ['value' => $score['response']],
                    'is_correct' => $score['is_correct'],
                    'points_awarded' => $score['points_awarded'],
                    'max_points' => $score['max_points'],
                ]);

                $scoreAwarded += $score['points_awarded'];
                $maxScore += $score['max_points'];
                if (! $score['answered']) {
                    $unanswered++;
                } elseif ($score['is_correct']) {
                    $correct++;
                } else {
                    $incorrect++;
                }
            }

            $submittedAt = now();
            $duration = max(0, $submittedAt->diffInSeconds($lockedAttempt->started_at));
            $lockedAttempt->update([
                'status' => 'submitted',
                'completion_reason' => $reason,
                'submitted_at' => $submittedAt,
                'score_awarded' => round($scoreAwarded, 2),
                'max_score' => round($maxScore, 2),
                'percentage' => $maxScore > 0 ? round(($scoreAwarded / $maxScore) * 100, 2) : 0,
                'correct_count' => $correct,
                'incorrect_count' => $incorrect,
                'unanswered_count' => $unanswered,
                'ungraded_count' => 0,
                'duration_seconds' => $duration,
            ]);
        });
    }

    private function exerciseIsStartable(Exercise $exercise): bool
    {
        if ($exercise->status !== 'active' || ($exercise->topic !== null && $exercise->topic->status !== 'active')) {
            return false;
        }

        if ($exercise->exerciseQuestions->isEmpty()) {
            return false;
        }

        foreach ($exercise->exerciseQuestions as $item) {
            $question = $item->question;
            if (! $question || $question->status !== 'active' || $question->skill !== $exercise->skill) {
                return false;
            }
            if ($question->topic && $question->topic->status !== 'active') {
                return false;
            }
            if ($question->passage_id !== null && (! $question->passage || $question->passage->status !== 'active')) {
                return false;
            }
            if ($question->listening_content_id !== null && (! $question->listeningContent || $question->listeningContent->status !== 'active')) {
                return false;
            }
            $correct = $question->options->where('is_correct', true)->count();
            $validCount = $question->type === 'true_false'
                ? $question->options->count() === 2
                : $question->options->count() >= 2;
            if (! in_array($question->type, Question::TYPES, true) || ! $validCount || $correct !== 1) {
                return false;
            }
        }

        return true;
    }

    private function assertSupportedAttempt(Attempt $attempt): void
    {
        abort_unless(
            ($attempt->exercise_id !== null && $attempt->exam_id === null)
                || ($attempt->exercise_id === null && $attempt->exam_id !== null),
            404,
        );
    }

    private function exerciseSnapshot(Exercise $exercise): array
    {
        return [
            'snapshot_version' => 1,
            'kind' => 'exercise',
            'exercise_id' => $exercise->id,
            'title' => $exercise->title,
            'instructions' => $exercise->instructions,
            'skill' => $exercise->skill,
            'difficulty' => $exercise->difficulty,
            'time_limit_seconds' => $exercise->time_limit_seconds,
            'items' => $exercise->exerciseQuestions->map(fn (ExerciseQuestion $item): array => [
                'question_id' => $item->question_id,
                'position' => $item->position,
                'points' => (float) $item->points,
            ])->values()->all(),
        ];
    }

    private function questionSnapshot(Question $question, ?int $sourceItemId = null): array
    {
        return [
            'snapshot_version' => 1,
            'source_item_id' => $sourceItemId,
            'topic_id' => $question->topic_id,
            'topic_name' => $question->topic?->name,
            'type' => $question->type,
            'skill' => $question->skill,
            'difficulty' => $question->difficulty,
            'prompt' => $question->prompt,
            'options' => $question->options->map(fn ($option): array => [
                'key' => $option->option_key,
                'content' => $option->content,
            ])->values()->all(),
            'correct_keys' => $question->options->where('is_correct', true)->pluck('option_key')->values()->all(),
            'explanation' => $question->explanation,
            'source_type' => $question->source_type,
        ];
    }

    private function contextSnapshot(Question $question): ?array
    {
        if ($question->passage) {
            return [
                'kind' => 'passage',
                'title' => $question->passage->title,
                'body' => $question->passage->body,
                'word_count' => $question->passage->word_count,
                'source_type' => $question->passage->source_type,
            ];
        }

        if ($question->listeningContent) {
            return [
                'kind' => 'listening',
                'title' => $question->listeningContent->title,
                'transcript' => $question->listeningContent->transcript,
                'audio_path' => $question->listeningContent->audio_path,
                'audio_mime' => $question->listeningContent->audio_mime,
                'audio_size_bytes' => $question->listeningContent->audio_size_bytes,
                'duration_seconds' => $question->listeningContent->duration_seconds,
                'source_type' => $question->listeningContent->source_type,
            ];
        }

        return null;
    }

    private function takingItem(AttemptAnswer $answer): array
    {
        $snapshot = is_array($answer->question_snapshot) ? $answer->question_snapshot : [];
        $context = is_array($answer->context_snapshot) ? $answer->context_snapshot : null;

        if ($context !== null && ($context['kind'] ?? null) === 'listening') {
            unset($context['transcript']);
        }

        return [
            'id' => $answer->id,
            'position' => $answer->question_position,
            'sectionPosition' => $answer->section_position,
            'type' => $snapshot['type'] ?? null,
            'prompt' => $snapshot['prompt'] ?? '',
            'options' => $snapshot['options'] ?? [],
            'context' => $context,
            'response' => data_get($answer->response, 'value'),
            'save_version' => $answer->save_version,
            'save_url' => route('attempts.answers.update', [$answer->attempt_id, $answer->id]),
        ];
    }
}
