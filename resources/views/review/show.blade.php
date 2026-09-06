@extends('layouts.app')

@section('title', 'Review question · B1 English Self-Study')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
        <div>
            <p class="text-primary fw-semibold mb-1">Wrong-answer review</p>
            <h1 class="h2 mb-1">{{ $attempt->snapshot_title }}</h1>
            <p class="text-body-secondary mb-0">Attempt #{{ $attempt->id }} · Question {{ $attemptAnswer->question_position + 1 }}</p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('review.wrong.index', ['attempt' => $attempt->id]) }}">Back to review</a>
    </div>

    @if (is_array($context) && ($context['kind'] ?? null) === 'passage')
        <div class="example-panel rounded p-3 mb-4"><h2 class="h6">{{ $context['title'] ?? 'Reading passage' }}</h2><p class="preserve-lines mb-0">{{ $context['body'] ?? '' }}</p></div>
    @elseif (is_array($context) && ($context['kind'] ?? null) === 'listening')
        <div class="example-panel rounded p-3 mb-4"><h2 class="h6">{{ $context['title'] ?? 'Listening item' }}</h2><p class="preserve-lines mb-0">{{ $context['transcript'] ?? '' }}</p></div>
    @endif

    <article class="card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between gap-3 mb-3"><span class="badge text-bg-danger">Incorrect</span><span class="text-body-secondary">{{ $snapshot['skill'] ?? 'Not recorded' }} · {{ $snapshot['type'] ?? 'Not recorded' }}</span></div>
            <h2 class="h4">{{ $snapshot['prompt'] ?? 'Question snapshot unavailable' }}</h2>
            <ol class="mt-4 mb-4">
                @foreach ($options as $option)
                    <li class="{{ $option['key'] === $response ? 'text-danger fw-semibold' : '' }} {{ in_array($option['key'], $correctOptions->pluck('key')->all(), true) ? 'text-success fw-semibold' : '' }}">{{ $option['key'] }}. {{ $option['content'] }} @if ($option['key'] === $response)<span class="badge text-bg-light">Your answer</span>@endif @if (in_array($option['key'], $correctOptions->pluck('key')->all(), true))<span class="small">Correct</span>@endif</li>
                @endforeach
            </ol>
            @if ($snapshot['explanation'] ?? null)<div class="border-top pt-3"><h3 class="h6">Explanation</h3><p class="preserve-lines mb-0">{{ $snapshot['explanation'] }}</p></div>@endif
        </div>
    </article>

    <div class="d-flex flex-wrap gap-2 mt-4">
        @if ($attempt->exercise?->status === 'active' && ($attempt->exercise->topic?->status === 'active' || $attempt->exercise->topic_id === null))
            <a class="btn btn-primary" href="{{ route('practice.show', $attempt->exercise) }}">Try practice again</a>
        @endif
        <a class="btn btn-outline-secondary" href="{{ route('attempts.result', $attempt) }}">View result</a>
    </div>
@endsection
