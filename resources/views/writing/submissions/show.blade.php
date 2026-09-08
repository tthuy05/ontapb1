@extends('layouts.app')

@section('title', 'Writing practice review')

@section('content')
    @php($snapshot = $writingSubmission->prompt_snapshot ?? [])
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('writing.index') }}">Writing</a></li><li class="breadcrumb-item active" aria-current="page">Practice review</li></ol></nav>
    <div class="mb-4"><p class="text-primary fw-semibold mb-1">Writing practice review — not an official VSTEP score</p><h1 class="h2 mb-1">{{ $snapshot['title'] ?? 'Writing response' }}</h1><p class="text-body-secondary mb-0">{{ ucfirst($writingSubmission->status) }} · {{ $writingSubmission->word_count }} words · Version {{ $writingSubmission->save_version }}</p></div>
    <article class="card mb-4"><div class="card-body p-4"><h2 class="h5">Prompt snapshot</h2><p class="small text-body-secondary">This copy is preserved with the response, even if the live prompt changes.</p><div class="preserve-lines">{{ $snapshot['instructions'] ?? '' }}</div></div></article>
    <article class="card mb-4"><div class="card-body p-4"><h2 class="h5">Your response</h2><div class="preserve-lines">{{ $writingSubmission->response_text }}</div></div></article>
    @if ($writingSubmission->self_check)<article class="card mb-4"><div class="card-body p-4"><h2 class="h5">Your self-check</h2><dl class="row mb-0">@foreach ($writingSubmission->self_check as $key => $value)<dt class="col-sm-4">{{ ucfirst($key) }}</dt><dd class="col-sm-8">{{ $value }} / 5</dd>@endforeach</dl><p class="small text-body-secondary mb-0 mt-3">This is learner reflection, not an automated or examiner score.</p></div></article>@endif
    <div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-primary" href="{{ route('writing.show', $writingSubmission->writingPrompt) }}">Back to prompt</a>@if ($writingSubmission->status === 'draft')<span class="small text-body-secondary align-self-center">Drafts remain editable.</span>@endif</div>
@endsection
