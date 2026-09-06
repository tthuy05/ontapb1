<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manage\ContentStatusRequest;
use App\Http\Requests\Manage\ListeningContentRequest;
use App\Models\ListeningContent;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListeningContentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $status = $request->string('status')->toString();
        $sourceType = $request->string('source_type')->toString();
        $topicId = $request->integer('topic');

        $contents = ListeningContent::query()->with('topic')->withCount('questions')
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")->orWhere('transcript', 'like', "%{$search}%");
            }))
            ->when(in_array($status, ListeningContent::STATUSES, true), fn (Builder $query) => $query->where('status', $status))
            ->when(in_array($sourceType, ListeningContent::SOURCE_TYPES, true), fn (Builder $query) => $query->where('source_type', $sourceType))
            ->when($topicId > 0, fn (Builder $query) => $query->where('topic_id', $topicId))
            ->orderBy('title')->paginate(20)->withQueryString();

        return view('manage.listening.index', ['contents' => $contents, 'topics' => $this->topics()]);
    }

    public function create(): View
    {
        return view('manage.listening.create', ['topics' => $this->topics()]);
    }

    public function store(ListeningContentRequest $request): RedirectResponse
    {
        $content = ListeningContent::query()->create($request->validated());

        return redirect()->route('manage.listening.edit', $content)->with('status', 'Listening content created.');
    }

    public function show(ListeningContent $listeningContent): View
    {
        $listeningContent->load(['topic', 'questions.options']);

        return view('manage.listening.show', compact('listeningContent'));
    }

    public function edit(ListeningContent $listeningContent): View
    {
        return view('manage.listening.edit', ['listeningContent' => $listeningContent, 'topics' => $this->topics()]);
    }

    public function update(ListeningContentRequest $request, ListeningContent $listeningContent): RedirectResponse
    {
        $listeningContent->update($request->validated());

        return redirect()->route('manage.listening.edit', $listeningContent)->with('status', 'Listening content updated.');
    }

    public function updateStatus(ContentStatusRequest $request, ListeningContent $listeningContent): RedirectResponse
    {
        if ($request->validated('status') === 'active' && ($errors = $this->activationErrors($listeningContent)) !== []) {
            return back()->with('error', implode(' ', $errors));
        }

        $listeningContent->update($request->validated());

        return back()->with('status', 'Listening content status updated.');
    }

    public function destroy(Request $request, ListeningContent $listeningContent): RedirectResponse
    {
        $request->validate(['confirm_delete' => ['accepted']]);
        if ($listeningContent->status !== 'draft' || $listeningContent->questions()->exists()) {
            return back()->with('error', 'This Listening item is published or referenced by questions. Deactivate it instead.');
        }
        $listeningContent->delete();

        return redirect()->route('manage.listening.index')->with('status', 'Unused draft Listening item deleted.');
    }

    private function topics()
    {
        return Topic::query()->whereIn('area', ['listening', 'general'])->orderBy('position')->orderBy('name')->get();
    }

    private function activationErrors(ListeningContent $content): array
    {
        $errors = [];
        if (trim($content->transcript) === '' || trim($content->audio_path) === '' || $content->audio_size_bytes < 1) {
            $errors[] = 'Listening content requires a transcript and a measured audio asset.';
        }
        if ($content->cefr_level !== 'B1' || ! in_array($content->difficulty, ListeningContent::DIFFICULTIES, true)) {
            $errors[] = 'Listening content must use the supported B1 difficulty settings.';
        }
        if ($content->topic_id !== null && (! $content->topic || $content->topic->status !== 'active')) {
            $errors[] = 'The Listening topic must be active before the content can be activated.';
        }
        if ($content->source_type === 'original' && trim((string) $content->source_notes) === '' && trim((string) $content->source_reference) === '') {
            $errors[] = 'Original Listening content requires a provenance note.';
        }
        if ($content->source_type !== 'original' && trim((string) $content->source_reference) === '') {
            $errors[] = 'Non-original Listening content requires a source reference.';
        }

        return $errors;
    }
}
