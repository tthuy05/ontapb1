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
}
