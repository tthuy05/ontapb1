@extends('layouts.app')

@section('title', 'Speaking practice review')

@section('content')
    @php($snapshot = $speakingSubmission->prompt_snapshot ?? [])
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('speaking.index') }}">Speaking</a></li><li class="breadcrumb-item active" aria-current="page">Practice review</li></ol></nav>
    <div class="mb-4"><p class="text-primary fw-semibold mb-1">Speaking practice review — not an official VSTEP score</p><h1 class="h2 mb-1">{{ $snapshot['title'] ?? 'Speaking response' }}</h1><p class="text-body-secondary mb-0">{{ ucfirst($speakingSubmission->status) }} · {{ $speakingSubmission->duration_seconds ?? 0 }} seconds · Version {{ $speakingSubmission->save_version }}</p></div>
    <article class="card mb-4"><div class="card-body p-4"><h2 class="h5">Prompt snapshot</h2><p class="small text-body-secondary">This copy is preserved with the practice review. The audio itself is not stored on the server.</p><div class="preserve-lines">{{ $snapshot['instructions'] ?? '' }}</div></div></article>
    @if ($speakingSubmission->self_assessment)<article class="card mb-4"><div class="card-body p-4"><h2 class="h5">Your self-review</h2><dl class="row mb-0">@foreach ($speakingSubmission->self_assessment as $key => $value)<dt class="col-sm-5">{{ str_replace('_', ' ', ucfirst($key)) }}</dt><dd class="col-sm-7">{{ $value }} / 5</dd>@endforeach</dl></div></article>@endif
    @if ($speakingSubmission->notes)<article class="card mb-4"><div class="card-body p-4"><h2 class="h5">Notes for next attempt</h2><div class="preserve-lines">{{ $speakingSubmission->notes }}</div></div></article>@endif
    <div class="alert alert-info">This is learner reflection only. No audio file, official score, or server-side recording is attached to this review.</div>
    <a class="btn btn-outline-primary" href="{{ route('speaking.show', $speakingSubmission->speakingPrompt) }}">Back to prompt</a>
@endsection
