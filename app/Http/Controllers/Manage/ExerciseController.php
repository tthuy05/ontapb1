<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manage\ContentStatusRequest;
use App\Http\Requests\Manage\ExerciseRequest;
use App\Models\Exercise;
use App\Models\Question;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExerciseController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $status = $request->string('status')->toString();
        $skill = $request->string('skill')->toString();
        $topicId = $request->integer('topic');

        $exercises = Exercise::query()->with('topic')->withCount(['exerciseQuestions', 'attempts'])
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('instructions', 'like', "%{$search}%");
            }))
            ->when(in_array($status, Exercise::STATUSES, true), fn (Builder $query) => $query->where('status', $status))
            ->when(in_array($skill, Exercise::SKILLS, true), fn (Builder $query) => $query->where('skill', $skill))
            ->when($topicId > 0, fn (Builder $query) => $query->where('topic_id', $topicId))
            ->orderBy('status')->orderBy('title')
            ->paginate(20)->withQueryString();

        return view('manage.exercises.index', [
            'exercises' => $exercises,
            'topics' => $this->topics(),
        ]);
    }

    public function create(): View
    {
        return view('manage.exercises.create', $this->formOptions());
    }

    public function store(ExerciseRequest $request): RedirectResponse
    {
        $exercise = DB::transaction(function () use ($request): Exercise {
            $exercise = Exercise::query()->create($request->contentAttributes());
            $this->syncItems($exercise, $request->itemsAttributes());

            return $exercise;
        });

        return redirect()->route('manage.exercises.edit', $exercise)->with('status', 'Exercise created.');
    }

    public function show(Exercise $exercise): View
    {
        $exercise->load([
            'topic',
            'exerciseQuestions' => fn ($query) => $query->with(['question.topic', 'question.passage', 'question.listeningContent', 'question.options']),
        ]);

        return view('manage.exercises.show', compact('exercise'));
    }

    public function preview(Exercise $exercise): View
    {
        $exercise->load([
            'topic',
            'exerciseQuestions' => fn ($query) => $query->with(['question.options']),
        ]);

        return view('manage.exercises.preview', compact('exercise'));
    }

    public function edit(Exercise $exercise): View
    {
        $exercise->load('exerciseQuestions.question');

        return view('manage.exercises.edit', array_merge(['exercise' => $exercise], $this->formOptions()));
    }

    public function update(ExerciseRequest $request, Exercise $exercise): RedirectResponse
    {
        DB::transaction(function () use ($request, $exercise): void {
            $exercise->update($request->contentAttributes());
            $this->syncItems($exercise, $request->itemsAttributes());
        });

        return redirect()->route('manage.exercises.edit', $exercise)->with('status', 'Exercise updated.');
    }

    public function updateStatus(ContentStatusRequest $request, Exercise $exercise): RedirectResponse
    {
        if ($request->validated('status') === 'active') {
            $exercise->load([
                'topic',
                'exerciseQuestions' => fn ($query) => $query->with(['question.topic', 'question.passage', 'question.listeningContent', 'question.options']),
            ]);
            if (($errors = $this->activationErrors($exercise)) !== []) {
                return back()->with('error', implode(' ', $errors));
            }
        }

        $exercise->update($request->validated());

        return back()->with('status', 'Exercise status updated.');
    }

    public function destroy(Request $request, Exercise $exercise): RedirectResponse
    {
        $request->validate(['confirm_delete' => ['accepted']]);
        if ($exercise->status !== 'draft' || $exercise->attempts()->exists()) {
            return back()->with('error', 'Published or attempted exercises are preserved. Deactivate them instead.');
        }

        $exercise->delete();

        return redirect()->route('manage.exercises.index')->with('status', 'Unused draft exercise deleted.');
    }

    private function syncItems(Exercise $exercise, array $items): void
    {
        $exercise->exerciseQuestions()->delete();
        $exercise->exerciseQuestions()->createMany($items);
    }

    private function activationErrors(Exercise $exercise): array
    {
        $errors = [];
        if ($exercise->title === '' || trim($exercise->title) === '') {
            $errors[] = 'An exercise needs a title.';
        }
        if (! in_array($exercise->skill, Exercise::SKILLS, true)) {
            $errors[] = 'An exercise must use a supported Reading or Listening skill.';
        }
        if (! in_array($exercise->difficulty, Exercise::DIFFICULTIES, true)) {
            $errors[] = 'An exercise must use a supported B1 difficulty.';
        }
        if ($exercise->topic && ($exercise->topic->status !== 'active' || ! in_array($exercise->topic->area, [$exercise->skill, 'general'], true))) {
            $errors[] = 'An active exercise needs an active topic matching its skill.';
        }
        if ($exercise->exerciseQuestions->isEmpty()) {
            $errors[] = 'An active exercise needs at least one question.';
        }

        foreach ($exercise->exerciseQuestions as $item) {
            $question = $item->question;
            if (! $question || $question->status !== 'active') {
                $errors[] = 'Every exercise question must be active.';

                continue;
            }
            if ($question->skill !== $exercise->skill || ! in_array($question->type, Question::TYPES, true)) {
                $errors[] = 'Every exercise question must match the exercise skill and use a supported type.';
            }
            if ($question->topic && $question->topic->status !== 'active') {
                $errors[] = 'Every exercise question topic must be active.';
            }
            if ($question->passage_id !== null && (! $question->passage || $question->passage->status !== 'active')) {
                $errors[] = 'Every Reading context must be active before the exercise can be activated.';
            }
            if ($question->listening_content_id !== null && (! $question->listeningContent || $question->listeningContent->status !== 'active')) {
                $errors[] = 'Every Listening context must be active before the exercise can be activated.';
            }
            $correct = $question->options->where('is_correct', true)->count();
            $validCount = $question->type === 'true_false'
                ? $question->options->count() === 2
                : $question->options->count() >= 2;
            if (! $validCount || $correct !== 1) {
                $errors[] = 'Every exercise question must have a valid answer structure.';
            }
        }

        return array_values(array_unique($errors));
    }

    private function formOptions(): array
    {
        return [
            'topics' => $this->topics(),
            'questions' => Question::query()
                ->with(['topic', 'passage', 'listeningContent'])
                ->whereIn('status', ['draft', 'active'])
                ->whereIn('skill', Exercise::SKILLS)
                ->orderBy('skill')->orderBy('id')->get(),
        ];
    }

    private function topics()
    {
        return Topic::query()->whereIn('area', [...Exercise::SKILLS, 'general'])
            ->orderBy('position')->orderBy('name')->get();
    }
}
