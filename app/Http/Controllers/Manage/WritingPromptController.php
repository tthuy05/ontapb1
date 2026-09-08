<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manage\ContentStatusRequest;
use App\Http\Requests\Manage\WritingPromptRequest;
use App\Models\Topic;
use App\Models\WritingPrompt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WritingPromptController extends Controller
{
    public function index(Request $request): View
    {
        $prompts = WritingPrompt::query()->with('topic')->withCount('submissions')
            ->when($request->filled('search'), fn (Builder $q) => $q->where('title', 'like', '%'.trim($request->string('search')).'%'))
            ->when(in_array($request->string('status')->toString(), WritingPrompt::STATUSES, true), fn (Builder $q) => $q->where('status', $request->string('status')))
            ->when(in_array($request->string('task_type')->toString(), WritingPrompt::TASK_TYPES, true), fn (Builder $q) => $q->where('task_type', $request->string('task_type')))
            ->orderBy('status')->orderBy('task_type')->orderBy('title')->paginate(20)->withQueryString();

        return view('manage.writing.index', ['prompts' => $prompts]);
    }

    public function create(): View
    {
        return view('manage.writing.create', array_merge(['writingPrompt' => new WritingPrompt], $this->formOptions()));
    }

    public function store(WritingPromptRequest $request): RedirectResponse
    {
        $prompt = WritingPrompt::query()->create($request->contentAttributes());

        return redirect()->route('manage.writing.edit', $prompt)->with('status', 'Writing prompt created.');
    }

    public function show(WritingPrompt $writingPrompt): View
    {
        $writingPrompt->load('topic')->loadCount('submissions');

        return view('manage.writing.show', compact('writingPrompt'));
    }

    public function preview(WritingPrompt $writingPrompt): View
    {
        $writingPrompt->load('topic');

        return view('writing.show', ['writingPrompt' => $writingPrompt, 'draft' => null, 'submissions' => collect(), 'managePreview' => true]);
    }

    public function edit(WritingPrompt $writingPrompt): View
    {
        return view('manage.writing.edit', array_merge(['writingPrompt' => $writingPrompt], $this->formOptions()));
    }

    public function update(WritingPromptRequest $request, WritingPrompt $writingPrompt): RedirectResponse
    {
        $writingPrompt->update($request->contentAttributes());

        return redirect()->route('manage.writing.edit', $writingPrompt)->with('status', 'Writing prompt updated.');
    }

    public function updateStatus(ContentStatusRequest $request, WritingPrompt $writingPrompt): RedirectResponse
    {
        if ($request->validated('status') === 'active') {
            $payload = $writingPrompt->toArray();
            if ($writingPrompt->source_type === 'original' && trim((string) $writingPrompt->source_notes) === '' && trim((string) $writingPrompt->source_reference) === '') {
                return back()->with('error', 'Active original content requires a provenance note.');
            }
            if ($writingPrompt->topic_id !== null && (! $writingPrompt->topic || $writingPrompt->topic->status !== 'active')) {
                return back()->with('error', 'The writing prompt topic must be active.');
            }
        }
        $writingPrompt->update($request->validated());

        return back()->with('status', 'Writing prompt status updated.');
    }

    public function destroy(Request $request, WritingPrompt $writingPrompt): RedirectResponse
    {
        $request->validate(['confirm_delete' => ['accepted']]);
        if ($writingPrompt->status !== 'draft' || $writingPrompt->submissions()->exists() || $writingPrompt->examSectionItems()->exists()) {
            return back()->with('error', 'Published or referenced writing prompts are preserved. Deactivate them instead.');
        }
        $writingPrompt->delete();

        return redirect()->route('manage.writing.index')->with('status', 'Unused draft writing prompt deleted.');
    }

    private function formOptions(): array
    {
        return ['topics' => Topic::query()->whereIn('area', ['writing', 'general'])->orderBy('position')->orderBy('name')->get()];
    }
}
