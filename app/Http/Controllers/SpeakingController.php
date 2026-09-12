<?php

namespace App\Http\Controllers;

use App\Http\Requests\SpeakingSubmissionRequest;
use App\Models\SpeakingPrompt;
use App\Models\SpeakingSubmission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpeakingController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $partType = $request->string('part_type')->toString();
        $prompts = $this->visiblePrompts()
            ->with('topic')
            ->withCount(['submissions as submitted_count' => fn (Builder $query): Builder => $query->where('status', 'submitted')])
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $q) => $q->where('title', 'like', "%{$search}%")->orWhere('instructions', 'like', "%{$search}%")))
            ->when(in_array($partType, SpeakingPrompt::PART_TYPES, true), fn (Builder $query) => $query->where('part_type', $partType))
            ->orderBy('part_type')->orderBy('title')->paginate(20)->withQueryString();

        return view('speaking.index', ['prompts' => $prompts, 'partTypes' => SpeakingPrompt::PART_TYPES]);
    }

    public function show(SpeakingPrompt $speakingPrompt): View
    {
        $this->ensureVisible($speakingPrompt);
        $speakingPrompt->load('topic');
        $draft = $speakingPrompt->submissions()->where('status', 'draft')->latest('updated_at')->first();
        $submissions = $speakingPrompt->submissions()->where('status', 'submitted')->latest('completed_at')->limit(10)->get();

        return view('speaking.show', compact('speakingPrompt', 'draft', 'submissions'));
    }

    public function store(SpeakingSubmissionRequest $request, SpeakingPrompt $speakingPrompt): RedirectResponse
    {
        $this->ensureVisible($speakingPrompt);
        $status = $request->string('status')->toString();
        $submission = $speakingPrompt->submissions()->create(array_merge($request->submissionAttributes(), [
            'status' => $status,
            'prompt_snapshot' => $this->snapshot($speakingPrompt),
            'save_version' => 0,
            'completed_at' => $status === 'submitted' ? now() : null,
        ]));

        return redirect()->route('speaking.submissions.show', $submission)->with('status', $status === 'submitted' ? 'Speaking practice saved for self-review.' : 'Speaking draft saved.');
    }

    public function showSubmission(SpeakingSubmission $speakingSubmission): View
    {
        $speakingSubmission->load('speakingPrompt');

        return view('speaking.submissions.show', compact('speakingSubmission'));
    }

    public function update(SpeakingSubmissionRequest $request, SpeakingSubmission $speakingSubmission): RedirectResponse
    {
        abort_unless($speakingSubmission->status === 'draft', 409, 'Submitted speaking practice is immutable.');
        $status = $request->string('status')->toString();
        $expectedVersion = $request->integer('save_version', $speakingSubmission->save_version);
        $updated = SpeakingSubmission::query()->whereKey($speakingSubmission->id)->where('status', 'draft')->where('save_version', $expectedVersion)->update(array_merge($request->submissionAttributes(), [
            'status' => $status,
            'save_version' => $expectedVersion + 1,
            'completed_at' => $status === 'submitted' ? now() : null,
            'updated_at' => now(),
        ]));
        abort_if($updated !== 1, 409, 'This speaking draft changed elsewhere. Reload and try again.');

        return redirect()->route('speaking.submissions.show', $speakingSubmission)->with('status', $status === 'submitted' ? 'Speaking practice saved for self-review.' : 'Speaking draft saved.');
    }

    private function visiblePrompts(): Builder
    {
        return SpeakingPrompt::query()->active()->where(fn (Builder $query): Builder => $query->whereNull('topic_id')->orWhereHas('topic', fn (Builder $topic): Builder => $topic->active()));
    }

    private function ensureVisible(SpeakingPrompt $speakingPrompt): void
    {
        abort_unless($speakingPrompt->status === 'active' && ($speakingPrompt->topic_id === null || ($speakingPrompt->topic && $speakingPrompt->topic->status === 'active')), 404);
    }

    private function snapshot(SpeakingPrompt $prompt): array
    {
        return $prompt->only(['title', 'part_type', 'instructions', 'preparation_seconds', 'speaking_seconds', 'suggested_ideas', 'follow_up_questions', 'checklist', 'source_type', 'source_reference']);
    }
}
