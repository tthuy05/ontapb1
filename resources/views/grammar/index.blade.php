@extends('layouts.app')

@section('title', 'Grammar · B1 English Self-Study')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <p class="text-primary fw-semibold mb-1">Study reference</p>
            <h1 class="h2 mb-1">Grammar</h1>
            <p class="text-body-secondary mb-0">Read active lessons in syllabus order.</p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('manage.grammar.index') }}">Manage grammar</a>
    </div>

    <form class="card card-body mb-4" method="GET" action="{{ route('grammar.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-6">
                <label class="form-label" for="search">Search</label>
                <input class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Lesson title or objective">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label" for="topic">Topic</label>
                <select class="form-select" id="topic" name="topic">
                    <option value="">All topics</option>
                    @foreach ($topics as $topic)
                        <option value="{{ $topic->id }}" @selected((string) request('topic') === (string) $topic->id)>{{ $topic->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-grid">
                <button class="btn btn-primary" type="submit">Filter</button>
            </div>
        </div>
    </form>

    @if ($lessons->isEmpty())
        <div class="card card-body text-center py-5">
            <h2 class="h5">No active grammar lessons found</h2>
            <p class="text-body-secondary mb-3">Adjust the filters or add and activate a lesson in Manage.</p>
            <a class="btn btn-primary align-self-center" href="{{ route('manage.grammar.create') }}">Create grammar lesson</a>
        </div>
    @else
        <div class="list-group shadow-sm">
            @foreach ($lessons as $lesson)
                <a class="list-group-item list-group-item-action p-3 p-md-4" href="{{ route('grammar.show', $lesson) }}">
                    <div class="d-flex justify-content-between gap-3">
                        <div>
                            <p class="small text-primary fw-semibold mb-1">{{ $lesson->topic->name }}</p>
                            <h2 class="h5 mb-2">{{ $lesson->title }}</h2>
                            <p class="text-body-secondary mb-0">{{ $lesson->objectives }}</p>
                        </div>
                        <span class="text-body-secondary" aria-hidden="true">→</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-4">{{ $lessons->links() }}</div>
    @endif
@endsection
