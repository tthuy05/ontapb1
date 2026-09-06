@extends('layouts.app')

@section('title', 'Practice history · B1 English Self-Study')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <p class="text-primary fw-semibold mb-1">Practice feedback</p>
            <h1 class="h2 mb-1">Practice history</h1>
            <p class="text-body-secondary mb-0">Review attempts using the configuration captured when each attempt began.</p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('review.wrong.index') }}">Wrong-answer review</a>
    </div>

    <form class="card card-body mb-4" method="GET" action="{{ route('history.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-5"><label class="form-label" for="history-search">Search title</label><input class="form-control" id="history-search" name="search" value="{{ $search }}" maxlength="100"></div>
            <div class="col-md-3"><label class="form-label" for="history-status">Status</label><select class="form-select" id="history-status" name="status"><option value="">All statuses</option>@foreach ($statuses as $value)<option value="{{ $value }}" @selected($status === $value)>{{ ucfirst(str_replace('_', ' ', $value)) }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label" for="history-skill">Skill</label><select class="form-select" id="history-skill" name="skill"><option value="">All skills</option>@foreach ($skills as $value)<option value="{{ $value }}" @selected($skill === $value)>{{ ucfirst($value) }}</option>@endforeach</select></div>
            <div class="col-md-1"><button class="btn btn-primary w-100" type="submit">Filter</button></div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th scope="col">Practice target</th><th scope="col">Skill</th><th scope="col">Started</th><th scope="col">Status</th><th scope="col">Score</th><th scope="col" class="text-end">Action</th></tr></thead>
                <tbody>
                    @forelse ($attempts as $attempt)
                        <tr>
                            <th scope="row">{{ $attempt->snapshot_title }}</th>
                            <td>{{ ucfirst($attempt->snapshot_skill ?? 'Not recorded') }}</td>
                            <td>{{ $attempt->started_at?->format('Y-m-d H:i') }}</td>
                            <td><span class="badge text-bg-{{ $attempt->status === 'submitted' ? 'success' : ($attempt->status === 'in_progress' ? 'warning' : 'secondary') }}">{{ ucfirst(str_replace('_', ' ', $attempt->status)) }}</span></td>
                            <td>{{ $attempt->status === 'submitted' ? $attempt->percentage.'%' : '—' }}</td>
                            <td class="text-end table-actions">
                                @if ($attempt->status === 'submitted')
                                    <a href="{{ route('attempts.result', $attempt) }}">Result</a>
                                @elseif ($attempt->status === 'in_progress')
                                    <a href="{{ route('attempts.show', $attempt) }}">Resume</a>
                                @else
                                    <span class="text-body-secondary">Finished</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td class="text-body-secondary p-4" colspan="6">No attempts match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($attempts->hasPages())<div class="card-footer">{{ $attempts->links() }}</div>@endif
    </div>
@endsection
