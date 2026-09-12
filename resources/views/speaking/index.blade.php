@extends('layouts.app')

@section('title', 'Speaking practice')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
        <div><p class="text-primary fw-semibold mb-1">Speaking practice</p><h1 class="h2 mb-1">Rehearse with a local recording</h1><p class="text-body-secondary mb-0">Use timed prompts, play back your own response, and record a short self-review. Audio never leaves this browser.</p></div>
    </div>
    <form class="card card-body mb-4" method="GET" action="{{ route('speaking.index') }}">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-7"><label class="form-label" for="search">Search prompts</label><input class="form-control" id="search" name="search" value="{{ request('search') }}"></div>
            <div class="col-8 col-md-3"><label class="form-label" for="part_type">Part</label><select class="form-select" id="part_type" name="part_type"><option value="">All parts</option>@foreach ($partTypes as $partType)<option value="{{ $partType }}" @selected(request('part_type') === $partType)>{{ str_replace('_', ' ', ucfirst($partType)) }}</option>@endforeach</select></div>
            <div class="col-4 col-md-2"><button class="btn btn-primary w-100" type="submit">Filter</button></div>
        </div>
    </form>
    <div class="row g-3">
        @forelse ($prompts as $prompt)
            <div class="col-12 col-lg-6">
                <a class="card h-100 text-decoration-none summary-card" href="{{ route('speaking.show', $prompt) }}">
                    <div class="card-body p-4"><div class="d-flex justify-content-between gap-3"><span class="badge text-bg-light">{{ str_replace('_', ' ', ucfirst($prompt->part_type)) }}</span><span class="small text-body-secondary">{{ $prompt->submitted_count }} review(s)</span></div><h2 class="h4 text-body mt-3">{{ $prompt->title }}</h2><p class="text-body-secondary mb-2">{{ $prompt->preparation_seconds ?? 0 }} seconds preparation · {{ $prompt->speaking_seconds ?? 'Flexible' }}{{ $prompt->speaking_seconds ? ' seconds speaking' : '' }}</p><p class="text-body mb-0">{{ $prompt->instructions }}</p></div>
                </a>
            </div>
        @empty
            <div class="col-12"><div class="card card-body"><p class="mb-0 text-body-secondary">No active speaking prompts are available.</p></div></div>
        @endforelse
    </div>
    <div class="mt-4">{{ $prompts->links() }}</div>
@endsection
