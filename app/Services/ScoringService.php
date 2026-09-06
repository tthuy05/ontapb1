<?php

namespace App\Services;

use App\Models\AttemptAnswer;

class ScoringService
{
    public function normalizeResponse(mixed $response, array $snapshot): ?string
    {
        if (is_array($response)) {
            $response = $response['value'] ?? null;
        }

        if (! is_string($response)) {
            return null;
        }

        $response = trim($response);
        if ($response === '') {
            return null;
        }

        return $response;
    }

    public function isAllowedResponse(mixed $response, array $snapshot): bool
    {
        $normalized = $this->normalizeResponse($response, $snapshot);

        return $normalized !== null && in_array(
            $normalized,
            collect($snapshot['options'] ?? [])->pluck('key')->map(fn ($key): string => (string) $key)->all(),
            true,
        );
    }

    /**
     * @return array{response: ?string, answered: bool, is_correct: ?bool, points_awarded: float, max_points: float}
     */
    public function score(AttemptAnswer $answer): array
    {
        $snapshot = is_array($answer->question_snapshot) ? $answer->question_snapshot : [];
        $response = $this->normalizeResponse($answer->response, $snapshot);
        $maxPoints = round((float) $answer->max_points, 2);

        if ($response === null) {
            return [
                'response' => null,
                'answered' => false,
                'is_correct' => null,
                'points_awarded' => 0.0,
                'max_points' => $maxPoints,
            ];
        }

        $correctKeys = collect($snapshot['correct_keys'] ?? [])
            ->map(fn ($key): string => (string) $key)
            ->all();
        $isCorrect = in_array($response, $correctKeys, true);

        return [
            'response' => $response,
            'answered' => true,
            'is_correct' => $isCorrect,
            'points_awarded' => $isCorrect ? $maxPoints : 0.0,
            'max_points' => $maxPoints,
        ];
    }
}
