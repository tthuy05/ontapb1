<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manage\ContentStatusRequest;
use App\Http\Requests\Manage\QuestionRequest;
use App\Models\ListeningContent;
use App\Models\Passage;
use App\Models\Question;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $status = $request->string('status')->toString();
        $skill = $request->string('skill')->toString();
        $type = $request->string('type')->toString();

        $questions = Question::query()->with(['topic', 'passage', 'listeningContent'])->withCount('options')
            ->when($search !== '', fn (Builder $query) => $query->where('prompt', 'like', "%{$search}%"))
            ->when(in_array($status, Question::STATUSES, true), fn (Builder $query) => $query->where('status', $status))
            ->when(in_array($skill, Question::SKILLS, true), fn (Builder $query) => $query->where('skill', $skill))
            ->when(in_array($type, Question::TYPES, true), fn (Builder $query) => $query->where('type', $type))
            ->orderBy('skill')->orderBy('id')->paginate(20)->withQueryString();

        return view('manage.questions.index', [
            'questions' => $questions,
            'topics' => $this->topics(),
        ]);
    }

    public function create(): View
    {
        return view('manage.questions.create', $this->formOptions());
    }

    public function store(QuestionRequest $request): RedirectResponse
    {
        $question = DB::transaction(function () use ($request): Question {
            $question = Question::query()->create($request->contentAttributes());
            $this->syncOptions($question, $request->optionsAttributes());
            $question->load(['topic', 'passage.topic', 'listeningContent.topic', 'options']);
            $this->rejectIfNotActivatable($question);

            return $question;
        });

        return redirect()->route('manage.questions.edit', $question)->with('status', 'Question created.');
    }

    public function show(Question $question): View
    {
        $question->load(['topic', 'passage', 'listeningContent', 'options']);

        return view('manage.questions.show', compact('question'));
    }

    public function edit(Question $question): View
    {
        $question->load('options');

        return view('manage.questions.edit', array_merge(['question' => $question], $this->formOptions()));
    }

    public function update(QuestionRequest $request, Question $question): RedirectResponse
    {
        DB::transaction(function () use ($request, $question): void {
            $question->update($request->contentAttributes());
            $this->syncOptions($question, $request->optionsAttributes());
            $question->load(['topic', 'passage.topic', 'listeningContent.topic', 'options']);
            $this->rejectIfNotActivatable($question);
        });

        return redirect()->route('manage.questions.edit', $question)->with('status', 'Question updated.');
    }

    public function updateStatus(ContentStatusRequest $request, Question $question): RedirectResponse
    {
        if ($request->validated('status') === 'active') {
            $question->load(['topic', 'passage.topic', 'listeningContent.topic', 'options']);
            if (($errors = $this->activationErrors($question)) !== []) {
                return back()->with('error', implode(' ', $errors));
            }
        }

        $question->update($request->validated());

        return back()->with('status', 'Question status updated.');
    }

    public function destroy(Request $request, Question $question): RedirectResponse
    {
        $request->validate(['confirm_delete' => ['accepted']]);
        if ($question->status !== 'draft') {
            return back()->with('error', 'Published questions are preserved. Deactivate them instead.');
        }
        $question->delete();

        return redirect()->route('manage.questions.index')->with('status', 'Unused draft question deleted.');
    }

    private function syncOptions(Question $question, array $options): void
    {
        $question->options()->delete();
        foreach ($options as $position => $option) {
            $question->options()->create([
                'option_key' => $option['option_key'],
                'content' => $option['content'],
                'is_correct' => (bool) $option['is_correct'],
                'position' => $position,
            ]);
        }
    }

    private function rejectIfNotActivatable(Question $question): void
    {
        if ($question->status === 'active' && ($errors = $this->activationErrors($question)) !== []) {
            throw ValidationException::withMessages(['status' => $errors]);
        }
    }

    private function activationErrors(Question $question): array
    {
        $errors = [];
        if (! in_array($question->skill, Question::SKILLS, true) || ! in_array($question->type, Question::TYPES, true)) {
            $errors[] = 'Questions must use an enabled Reading/Listening skill and supported type.';
        }
        if ($question->cefr_level !== 'B1' || ! in_array($question->difficulty, Question::DIFFICULTIES, true)) {
            $errors[] = 'Questions must use the supported B1 difficulty settings.';
        }
        if (trim((string) $question->prompt) === '' || trim((string) $question->explanation) === '') {
            $errors[] = 'Active questions require a prompt and explanation.';
        }
        if ($question->topic_id !== null && (! $question->topic || $question->topic->status !== 'active')) {
            $errors[] = 'The question topic must be active before activation.';
        }
        if ($question->passage_id !== null) {
            if ($question->skill !== 'reading' || ! $question->passage || $question->passage->status !== 'active') {
                $errors[] = 'Reading questions require an active Reading passage.';
            }
        }
        if ($question->listening_content_id !== null) {
            if ($question->skill !== 'listening' || ! $question->listeningContent || $question->listeningContent->status !== 'active') {
                $errors[] = 'Listening questions require active Listening content.';
            }
        }
        $options = $question->options;
        $correct = $options->where('is_correct', true)->count();
        if ($question->type === 'single_choice' && ($options->count() < 2 || $correct !== 1)) {
            $errors[] = 'Single-choice questions need at least two options and exactly one correct option.';
        }
        if ($question->type === 'true_false' && ($options->count() !== 2 || $correct !== 1)) {
            $errors[] = 'True/false questions need exactly two options and exactly one correct option.';
        }
        if ($question->source_type === 'original' && trim((string) $question->source_notes) === '' && trim((string) $question->source_reference) === '') {
            $errors[] = 'Original questions require a provenance note.';
        }
        if ($question->source_type !== 'original' && trim((string) $question->source_reference) === '') {
            $errors[] = 'Non-original questions require a source reference.';
        }

        return $errors;
    }

    private function formOptions(): array
    {
        return [
            'topics' => $this->topics(),
            'passages' => Passage::query()->orderBy('title')->get(),
            'listeningContents' => ListeningContent::query()->orderBy('title')->get(),
        ];
    }

    private function topics()
    {
        return Topic::query()->whereIn('area', ['reading', 'listening', 'general'])->orderBy('position')->orderBy('name')->get();
    }
}
