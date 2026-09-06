@extends('layouts.app')

@section('title', 'Manage vocabulary · B1 English Self-Study')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div><p class="text-primary fw-semibold mb-1">Manage</p><h1 class="h2 mb-0">Vocabulary</h1></div>
        <a class="btn btn-primary" href="{{ route('manage.vocabulary.create') }}">Create entry</a>
    </div>

    <form class="card card-body mb-4" method="GET" action="{{ route('manage.vocabulary.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-4"><label class="form-label" for="search">Search</label><input class="form-control" id="search" name="search" value="{{ request('search') }}"></div>
            <div class="col-6 col-lg-3"><label class="form-label" for="topic">Topic</label><select class="form-select" id="topic" name="topic"><option value="">All topics</option>@foreach ($topics as $topic)<option value="{{ $topic->id }}" @selected((string) request('topic') === (string) $topic->id)>{{ $topic->name }}</option>@endforeach</select></div>
            <div class="col-6 col-lg-2"><label class="form-label" for="status">Status</label><select class="form-select" id="status" name="status"><option value="">All</option>@foreach (\App\Models\Vocabulary::STATUSES as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div class="col-8 col-lg-2"><label class="form-label" for="source_type">Source</label><select class="form-select" id="source_type" name="source_type"><option value="">All</option>@foreach (\App\Models\Vocabulary::SOURCE_TYPES as $sourceType)<option value="{{ $sourceType }}" @selected(request('source_type') === $sourceType)>{{ str_replace('_', ' ', ucfirst($sourceType)) }}</option>@endforeach</select></div>
            <div class="col-4 col-lg-1 d-grid"><button class="btn btn-outline-primary" type="submit">Filter</button></div>
        </div>
    </form>

    @if ($vocabularies->isEmpty())
        <div class="card card-body text-center py-5"><h2 class="h5">No vocabulary entries found</h2><p class="text-body-secondary">Create a small original entry and review it before activation.</p><a class="btn btn-primary align-self-center" href="{{ route('manage.vocabulary.create') }}">Create entry</a></div>
    @else
        <div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead><tr><th scope="col">Term</th><th scope="col">Topic</th><th scope="col">Progress</th><th scope="col">Source</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
            <tbody>
                @foreach ($vocabularies as $vocabulary)
                    <tr>
                        <td><strong>{{ $vocabulary->term }}</strong><span class="d-block small text-body-secondary">{{ $vocabulary->part_of_speech ?: '—' }}</span></td>
                        <td>{{ $vocabulary->topic->name }}</td><td>{{ ucfirst($vocabulary->progress?->state ?? 'new') }}</td><td>{{ str_replace('_', ' ', ucfirst($vocabulary->source_type)) }}</td>
                        <td>@include('partials.status-badge', ['status' => $vocabulary->status])</td>
                        <td class="text-end table-actions"><div class="d-flex flex-wrap justify-content-end gap-2"><a class="btn btn-sm btn-outline-secondary" href="{{ route('manage.vocabulary.show', $vocabulary) }}">Preview</a><a class="btn btn-sm btn-outline-primary" href="{{ route('manage.vocabulary.edit', $vocabulary) }}">Edit</a><form method="POST" action="{{ route('manage.vocabulary.status.update', $vocabulary) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $vocabulary->status === 'active' ? 'inactive' : 'active' }}"><button class="btn btn-sm btn-outline-secondary" type="submit">{{ $vocabulary->status === 'active' ? 'Deactivate' : 'Activate' }}</button></form></div></td>
                    </tr>
                @endforeach
            </tbody>
        </table></div></div>
        <div class="mt-4">{{ $vocabularies->links() }}</div>
    @endif
@endsection
