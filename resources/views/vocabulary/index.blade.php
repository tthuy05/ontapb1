@extends('layouts.app')

@section('title', 'Vocabulary · B1 English Self-Study')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <p class="text-primary fw-semibold mb-1">Study reference</p>
            <h1 class="h2 mb-1">Vocabulary</h1>
            <p class="text-body-secondary mb-0">Browse active words and keep a simple review state.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-primary" href="{{ route('vocabulary.review.index') }}">Review vocabulary</a>
            <a class="btn btn-outline-primary" href="{{ route('manage.vocabulary.index') }}">Manage vocabulary</a>
        </div>
    </div>

    <form class="card card-body mb-4" method="GET" action="{{ route('vocabulary.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-5">
                <label class="form-label" for="search">Search</label>
                <input class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Term, meaning, or translation">
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <label class="form-label" for="topic">Topic</label>
                <select class="form-select" id="topic" name="topic">
                    <option value="">All topics</option>
                    @foreach ($topics as $topic)
                        <option value="{{ $topic->id }}" @selected((string) request('topic') === (string) $topic->id)>{{ $topic->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6 col-lg-2">
                <label class="form-label" for="state">Review state</label>
                <select class="form-select" id="state" name="state">
                    <option value="">All states</option>
                    @foreach (['new', 'learning', 'learned', 'review'] as $state)
                        <option value="{{ $state }}" @selected(request('state') === $state)>{{ ucfirst($state) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-lg-2 d-grid">
                <button class="btn btn-primary" type="submit">Filter</button>
            </div>
        </div>
    </form>

    @if ($vocabularies->isEmpty())
        <div class="card card-body text-center py-5">
            <h2 class="h5">No active vocabulary found</h2>
            <p class="text-body-secondary mb-3">Adjust the filters or add and activate an entry in Manage.</p>
            <a class="btn btn-primary align-self-center" href="{{ route('manage.vocabulary.create') }}">Create vocabulary</a>
        </div>
    @else
        <div class="row g-3">
            @foreach ($vocabularies as $vocabulary)
                <div class="col-12 col-md-6">
                    <article class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between gap-3">
                                <div>
                                    <h2 class="h5 mb-1"><a href="{{ route('vocabulary.show', $vocabulary) }}">{{ $vocabulary->term }}</a></h2>
                                    <p class="small text-body-secondary mb-3">
                                        {{ $vocabulary->part_of_speech ?: 'Unspecified part of speech' }} · {{ $vocabulary->topic->name }}
                                    </p>
                                </div>
                                <span class="badge text-bg-light align-self-start">{{ ucfirst($vocabulary->progress?->state ?? 'new') }}</span>
                            </div>
                            <p class="mb-0">{{ $vocabulary->definition }}</p>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $vocabularies->links() }}</div>
    @endif
@endsection
