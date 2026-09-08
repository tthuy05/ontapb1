<?php

namespace App\Http\Controllers;

use App\Http\Requests\WritingSubmissionRequest;
use App\Models\WritingPrompt;
use App\Models\WritingSubmission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WritingController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $taskType = $request->string('task_type')->toString();
        $prompts = $this->visiblePrompts()
            ->with('topic')->withCount(['submissions as submitted_count' => fn (Builder $query): Builder => $query->where('status', 'submitted')])
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $q) => $q->where('title', 'like', "%{$search}%")->orWhere('instructions', 'like', "%{$search}%")))
            ->when(in_array($taskType, WritingPrompt::TASK_TYPES, true), fn (Builder $query) => $query->where('task_type', $taskType))
            ->orderBy('task_type')->orderBy('title')->paginate(20)->withQueryString();

        return view('writing.index', ['prompts' => $prompts, 'taskTypes' => WritingPrompt::TASK_TYPES]);
    }

    public function show(WritingPrompt $writingPrompt): View
    {
        $this->ensureVisible($writingPrompt);
        $writingPrompt->load('topic');
        $draft = $writingPrompt->submissions()->where('status', 'draft')->latest('updated_at')->first();
        $submissions = $writingPrompt->submissions()->where('status', 'submitted')->latest('submitted_at')->limit(10)->get();

        return view('writing.show', compact('writingPrompt', 'draft', 'submissions'));
    }

    public function store(WritingSubmissionRequest $request, WritingPrompt $writingPrompt): RedirectResponse
    {
        $this->ensureVisible($writingPrompt);
        $attributes = $request->submissionAttributes();
        $status = $request->string('status')->toString();
        $submission = $writingPrompt->submissions()->create(array_merge($attributes, [
            'status' => $status,
            'word_count' => WritingSubmission::countWords($attributes['response_text']),
            'prompt_snapshot' => $this->snapshot($writingPrompt),
            'save_version' => 0,
            'submitted_at' => $status === 'submitted' ? now() : null,
        ]));

        return redirect()->route('writing.submissions.show', $submission)->with('status', $status === 'submitted' ? 'Writing response submitted for practice review.' : 'Writing draft saved.');
    }

    public function showSubmission(WritingSubmission $writingSubmission): View
    {
        $writingSubmission->load('writingPrompt');

        return view('writing.submissions.show', compact('writingSubmission'));
    }

    public function update(WritingSubmissionRequest $request, WritingSubmission $writingSubmission): RedirectResponse
    {
        abort_unless($writingSubmission->status === 'draft', 409, 'Submitted writing responses are immutable.');
        $attributes = $request->submissionAttributes();
        $expectedVersion = $request->integer('save_version', $writingSubmission->save_version);
        $updated = WritingSubmission::query()->whereKey($writingSubmission->id)->where('status', 'draft')->where('save_version', $expectedVersion)->update(array_merge($attributes, [
            'status' => $request->string('status')->toString(),
            'word_count' => WritingSubmission::countWords($attributes['response_text']),
            'save_version' => $expectedVersion + 1,
            'submitted_at' => $request->string('status')->toString() === 'submitted' ? now() : null,
            'updated_at' => now(),
        ]));
        abort_if($updated !== 1, 409, 'This writing draft changed elsewhere. Reload and try again.');

        return redirect()->route('writing.submissions.show', $writingSubmission)->with('status', $request->string('status')->toString() === 'submitted' ? 'Writing response submitted for practice review.' : 'Writing draft saved.');
    }

    private function visiblePrompts(): Builder
    {
        return WritingPrompt::query()->active()->where(fn (Builder $query): Builder => $query->whereNull('topic_id')->orWhereHas('topic', fn (Builder $topic): Builder => $topic->active()));
    }

    private function ensureVisible(WritingPrompt $writingPrompt): void
    {
        abort_unless($writingPrompt->status === 'active' && ($writingPrompt->topic_id === null || ($writingPrompt->topic && $writingPrompt->topic->status === 'active')), 404);
    }

    private function snapshot(WritingPrompt $prompt): array
    {
        return $prompt->only(['title', 'task_type', 'instructions', 'minimum_words', 'recommended_minutes', 'guidance', 'checklist', 'model_answer', 'source_type', 'source_reference']);
    }
}
