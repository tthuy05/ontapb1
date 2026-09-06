@extends('layouts.app')

@section('title', 'Listening · B1 English Self-Study')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div><p class="text-primary fw-semibold mb-1">Study reference</p><h1 class="h2 mb-0">Listening library</h1></div>
        <span class="text-body-secondary">Transcript and reviewed audio metadata</span>
    </div>
    <form class="card card-body mb-4" method="GET" action="{{ route('listening.index') }}"><div class="row g-3 align-items-end">
        <div class="col-12 col-lg-5"><label class="form-label" for="search">Search</label><input class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Title or transcript text"></div>
        <div class="col-6 col-lg-3"><label class="form-label" for="topic">Topic</label><select class="form-select" id="topic" name="topic"><option value="">All topics</option>@foreach ($topics as $topic)<option value="{{ $topic->id }}" @selected((string) request('topic') === (string) $topic->id)>{{ $topic->name }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-2"><label class="form-label" for="difficulty">Difficulty</label><select class="form-select" id="difficulty" name="difficulty"><option value="">All</option>@foreach ([1 => 'Easy', 2 => 'Core', 3 => 'Stretch'] as $value => $label)<option value="{{ $value }}" @selected((string) request('difficulty') === (string) $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-12 col-lg-2 d-grid"><button class="btn btn-outline-primary" type="submit">Filter</button></div>
    </div></form>
    @if ($contents->isEmpty())
        <div class="card card-body text-center py-5"><h2 class="h5">No active Listening items found</h2><p class="text-body-secondary mb-0">Ask the owner to activate an original Listening item.</p></div>
    @else
        <div class="row g-3">@foreach ($contents as $content)<div class="col-12 col-md-6"><article class="card h-100"><div class="card-body d-flex flex-column"><div class="d-flex justify-content-between gap-3 mb-2"><span class="badge text-bg-light">{{ ['1' => 'Easy', '2' => 'Core', '3' => 'Stretch'][$content->difficulty] ?? 'B1' }}</span><span class="small text-body-secondary">{{ $content->duration_seconds ? $content->duration_seconds.' sec' : 'Duration not set' }}</span></div><h2 class="h5">{{ $content->title }}</h2>@if ($content->topic)<p class="small text-body-secondary mb-2">{{ $content->topic->name }}</p>@endif<p class="text-body-secondary flex-grow-1">{{ $content->speaker_count ? $content->speaker_count.' speaker(s)' : 'Speaker count not set' }} · {{ $content->questions_count }} active questions</p><a class="btn btn-primary btn-sm align-self-start" href="{{ route('listening.show', $content) }}">Open listening item</a></div></article></div>@endforeach</div>
        <div class="mt-4">{{ $contents->links() }}</div>
    @endif
@endsection
