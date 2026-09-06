@extends('layouts.app')

@section('title', 'Manage mock exams · B1 English Self-Study')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div><p class="text-primary fw-semibold mb-1">Manage</p><h1 class="h2 mb-0">Mock exams</h1></div>
        <a class="btn btn-primary" href="{{ route('manage.exams.create') }}">Create mock exam</a>
    </div>

    <form class="card card-body mb-4" method="GET" action="{{ route('manage.exams.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-5"><label class="form-label" for="exam-search">Search</label><input class="form-control" id="exam-search" name="search" value="{{ request('search') }}" maxlength="100"></div>
            <div class="col-6 col-lg-3"><label class="form-label" for="exam-format">Format</label><select class="form-select" id="exam-format" name="format"><option value="">All formats</option>@foreach (\App\Models\Exam::FORMAT_LABELS as $format)<option value="{{ $format }}" @selected(request('format') === $format)>{{ str_replace('_', ' ', ucfirst($format)) }}</option>@endforeach</select></div>
            <div class="col-6 col-lg-3"><label class="form-label" for="exam-status">Status</label><select class="form-select" id="exam-status" name="status"><option value="">All</option>@foreach (\App\Models\Exam::STATUSES as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div class="col-12 col-lg-1 d-grid"><button class="btn btn-outline-primary" type="submit">Filter</button></div>
        </div>
    </form>

    @if ($exams->isEmpty())
        <div class="card card-body text-center py-5"><h2 class="h5">No mock exams found</h2><p class="text-body-secondary">Create a draft exam and add reviewed objective questions.</p><a class="btn btn-primary align-self-center" href="{{ route('manage.exams.create') }}">Create mock exam</a></div>
    @else
        <div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Exam</th><th>Format</th><th>Sections</th><th>Attempts</th><th>Status</th><th><span class="visually-hidden">Actions</span></th></tr></thead><tbody>
            @foreach ($exams as $exam)
                <tr><td><strong>{{ $exam->title }}</strong><span class="d-block small text-body-secondary">#{{ $exam->id }}</span></td><td>{{ str_replace('_', ' ', ucfirst($exam->format_label)) }}</td><td>{{ $exam->sections_count }}</td><td>{{ $exam->attempts_count }}</td><td>@include('partials.status-badge', ['status' => $exam->status])</td><td class="text-end table-actions"><div class="d-flex flex-wrap justify-content-end gap-2"><a class="btn btn-sm btn-outline-secondary" href="{{ route('manage.exams.preview', $exam) }}">Preview</a><a class="btn btn-sm btn-outline-primary" href="{{ route('manage.exams.edit', $exam) }}">Edit</a><form method="POST" action="{{ route('manage.exams.status.update', $exam) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $exam->status === 'active' ? 'inactive' : 'active' }}"><button class="btn btn-sm btn-outline-secondary" type="submit">{{ $exam->status === 'active' ? 'Deactivate' : 'Activate' }}</button></form></div></td></tr>
            @endforeach
        </tbody></table></div></div><div class="mt-4">{{ $exams->links() }}</div>
    @endif
@endsection
