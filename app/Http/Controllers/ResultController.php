<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\AttemptAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function show(Attempt $attempt): View|RedirectResponse
    {
        abort_unless($attempt->exercise_id !== null && $attempt->exam_id === null, 404);

        if ($attempt->status !== 'submitted') {
            return redirect()->route('attempts.show', $attempt);
        }

        $attempt->load(['exercise', 'answers']);

        return view('results.show', [
            'attempt' => $attempt,
            'items' => $attempt->answers->map(fn (AttemptAnswer $answer): array => $this->resultItem($answer))->all(),
            'breakdowns' => [
                'skill' => $this->breakdown($attempt->answers, fn (AttemptAnswer $answer): string => (string) data_get($answer->question_snapshot, 'skill', 'Not recorded')),
                'topic' => $this->breakdown($attempt->answers, fn (AttemptAnswer $answer): string => (string) data_get($answer->question_snapshot, 'topic_name', 'Not recorded')),
                'type' => $this->breakdown($attempt->answers, fn (AttemptAnswer $answer): string => (string) data_get($answer->question_snapshot, 'type', 'Not recorded')),
            ],
        ]);
    }

    private function resultItem(AttemptAnswer $answer): array
    {
        $snapshot = is_array($answer->question_snapshot) ? $answer->question_snapshot : [];
        $context = is_array($answer->context_snapshot) ? $answer->context_snapshot : null;
        $correctKeys = collect($snapshot['correct_keys'] ?? [])->map(fn ($key): string => (string) $key);
        $options = collect($snapshot['options'] ?? []);

        return [
            'position' => $answer->question_position,
            'answerId' => $answer->id,
            'skill' => $snapshot['skill'] ?? null,
            'topic' => $snapshot['topic_name'] ?? null,
            'type' => $snapshot['type'] ?? null,
            'prompt' => $snapshot['prompt'] ?? '',
            'options' => $options->all(),
            'response' => data_get($answer->response, 'value'),
            'correctOptions' => $options->filter(fn (array $option): bool => $correctKeys->contains((string) ($option['key'] ?? '')))->values()->all(),
            'explanation' => $snapshot['explanation'] ?? null,
            'context' => $context,
            'isCorrect' => $answer->is_correct,
            'pointsAwarded' => $answer->points_awarded,
            'maxPoints' => $answer->max_points,
        ];
    }

    private function breakdown($answers, callable $label): array
    {
        return $answers->groupBy($label)->map(function ($items, $name): array {
            $max = (float) $items->sum(fn (AttemptAnswer $answer): float => (float) $answer->max_points);
            $awarded = (float) $items->sum(fn (AttemptAnswer $answer): float => (float) $answer->points_awarded);

            return [
                'name' => $name,
                'questions' => $items->count(),
                'correct' => $items->where('is_correct', true)->count(),
                'incorrect' => $items->where('is_correct', false)->count(),
                'unanswered' => $items->whereNull('is_correct')->count(),
                'points_awarded' => number_format($awarded, 2, '.', ''),
                'max_points' => number_format($max, 2, '.', ''),
                'percentage' => $max > 0 ? number_format(($awarded / $max) * 100, 2, '.', '') : '0.00',
            ];
        })->values()->all();
    }
}
