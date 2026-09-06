@extends('layouts.app')

@section('title', 'Manage exercises · B1 English Self-Study')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div><p class="text-primary fw-semibold mb-1">Manage</p><h1 class="h2 mb-0">Practice exercises</h1></div>
        <a class="btn btn-primary" href="{{ route('manage.exercises.create') }}">Create exercise</a>
    </div>

    <form class="card card-body mb-4" method="GET" action="{{ route('manage.exercises.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-4"><label class="form-label" for="search">Search</label><input class="form-control" id="search" name="search" value="{{ request('search') }}"></div>
            <div class="col-6 col-lg-2"><label class="form-label" for="skill">Skill</label><select class="form-select" id="skill" name="skill"><option value="">All</option>@foreach (\App\Models\Exercise::SKILLS as $skill)<option value="{{ $skill }}" @selected(request('skill') === $skill)>{{ ucfirst($skill) }}</option>@endforeach</select></div>
            <div class="col-6 col-lg-2"><label class="form-label" for="status">Status</label><select class="form-select" id="status" name="status"><option value="">All</option>@foreach (\App\Models\Exercise::STATUSES as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div class="col-6 col-lg-3"><label class="form-label" for="topic">Topic</label><select class="form-select" id="topic" name="topic"><option value="">All topics</option>@foreach ($topics as $topic)<option value="{{ $topic->id }}" @selected((int) request('topic') === $topic->id)>{{ $topic->name }}</option>@endforeach</select></div>
            <div class="col-6 col-lg-1 d-grid"><button class="btn btn-outline-primary" type="submit">Filter</button></div>
        </div>
    </form>

    @if ($exercises->isEmpty())
        <div class="card card-body text-center py-5"><h2 class="h5">No exercises found</h2><p class="text-body-secondary">Create a draft exercise and assign reviewed questions.</p><a class="btn btn-primary align-self-center" href="{{ route('manage.exercises.create') }}">Create exercise</a></div>
    @else
        <div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Exercise</th><th>Skill</th><th>Questions</th><th>Attempts</th><th>Status</th><th><span class="visually-hidden">Actions</span></th></tr></thead><tbody>
            @foreach ($exercises as $exercise)
                <tr><td><strong>{{ $exercise->title }}</strong><span class="d-block small text-body-secondary">#{{ $exercise->id }} · {{ $exercise->topic?->name ?? 'No topic' }}</span></td><td>{{ ucfirst($exercise->skill) }} · L{{ $exercise->difficulty }}</td><td>{{ $exercise->exercise_questions_count }}</td><td>{{ $exercise->attempts_count }}</td><td>@include('partials.status-badge', ['status' => $exercise->status])</td><td class="text-end table-actions"><div class="d-flex flex-wrap justify-content-end gap-2"><a class="btn btn-sm btn-outline-secondary" href="{{ route('manage.exercises.preview', $exercise) }}">Preview</a><a class="btn btn-sm btn-outline-primary" href="{{ route('manage.exercises.edit', $exercise) }}">Edit</a><form method="POST" action="{{ route('manage.exercises.status.update', $exercise) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $exercise->status === 'active' ? 'inactive' : 'active' }}"><button class="btn btn-sm btn-outline-secondary" type="submit">{{ $exercise->status === 'active' ? 'Deactivate' : 'Activate' }}</button></form></div></td></tr>
            @endforeach
        </tbody></table></div></div><div class="mt-4">{{ $exercises->links() }}</div>
    @endif
@endsection
