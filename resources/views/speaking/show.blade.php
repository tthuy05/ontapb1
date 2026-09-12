@extends('layouts.app')

@section('title', $speakingPrompt->title.' · Speaking')

@section('content')
    @php($managePreview = $managePreview ?? false)
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ $managePreview ? route('manage.speaking.index') : route('speaking.index') }}">Speaking</a></li><li class="breadcrumb-item active" aria-current="page">{{ $speakingPrompt->title }}</li></ol></nav>
    <div class="mb-4"><p class="text-primary fw-semibold mb-1">{{ str_replace('_', ' ', ucfirst($speakingPrompt->part_type)) }} · {{ $speakingPrompt->preparation_seconds ?? 0 }} seconds preparation · {{ $speakingPrompt->speaking_seconds ?? 'Flexible' }}{{ $speakingPrompt->speaking_seconds ? ' seconds speaking' : '' }}</p><h1 class="h2 mb-1">{{ $speakingPrompt->title }}</h1><p class="text-body-secondary mb-0">{{ $managePreview ? 'Preview only — learner recording controls are disabled.' : 'Practice guidance only — there is no official score.' }}</p></div>
    <article class="card mb-4"><div class="card-body p-4"><h2 class="h5">Prompt</h2><div class="preserve-lines">{{ $speakingPrompt->instructions }}</div>@if ($speakingPrompt->topic)<p class="small text-body-secondary mt-3 mb-0">Topic: {{ $speakingPrompt->topic->name }}</p>@endif</div></article>
    <div class="row g-3 mb-4">
        @if ($speakingPrompt->suggested_ideas)<div class="col-12 col-md-4"><article class="card h-100"><div class="card-body"><h2 class="h6">Suggested ideas</h2><ul class="mb-0">@foreach ($speakingPrompt->suggested_ideas as $idea)<li>{{ $idea }}</li>@endforeach</ul></div></article></div>@endif
        @if ($speakingPrompt->follow_up_questions)<div class="col-12 col-md-4"><article class="card h-100"><div class="card-body"><h2 class="h6">Follow-up questions</h2><ul class="mb-0">@foreach ($speakingPrompt->follow_up_questions as $question)<li>{{ $question }}</li>@endforeach</ul></div></article></div>@endif
        @if ($speakingPrompt->checklist)<div class="col-12 col-md-4"><article class="card h-100"><div class="card-body"><h2 class="h6">Self-review checklist</h2><ul class="mb-0">@foreach ($speakingPrompt->checklist as $item)<li>{{ $item }}</li>@endforeach</ul></div></article></div>@endif
    </div>
    @if ($managePreview)
        <div class="alert alert-info">This prompt uses browser-local recording in the learner view. No audio field or server upload is available.</div>
    @else
        @php($editing = (bool) $draft)
        <form data-speaking-form method="POST" action="{{ $editing ? route('speaking.submissions.update', $draft) : route('speaking.submissions.store', $speakingPrompt) }}" class="card mb-4">
            @csrf @if ($editing) @method('PATCH') @endif
            <div class="card-body p-4">
                <h2 class="h5">Timed local rehearsal</h2>
                <p class="small text-body-secondary">Allow microphone access when asked. The recording is held in memory, playable here, and downloadable locally; only its duration is saved with your practice review.</p>
                <div data-speaking-recorder data-preparation-seconds="{{ $speakingPrompt->preparation_seconds ?? 0 }}" data-speaking-seconds="{{ $speakingPrompt->speaking_seconds ?? 120 }}" class="border rounded p-3 mb-4">
                    <div class="d-flex flex-wrap align-items-center gap-2"><span class="badge text-bg-light" data-speaking-timer>Ready</span><button class="btn btn-primary" type="button" data-speaking-start>Start local rehearsal</button><button class="btn btn-outline-danger" type="button" data-speaking-stop disabled>Stop</button><button class="btn btn-outline-secondary" type="button" data-speaking-play disabled>Play</button><a class="btn btn-outline-secondary" data-speaking-download hidden>Download</a></div>
                    <p class="small mt-2 mb-0" data-speaking-status role="status"></p>
                    <audio class="w-100 mt-3" data-speaking-audio controls hidden></audio>
                    <input type="hidden" name="duration_seconds" value="{{ old('duration_seconds', $draft?->duration_seconds) }}" data-speaking-duration>
                </div>
                <div class="row g-3 mb-3"><div class="col-12 col-md-6"><label class="form-label" for="self_assessment_content">Content</label><select class="form-select" id="self_assessment_content" name="self_assessment[content]"><option value="">Not rated</option>@for ($rating = 1; $rating <= 5; $rating++)<option value="{{ $rating }}" @selected((string) old('self_assessment.content', data_get($draft?->self_assessment, 'content')) === (string) $rating)>{{ $rating }} / 5</option>@endfor</select></div><div class="col-12 col-md-6"><label class="form-label" for="self_assessment_fluency">Fluency</label><select class="form-select" id="self_assessment_fluency" name="self_assessment[fluency]"><option value="">Not rated</option>@for ($rating = 1; $rating <= 5; $rating++)<option value="{{ $rating }}" @selected((string) old('self_assessment.fluency', data_get($draft?->self_assessment, 'fluency')) === (string) $rating)>{{ $rating }} / 5</option>@endfor</select></div><div class="col-12 col-md-6"><label class="form-label" for="self_assessment_pronunciation">Pronunciation</label><select class="form-select" id="self_assessment_pronunciation" name="self_assessment[pronunciation]"><option value="">Not rated</option>@for ($rating = 1; $rating <= 5; $rating++)<option value="{{ $rating }}" @selected((string) old('self_assessment.pronunciation', data_get($draft?->self_assessment, 'pronunciation')) === (string) $rating)>{{ $rating }} / 5</option>@endfor</select></div><div class="col-12 col-md-6"><label class="form-label" for="self_assessment_interaction">Interaction</label><select class="form-select" id="self_assessment_interaction" name="self_assessment[interaction]"><option value="">Not rated</option>@for ($rating = 1; $rating <= 5; $rating++)<option value="{{ $rating }}" @selected((string) old('self_assessment.interaction', data_get($draft?->self_assessment, 'interaction')) === (string) $rating)>{{ $rating }} / 5</option>@endfor</select></div><div class="col-12"><label class="form-label" for="self_assessment_language_control">Language control</label><select class="form-select" id="self_assessment_language_control" name="self_assessment[language_control]"><option value="">Not rated</option>@for ($rating = 1; $rating <= 5; $rating++)<option value="{{ $rating }}" @selected((string) old('self_assessment.language_control', data_get($draft?->self_assessment, 'language_control')) === (string) $rating)>{{ $rating }} / 5</option>@endfor</select></div></div>
                <label class="form-label" for="notes">Notes for the next attempt</label><textarea class="form-control mb-3" id="notes" name="notes" rows="5" maxlength="20000" placeholder="Write one target improvement for a second attempt.">{{ old('notes', $draft?->notes) }}</textarea>
                <input type="hidden" name="save_version" value="{{ old('save_version', $draft?->save_version ?? 0) }}">
                <div class="d-flex flex-wrap gap-2"><button class="btn btn-outline-primary" type="submit" name="status" value="draft">Save draft</button><button class="btn btn-primary" type="submit" name="status" value="submitted">Save self-review</button></div>
            </div>
        </form>
    @endif
    @if (! $managePreview && $submissions->isNotEmpty())
        <article class="card"><div class="card-body p-4"><h2 class="h5">Previous self-reviews</h2><ul class="mb-0">@foreach ($submissions as $submission)<li><a href="{{ route('speaking.submissions.show', $submission) }}">{{ $submission->completed_at?->format('Y-m-d H:i') ?? 'Saved review' }}</a> · {{ $submission->duration_seconds }} seconds</li>@endforeach</ul></div></article>
    @endif
@endsection
