@extends('layouts.app')

@section('title', 'Wrong-answer review · B1 English Self-Study')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <p class="text-primary fw-semibold mb-1">Snapshot-based feedback</p>
            <h1 class="h2 mb-1">Wrong-answer review</h1>
            <p class="text-body-secondary mb-0">These questions come from submitted attempts and keep the wording shown at the time.</p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('history.index') }}">Practice history</a>
    </div>

    <form class="card card-body mb-4" method="GET" action="{{ route('review.wrong.index') }}">
        @if (request('attempt'))<input type="hidden" name="attempt" value="{{ request('attempt') }}">@endif
        <div class="row g-3 align-items-end">
            <div class="col-md-5"><label class="form-label" for="review-search">Search prompt</label><input class="form-control" id="review-search" name="search" value="{{ $search }}" maxlength="100"></div>
            <div class="col-md-3"><label class="form-label" for="review-skill">Skill</label><select class="form-select" id="review-skill" name="skill"><option value="">All skills</option>@foreach ($skills as $value)<option value="{{ $value }}" @selected($skill === $value)>{{ ucfirst($value) }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label" for="review-type">Question type</label><select class="form-select" id="review-type" name="type"><option value="">All types</option>@foreach ($types as $value)<option value="{{ $value }}" @selected($type === $value)>{{ ucfirst(str_replace('_', ' ', $value)) }}</option>@endforeach</select></div>
            <div class="col-md-1"><button class="btn btn-primary w-100" type="submit">Filter</button></div>
        </div>
    </form>

    <div class="vstack gap-3">
        @forelse ($answers as $answer)
            @php($snapshot = is_array($answer->question_snapshot) ? $answer->question_snapshot : [])
            <article class="card">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                        <div>
                            <p class="small text-body-secondary mb-1">{{ $answer->attempt?->snapshot_title }} · Question {{ $answer->question_position + 1 }}</p>
                            <h2 class="h5 mb-2">{{ $snapshot['prompt'] ?? 'Question snapshot unavailable' }}</h2>
                            <p class="mb-0 text-body-secondary">{{ ucfirst($snapshot['skill'] ?? 'Not recorded') }} · {{ ucfirst(str_replace('_', ' ', $snapshot['type'] ?? 'Not recorded')) }}</p>
                        </div>
                        <a class="btn btn-outline-danger align-self-start" href="{{ route('review.wrong.show', $answer) }}">Review answer</a>
                    </div>
                </div>
            </article>
        @empty
            <div class="card"><div class="card-body p-4"><h2 class="h5">No wrong answers found</h2><p class="text-body-secondary mb-0">Submit a practice attempt with an incorrect answer to build this review queue.</p></div></div>
        @endforelse
    </div>
    @if ($answers->hasPages())<div class="mt-4">{{ $answers->links() }}</div>@endif
@endsection
