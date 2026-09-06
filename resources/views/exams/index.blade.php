@extends('layouts.app')

@section('title', 'Mock exams · B1 English Self-Study')

@section('content')
    <div class="mb-4">
        <p class="text-primary fw-semibold mb-1">Study practice</p>
        <h1 class="h2 mb-1">Mock exams</h1>
        <p class="text-body-secondary mb-0">Complete a configurable Reading/Listening simulation built from the reviewed question bank.</p>
    </div>

    <form class="card card-body mb-4" method="GET" action="{{ route('exams.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-7">
                <label class="form-label" for="exam-search">Search</label>
                <input class="form-control" id="exam-search" name="search" value="{{ request('search') }}" maxlength="100">
            </div>
            <div class="col-8 col-lg-3">
                <label class="form-label" for="exam-format">Format</label>
                <select class="form-select" id="exam-format" name="format">
                    <option value="">All formats</option>
                    @foreach (\App\Models\Exam::FORMAT_LABELS as $format)
                        <option value="{{ $format }}" @selected(request('format') === $format)>{{ str_replace('_', ' ', ucfirst($format)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-4 col-lg-2 d-grid"><button class="btn btn-outline-primary" type="submit">Filter</button></div>
        </div>
    </form>

    @if ($exams->isEmpty())
        <div class="card card-body text-center py-5">
            <h2 class="h5">No mock exams found</h2>
            <p class="text-body-secondary mb-0">Ask the owner to create and activate a reviewed exam.</p>
        </div>
    @else
        <div class="row g-3">
            @foreach ($exams as $exam)
                <div class="col-12 col-md-6">
                    <article class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between gap-3 mb-2">
                                <span class="badge text-bg-light">{{ str_replace('_', ' ', ucfirst($exam->format_label)) }}</span>
                                <span class="small text-body-secondary">{{ $exam->sections_count }} {{ Str::plural('section', $exam->sections_count) }}</span>
                            </div>
                            <h2 class="h5">{{ $exam->title }}</h2>
                            <p class="text-body-secondary">{{ $exam->description ?: 'A configurable objective practice simulation.' }}</p>
                            <p class="small text-body-secondary mb-3">{{ $exam->time_limit_seconds ? ceil($exam->time_limit_seconds / 60).' min overall limit' : 'Untimed' }}</p>
                            <a class="btn btn-outline-primary mt-auto align-self-start" href="{{ route('exams.show', $exam) }}">View mock exam</a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $exams->links() }}</div>
    @endif
@endsection
