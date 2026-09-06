@extends('layouts.app')

@section('title', $exam->title.' · Mock exam')

@section('content')
    <nav aria-label="Breadcrumb" class="mb-3">
        <a href="{{ route('exams.index') }}">Mock exams</a>
        <span aria-hidden="true"> / </span>
        <span>{{ $exam->title }}</span>
    </nav>

    <div class="card mb-4">
        <div class="card-body p-4 p-md-5">
            <p class="text-primary fw-semibold mb-2">{{ str_replace('_', ' ', ucfirst($exam->format_label)) }} · Objective practice</p>
            <h1 class="h2 mb-3">{{ $exam->title }}</h1>
            @if ($exam->description)<p class="lead text-body-secondary preserve-lines">{{ $exam->description }}</p>@endif
            @if ($exam->instructions)<div class="alert alert-primary preserve-lines" role="note">{{ $exam->instructions }}</div>@endif
            <dl class="row mb-4">
                <dt class="col-sm-4">Sections</dt><dd class="col-sm-8">{{ $exam->sections->count() }}</dd>
                <dt class="col-sm-4">Questions</dt><dd class="col-sm-8">{{ $exam->sections->sum(fn ($section) => $section->items->count()) }}</dd>
                <dt class="col-sm-4">Overall time</dt><dd class="col-sm-8">{{ $exam->time_limit_seconds ? ceil($exam->time_limit_seconds / 60).' minutes' : 'Untimed' }}</dd>
                <dt class="col-sm-4">Result</dt><dd class="col-sm-8">Raw points and percentage only; not an official VSTEP score</dd>
            </dl>

            @if ($resumableAttempt)
                <div class="alert alert-info" role="status">
                    You have an in-progress attempt from {{ $resumableAttempt->started_at->format('Y-m-d H:i') }}.
                    <a href="{{ route('attempts.show', $resumableAttempt) }}">Resume it</a> or start a new attempt below.
                </div>
            @endif

            <form method="POST" action="{{ route('exams.attempts.store', $exam) }}">
                @csrf
                <button class="btn btn-primary" type="submit">Start mock exam</button>
            </form>
        </div>
    </div>

    <section aria-labelledby="exam-sections-heading">
        <h2 class="h4" id="exam-sections-heading">Sections</h2>
        <div class="vstack gap-3">
            @foreach ($exam->sections as $section)
                <article class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between gap-3">
                            <h3 class="h5">{{ $section->position + 1 }}. {{ $section->title }}</h3>
                            <span class="badge text-bg-light">{{ ucfirst($section->skill) }}</span>
                        </div>
                        @if ($section->instructions)<p class="text-body-secondary preserve-lines">{{ $section->instructions }}</p>@endif
                        <p class="small text-body-secondary mb-0">{{ $section->items->count() }} {{ Str::plural('question', $section->items->count()) }} · {{ $section->time_limit_seconds ? ceil($section->time_limit_seconds / 60).' min section guide' : 'No section limit' }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <div class="alert alert-warning mt-4" role="note">This simulation uses raw objective points and does not predict an official VSTEP band.</div>
@endsection
