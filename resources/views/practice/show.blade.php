@extends('layouts.app')

@section('title', $exercise->title.' · Practice')

@section('content')
    <nav aria-label="Breadcrumb" class="mb-3">
        <a href="{{ route('practice.index') }}">Practice</a>
        <span aria-hidden="true"> / </span>
        <span>{{ $exercise->title }}</span>
    </nav>

    <div class="card mb-4">
        <div class="card-body p-4 p-md-5">
            <p class="text-primary fw-semibold mb-2">{{ ucfirst($exercise->skill) }} practice · Difficulty {{ $exercise->difficulty }}</p>
            <h1 class="h2 mb-3">{{ $exercise->title }}</h1>
            @if ($exercise->instructions)
                <p class="lead text-body-secondary preserve-lines">{{ $exercise->instructions }}</p>
            @endif
            <dl class="row mb-4">
                <dt class="col-sm-4">Questions</dt><dd class="col-sm-8">{{ $exercise->exerciseQuestions->count() }}</dd>
                <dt class="col-sm-4">Time limit</dt><dd class="col-sm-8">{{ $exercise->time_limit_seconds ? ceil($exercise->time_limit_seconds / 60).' minutes' : 'Untimed' }}</dd>
                <dt class="col-sm-4">Feedback</dt><dd class="col-sm-8">After you submit</dd>
            </dl>

            @if ($resumableAttempt)
                <div class="alert alert-info" role="status">
                    You have an in-progress attempt from {{ $resumableAttempt->started_at->format('Y-m-d H:i') }}.
                    <a href="{{ route('attempts.show', $resumableAttempt) }}">Resume it</a> or start a new attempt below.
                </div>
            @endif

            <form method="POST" action="{{ route('practice.attempts.store', $exercise) }}">
                @csrf
                <button class="btn btn-primary" type="submit">Start new attempt</button>
            </form>
        </div>
    </div>

    <section aria-labelledby="exercise-rules-heading">
        <h2 class="h4" id="exercise-rules-heading">Before you start</h2>
        <ul>
            <li>Your answers are saved to this owner-only practice session.</li>
            <li>The exercise is snapshotted when it starts, so later content edits do not change its result.</li>
            <li>Correct answers and explanations appear only after submission.</li>
        </ul>
    </section>
@endsection
