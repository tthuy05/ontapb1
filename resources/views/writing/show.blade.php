@extends('layouts.app')

@section('title', $writingPrompt->title.' · Writing')

@section('content')
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ $managePreview ?? false ? route('manage.writing.index') : route('writing.index') }}">{{ $managePreview ?? false ? 'Manage Writing' : 'Writing' }}</a></li><li class="breadcrumb-item active" aria-current="page">{{ $writingPrompt->title }}</li></ol></nav>
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4"><div><p class="text-primary fw-semibold mb-1">{{ str_replace('_', ' ', ucfirst($writingPrompt->task_type)) }}</p><h1 class="h2 mb-1">{{ $writingPrompt->title }}</h1><p class="text-body-secondary mb-0">{{ $writingPrompt->minimum_words ? 'Target: at least '.$writingPrompt->minimum_words.' words' : 'Choose a suitable length' }} · Practice guidance only</p></div>@if ($managePreview ?? false)<a class="btn btn-outline-primary align-self-start" href="{{ route('manage.writing.edit', $writingPrompt) }}">Edit</a>@endif</div>
    <article class="card mb-4"><div class="card-body p-4"><h2 class="h5">Task instructions</h2><div class="preserve-lines">{{ $writingPrompt->instructions }}</div>
        @if ($writingPrompt->guidance)
            <h2 class="h5 mt-4">Planning guidance</h2><div class="preserve-lines">{{ $writingPrompt->guidance }}</div>
        @endif
        @if ($writingPrompt->checklist)
            <h2 class="h5 mt-4">Self-check</h2><ul class="mb-0">@foreach ($writingPrompt->checklist as $item)<li>{{ $item }}</li>@endforeach</ul>
        @endif
        @if ($writingPrompt->model_answer)
            <details class="mt-4"><summary>Reveal model answer</summary><div class="preserve-lines example-panel p-3 mt-2">{{ $writingPrompt->model_answer }}</div></details>
        @endif
    </div></article>
    @unless ($managePreview ?? false)
        @if ($draft)<div class="alert alert-info">You have a saved draft. Saving it again creates the next version.</div>@endif
        <form class="card mb-4" method="POST" action="{{ $draft ? route('writing.submissions.update', $draft) : route('writing.submissions.store', $writingPrompt) }}" data-writing-editor>@csrf @if ($draft) @method('PATCH')<input type="hidden" name="save_version" value="{{ $draft->save_version }}">@endif<div class="card-body p-4"><label class="form-label" for="response_text">Your response</label><textarea class="form-control" id="response_text" name="response_text" rows="16" maxlength="100000" data-writing-textarea>{{ old('response_text', $draft?->response_text ?? '') }}</textarea><div class="d-flex justify-content-between gap-3 mt-2"><span class="form-text">The server recalculates the count when saving.</span><span class="small fw-semibold" aria-live="polite" data-writing-word-count>0 words</span></div><h2 class="h5 mt-4">Optional self-check</h2><div class="row g-2">@foreach (['content' => 'Content', 'organization' => 'Organization', 'vocabulary' => 'Vocabulary', 'grammar' => 'Grammar', 'mechanics' => 'Mechanics'] as $key => $label)<div class="col-6 col-md"><label class="form-label small" for="self_check_{{ $key }}">{{ $label }} / 5</label><select class="form-select" id="self_check_{{ $key }}" name="self_check[{{ $key }}]"><option value="">—</option>@for ($score = 1; $score <= 5; $score++)<option value="{{ $score }}" @selected(old('self_check.'.$key, $draft?->self_check[$key] ?? '') == $score)>{{ $score }}</option>@endfor</select></div>@endforeach</div><p class="small text-body-secondary mt-3 mb-0">Self-check notes are for practice and are not examiner assessment or an official score.</p></div><div class="card-footer bg-white d-flex flex-wrap gap-2"><button class="btn btn-outline-primary" name="status" value="draft" type="submit">Save draft</button><button class="btn btn-primary" name="status" value="submitted" type="submit">Submit for review</button></div></form>
        @if ($submissions->isNotEmpty())<section><h2 class="h4">Submission history</h2>@foreach ($submissions as $submission)<a class="card card-body mb-2 text-decoration-none" href="{{ route('writing.submissions.show', $submission) }}"><div class="d-flex justify-content-between"><span class="text-body">{{ $submission->submitted_at?->format('Y-m-d H:i') }}</span><span class="text-body-secondary">{{ $submission->word_count }} words</span></div></a>@endforeach</section>@endif
    @endunless
@endsection
