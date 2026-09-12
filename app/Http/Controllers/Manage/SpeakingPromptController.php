<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manage\ContentStatusRequest;
use App\Http\Requests\Manage\SpeakingPromptRequest;
use App\Models\SpeakingPrompt;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpeakingPromptController extends Controller
{
    public function index(Request $request): View
    {
        $prompts = SpeakingPrompt::query()->with('topic')->withCount('submissions')
            ->when($request->filled('search'), fn (Builder $query) => $query->where('title', 'like', '%'.trim($request->string('search')).'%'))
            ->when(in_array($request->string('status')->toString(), SpeakingPrompt::STATUSES, true), fn (Builder $query) => $query->where('status', $request->string('status')))
            ->when(in_array($request->string('part_type')->toString(), SpeakingPrompt::PART_TYPES, true), fn (Builder $query) => $query->where('part_type', $request->string('part_type')))
            ->orderBy('status')->orderBy('part_type')->orderBy('title')->paginate(20)->withQueryString();

        return view('manage.speaking.index', compact('prompts'));
    }

    public function create(): View
    {
        return view('manage.speaking.create', array_merge(['speakingPrompt' => new SpeakingPrompt], $this->formOptions()));
    }

    public function store(SpeakingPromptRequest $request): RedirectResponse
    {
        $prompt = SpeakingPrompt::query()->create($request->contentAttributes());

        return redirect()->route('manage.speaking.edit', $prompt)->with('status', 'Speaking prompt created.');
    }

    public function show(SpeakingPrompt $speakingPrompt): View
    {
        $speakingPrompt->load('topic')->loadCount('submissions');
        $submissions = $speakingPrompt->submissions()->where('status', 'submitted')->latest('completed_at')->limit(10)->get();

        return view('manage.speaking.show', compact('speakingPrompt', 'submissions'));
    }

    public function preview(SpeakingPrompt $speakingPrompt): View
    {
        $speakingPrompt->load('topic');

        return view('speaking.show', ['speakingPrompt' => $speakingPrompt, 'draft' => null, 'submissions' => collect(), 'managePreview' => true]);
    }

    public function edit(SpeakingPrompt $speakingPrompt): View
    {
        return view('manage.speaking.edit', array_merge(['speakingPrompt' => $speakingPrompt], $this->formOptions()));
    }

    public function update(SpeakingPromptRequest $request, SpeakingPrompt $speakingPrompt): RedirectResponse
    {
        $speakingPrompt->update($request->contentAttributes());

        return redirect()->route('manage.speaking.edit', $speakingPrompt)->with('status', 'Speaking prompt updated.');
    }

    public function updateStatus(ContentStatusRequest $request, SpeakingPrompt $speakingPrompt): RedirectResponse
    {
        if ($request->validated('status') === 'active') {
            if ($speakingPrompt->topic_id !== null && (! $speakingPrompt->topic || $speakingPrompt->topic->status !== 'active')) {
                return back()->with('error', 'The speaking prompt topic must be active.');
            }
            if ($speakingPrompt->source_type === 'original' && trim((string) $speakingPrompt->source_notes) === '' && trim((string) $speakingPrompt->source_reference) === '') {
                return back()->with('error', 'Active original content requires a provenance note.');
            }
            if ($speakingPrompt->source_type !== 'original' && trim((string) $speakingPrompt->source_reference) === '') {
                return back()->with('error', 'Active non-original content requires a source reference.');
            }
        }
        $speakingPrompt->update($request->validated());

        return back()->with('status', 'Speaking prompt status updated.');
    }

    public function destroy(Request $request, SpeakingPrompt $speakingPrompt): RedirectResponse
    {
        $request->validate(['confirm_delete' => ['accepted']]);
        if ($speakingPrompt->status !== 'draft' || $speakingPrompt->submissions()->exists() || $speakingPrompt->examSectionItems()->exists()) {
            return back()->with('error', 'Published or referenced speaking prompts are preserved. Deactivate them instead.');
        }
        $speakingPrompt->delete();

        return redirect()->route('manage.speaking.index')->with('status', 'Unused draft speaking prompt deleted.');
    }

    private function formOptions(): array
    {
        return ['topics' => Topic::query()->whereIn('area', ['speaking', 'general'])->orderBy('position')->orderBy('name')->get()];
    }
}
