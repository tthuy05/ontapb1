@extends('layouts.app')

@section('title', 'Practice · B1 English Self-Study')

@section('content')
    <div class="mb-4">
        <p class="text-primary fw-semibold mb-1">Study practice</p>
        <h1 class="h2 mb-1">Practice exercises</h1>
        <p class="text-body-secondary mb-0">Take a short, resumable exercise built from the reviewed question bank.</p>
    </div>

    <form class="card card-body mb-4" method="GET" action="{{ route('practice.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-4">
                <label class="form-label" for="search">Search</label>
                <input class="form-control" id="search" name="search" value="{{ request('search') }}">
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label" for="skill">Skill</label>
                <select class="form-select" id="skill" name="skill">
                    <option value="">All</option>
                    @foreach (\App\Models\Exercise::SKILLS as $skill)
                        <option value="{{ $skill }}" @selected(request('skill') === $skill)>{{ ucfirst($skill) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-3">
                <label class="form-label" for="topic">Topic</label>
                <select class="form-select" id="topic" name="topic">
                    <option value="">All topics</option>
                    @foreach ($topics as $topic)
                        <option value="{{ $topic->id }}" @selected((int) request('topic') === $topic->id)>{{ $topic->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label" for="difficulty">Difficulty</label>
                <select class="form-select" id="difficulty" name="difficulty">
                    <option value="">All</option>
                    @foreach (\App\Models\Exercise::DIFFICULTIES as $difficulty)
                        <option value="{{ $difficulty }}" @selected((int) request('difficulty') === $difficulty)>Level {{ $difficulty }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-1 d-grid">
                <button class="btn btn-outline-primary" type="submit">Filter</button>
            </div>
        </div>
    </form>

    @if ($exercises->isEmpty())
        <div class="card card-body text-center py-5">
            <h2 class="h5">No exercises found</h2>
            <p class="text-body-secondary mb-0">Ask the owner to create and activate a practice exercise.</p>
        </div>
    @else
        <div class="row g-3">
            @foreach ($exercises as $exercise)
                <div class="col-12 col-md-6">
                    <article class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between gap-3 mb-2">
                                <span class="badge text-bg-light">{{ ucfirst($exercise->skill) }}</span>
                                <span class="small text-body-secondary">Difficulty {{ $exercise->difficulty }}</span>
                            </div>
                            <h2 class="h5">{{ $exercise->title }}</h2>
                            <p class="text-body-secondary">{{ $exercise->instructions ?: 'A focused objective practice set.' }}</p>
                            <p class="small text-body-secondary mb-3">
                                {{ $exercise->exercise_questions_count }} {{ Str::plural('question', $exercise->exercise_questions_count) }}
                                @if ($exercise->time_limit_seconds)
                                    · {{ ceil($exercise->time_limit_seconds / 60) }} min limit
                                @else
                                    · Untimed
                                @endif
                            </p>
                            <a class="btn btn-outline-primary mt-auto align-self-start" href="{{ route('practice.show', $exercise) }}">View exercise</a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $exercises->links() }}</div>
    @endif
@endsection
