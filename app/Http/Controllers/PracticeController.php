<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Exercise;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PracticeController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $skill = $request->string('skill')->toString();
        $difficulty = $request->integer('difficulty');
        $topicId = $request->integer('topic');

        $exercises = Exercise::query()
            ->with('topic')
            ->withCount('exerciseQuestions')
            ->active()
            ->where(function (Builder $query): void {
                $query->whereNull('topic_id')->orWhereHas('topic', fn (Builder $topic): Builder => $topic->active());
            })
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('instructions', 'like', "%{$search}%");
            }))
            ->when(in_array($skill, Exercise::SKILLS, true), fn (Builder $query) => $query->where('skill', $skill))
            ->when(in_array($difficulty, Exercise::DIFFICULTIES, true), fn (Builder $query) => $query->where('difficulty', $difficulty))
            ->when($topicId > 0, fn (Builder $query) => $query->where('topic_id', $topicId))
            ->orderBy('difficulty')->orderBy('title')
            ->paginate(20)->withQueryString();

        $topics = Topic::query()->active()->whereIn('area', [...Exercise::SKILLS, 'general'])
            ->orderBy('position')->orderBy('name')->get();

        return view('practice.index', compact('exercises', 'topics'));
    }

    public function show(Exercise $exercise): View
    {
        $exercise->load([
            'topic',
            'exerciseQuestions' => fn ($query) => $query->with('question'),
        ]);

        abort_unless(
            $exercise->status === 'active'
                && ($exercise->topic === null || $exercise->topic->status === 'active'),
            404,
        );

        $resumableAttempt = Attempt::query()
            ->where('exercise_id', $exercise->id)
            ->inProgress()
            ->latest('started_at')
            ->first();

        return view('practice.show', compact('exercise', 'resumableAttempt'));
    }
}
