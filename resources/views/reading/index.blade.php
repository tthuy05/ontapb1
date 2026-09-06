@extends('layouts.app')

@section('title', 'Reading · B1 English Self-Study')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div><p class="text-primary fw-semibold mb-1">Study reference</p><h1 class="h2 mb-0">Reading library</h1></div>
        <span class="text-body-secondary">Original and reviewed passages</span>
    </div>

    <form class="card card-body mb-4" method="GET" action="{{ route('reading.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-5"><label class="form-label" for="search">Search</label><input class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Title or passage text"></div>
            <div class="col-6 col-lg-3"><label class="form-label" for="topic">Topic</label><select class="form-select" id="topic" name="topic"><option value="">All topics</option>@foreach ($topics as $topic)<option value="{{ $topic->id }}" @selected((string) request('topic') === (string) $topic->id)>{{ $topic->name }}</option>@endforeach</select></div>
            <div class="col-6 col-lg-2"><label class="form-label" for="difficulty">Difficulty</label><select class="form-select" id="difficulty" name="difficulty"><option value="">All</option>@foreach ([1 => 'Easy', 2 => 'Core', 3 => 'Stretch'] as $value => $label)<option value="{{ $value }}" @selected((string) request('difficulty') === (string) $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-12 col-lg-2 d-grid"><button class="btn btn-outline-primary" type="submit">Filter</button></div>
        </div>
    </form>

    @if ($passages->isEmpty())
        <div class="card card-body text-center py-5"><h2 class="h5">No active passages found</h2><p class="text-body-secondary mb-0">Ask the owner to activate an original Reading passage.</p></div>
    @else
        <div class="row g-3">
            @foreach ($passages as $passage)
                <div class="col-12 col-md-6">
                    <article class="card h-100"><div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between gap-3 mb-2"><span class="badge text-bg-light">{{ ['1' => 'Easy', '2' => 'Core', '3' => 'Stretch'][$passage->difficulty] ?? 'B1' }}</span><span class="small text-body-secondary">{{ $passage->word_count }} words</span></div>
                        <h2 class="h5">{{ $passage->title }}</h2>
                        @if ($passage->topic)<p class="small text-body-secondary mb-2">{{ $passage->topic->name }}</p>@endif
                        <p class="preserve-lines text-body-secondary flex-grow-1">{{ Illuminate\Support\Str::limit($passage->body, 220) }}</p>
                        <div class="d-flex justify-content-between align-items-center gap-2"><span class="small text-body-secondary">{{ $passage->questions_count }} active questions</span><a class="btn btn-primary btn-sm" href="{{ route('reading.show', $passage) }}">Read passage</a></div>
                    </div></article>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $passages->links() }}</div>
    @endif
@endsection
