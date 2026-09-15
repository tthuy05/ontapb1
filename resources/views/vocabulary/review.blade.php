@extends('layouts.app')

@section('title', 'Vocabulary review · B1 English Self-Study')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <p class="text-primary fw-semibold mb-1">Spaced review</p>
            <h1 class="h2 mb-1">Vocabulary review queue</h1>
            <p class="text-body-secondary mb-0">
                {{ $reviewSummary['due'] }} due now · {{ $reviewSummary['new'] }} new available. Up to five new cards join each queue.
            </p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('vocabulary.index') }}">Browse vocabulary</a>
    </div>

    @forelse ($reviewItems as $vocabulary)
        @php($schedule = $vocabulary->progress?->reviewSchedule)
        <article class="card shadow-sm mb-4" aria-labelledby="review-word-{{ $vocabulary->id }}">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 mb-3">
                    <div>
                        <p class="small text-primary fw-semibold mb-1">{{ $vocabulary->topic->name }}</p>
                        <h2 class="h3 mb-1" id="review-word-{{ $vocabulary->id }}">{{ $vocabulary->term }}</h2>
                        <p class="small text-body-secondary mb-0">{{ $vocabulary->part_of_speech ?: 'Part of speech not specified' }}</p>
                    </div>
                    <span class="badge text-bg-light align-self-start">
                        @if ($schedule)
                            Due {{ $schedule->due_at->diffForHumans() }}
                        @elseif (in_array($vocabulary->progress?->state, ['learning', 'review'], true))
                            Existing review
                        @else
                            New card
                        @endif
                    </span>
                </div>

                <details class="border rounded-3 p-3 mb-3">
                    <summary class="fw-semibold">Reveal meaning and example</summary>
                    <div class="pt-3">
                        <p class="mb-2">{{ $vocabulary->definition }}</p>
                        @if ($vocabulary->translation)
                            <p class="text-body-secondary mb-2"><span class="fw-semibold">Vietnamese:</span> {{ $vocabulary->translation }}</p>
                        @endif
                        @if ($vocabulary->example_sentence)
                            <p class="mb-0"><span class="fw-semibold">Example:</span> {{ $vocabulary->example_sentence }}</p>
                        @endif
                    </div>
                </details>

                <form method="POST" action="{{ route('vocabulary.review.store', $vocabulary) }}">
                    @csrf
                    <fieldset>
                        <legend class="h6">How well did you remember it?</legend>
                        <div class="row g-2">
                            <div class="col-6 col-lg-3 d-grid"><button class="btn btn-outline-danger" type="submit" name="rating" value="again">Again · 10 min</button></div>
                            <div class="col-6 col-lg-3 d-grid"><button class="btn btn-outline-secondary" type="submit" name="rating" value="hard">Hard</button></div>
                            <div class="col-6 col-lg-3 d-grid"><button class="btn btn-outline-primary" type="submit" name="rating" value="good">Good</button></div>
                            <div class="col-6 col-lg-3 d-grid"><button class="btn btn-primary" type="submit" name="rating" value="easy">Easy</button></div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </article>
    @empty
        <div class="card card-body text-center py-5">
            <h2 class="h4">All caught up</h2>
            <p class="text-body-secondary">No active vocabulary is due and there are no new cards waiting.</p>
            <a class="btn btn-primary align-self-center" href="{{ route('vocabulary.index') }}">Browse vocabulary</a>
        </div>
    @endforelse
@endsection
