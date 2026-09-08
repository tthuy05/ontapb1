@extends('layouts.app')

@section('title', 'Writing practice')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
        <div><p class="text-primary fw-semibold mb-1">Writing practice</p><h1 class="h2 mb-1">Build a clear response</h1><p class="text-body-secondary mb-0">Save drafts and review your own checklist. This is practice guidance, not an official VSTEP rating.</p></div>
    </div>
    <form class="card card-body mb-4" method="GET" action="{{ route('writing.index') }}"><div class="row g-2 align-items-end"><div class="col-12 col-md-7"><label class="form-label" for="search">Search prompts</label><input class="form-control" id="search" name="search" value="{{ request('search') }}"></div><div class="col-8 col-md-3"><label class="form-label" for="task_type">Task</label><select class="form-select" id="task_type" name="task_type"><option value="">All tasks</option>@foreach ($taskTypes as $taskType)<option value="{{ $taskType }}" @selected(request('task_type') === $taskType)>{{ str_replace('_', ' ', ucfirst($taskType)) }}</option>@endforeach</select></div><div class="col-4 col-md-2"><button class="btn btn-primary w-100" type="submit">Filter</button></div></div></form>
    <div class="row g-3">@forelse ($prompts as $prompt)<div class="col-12 col-lg-6"><a class="card h-100 text-decoration-none summary-card" href="{{ route('writing.show', $prompt) }}"><div class="card-body p-4"><div class="d-flex justify-content-between gap-3"><span class="badge text-bg-light">{{ str_replace('_', ' ', ucfirst($prompt->task_type)) }}</span><span class="small text-body-secondary">{{ $prompt->submitted_count }} review(s)</span></div><h2 class="h4 text-body mt-3">{{ $prompt->title }}</h2><p class="text-body-secondary mb-2">{{ $prompt->minimum_words ? 'At least '.$prompt->minimum_words.' words' : 'Flexible length' }} · {{ $prompt->recommended_minutes ?? 'Self-paced' }}{{ $prompt->recommended_minutes ? ' minutes' : '' }}</p><p class="text-body mb-0">{{ $prompt->instructions }}</p></div></a></div>@empty<div class="col-12"><div class="card card-body"><p class="mb-0 text-body-secondary">No active writing prompts are available.</p></div></div>@endforelse</div>
    <div class="mt-4">{{ $prompts->links() }}</div>
@endsection
