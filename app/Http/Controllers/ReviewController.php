<?php

namespace App\Http\Controllers;

use App\Models\AttemptAnswer;
use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $skill = (string) $request->string('skill');
        $type = (string) $request->string('type');
        $attemptId = $request->integer('attempt');

        $answers = AttemptAnswer::query()
            ->with(['attempt.exercise', 'attempt.exam'])
            ->where('is_correct', false)
            ->whereHas('attempt', fn ($query) => $query->where('status', 'submitted')->when($attemptId > 0, fn ($query) => $query->whereKey($attemptId)))
            ->when(in_array($skill, Exercise::SKILLS, true), fn ($query) => $query->where('question_snapshot->skill', $skill))
            ->when(in_array($type, ['single_choice', 'true_false'], true), fn ($query) => $query->where('question_snapshot->type', $type))
            ->when($search !== '', fn ($query) => $query->where('question_snapshot->prompt', 'like', '%'.$search.'%'))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('review.index', [
            'answers' => $answers,
            'search' => $search,
            'skill' => $skill,
            'type' => $type,
            'skills' => Exercise::SKILLS,
            'types' => ['single_choice', 'true_false'],
        ]);
    }

    public function show(AttemptAnswer $attemptAnswer): View
    {
        $attemptAnswer->load('attempt.exercise.topic');
        abort_unless($attemptAnswer->is_correct === false && $attemptAnswer->attempt?->status === 'submitted', 404);

        $snapshot = is_array($attemptAnswer->question_snapshot) ? $attemptAnswer->question_snapshot : [];
        $options = collect($snapshot['options'] ?? []);
        $correctKeys = collect($snapshot['correct_keys'] ?? [])->map(fn ($key): string => (string) $key);

        return view('review.show', [
            'attemptAnswer' => $attemptAnswer,
            'attempt' => $attemptAnswer->attempt,
            'snapshot' => $snapshot,
            'context' => $attemptAnswer->context_snapshot,
            'response' => data_get($attemptAnswer->response, 'value'),
            'options' => $options,
            'correctOptions' => $options->filter(fn (array $option): bool => $correctKeys->contains((string) ($option['key'] ?? '')))->values(),
        ]);
    }
}
