@extends('layouts.app')

@section('title', 'Manage topics · B1 English Self-Study')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div><p class="text-primary fw-semibold mb-1">Manage</p><h1 class="h2 mb-0">Topics</h1></div>
        <a class="btn btn-primary" href="{{ route('manage.topics.create') }}">Create topic</a>
    </div>

    <form class="card card-body mb-4" method="GET" action="{{ route('manage.topics.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-5"><label class="form-label" for="search">Search</label><input class="form-control" id="search" name="search" value="{{ request('search') }}"></div>
            <div class="col-6 col-lg-3">
                <label class="form-label" for="area">Area</label>
                <select class="form-select" id="area" name="area"><option value="">All areas</option>@foreach (\App\Models\Topic::AREAS as $area)<option value="{{ $area }}" @selected(request('area') === $area)>{{ ucfirst($area) }}</option>@endforeach</select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status"><option value="">All</option>@foreach (\App\Models\Topic::STATUSES as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
            </div>
            <div class="col-12 col-lg-2 d-grid"><button class="btn btn-outline-primary" type="submit">Filter</button></div>
        </div>
    </form>

    @if ($topics->isEmpty())
        <div class="card card-body text-center py-5"><h2 class="h5">No topics found</h2><p class="text-body-secondary">Create the first approved syllabus topic.</p><a class="btn btn-primary align-self-center" href="{{ route('manage.topics.create') }}">Create topic</a></div>
    @else
        <div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead><tr><th scope="col">Topic</th><th scope="col">Area</th><th scope="col">Priority</th><th scope="col">Content</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
            <tbody>
                @foreach ($topics as $topic)
                    <tr>
                        <td><strong>{{ $topic->name }}</strong><span class="d-block small text-body-secondary">{{ $topic->slug }}</span></td>
                        <td>{{ ucfirst($topic->area) }}</td><td>{{ $topic->priority }}</td>
                        <td>{{ $topic->vocabularies_count + $topic->grammar_lessons_count }}</td>
                        <td>@include('partials.status-badge', ['status' => $topic->status])</td>
                        <td class="text-end table-actions">
                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('manage.topics.edit', $topic) }}">Edit</a>
                                <form method="POST" action="{{ route('manage.topics.status.update', $topic) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $topic->status === 'active' ? 'inactive' : 'active' }}"><button class="btn btn-sm btn-outline-secondary" type="submit">{{ $topic->status === 'active' ? 'Deactivate' : 'Activate' }}</button></form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table></div></div>
        <div class="mt-4">{{ $topics->links() }}</div>
    @endif
@endsection
