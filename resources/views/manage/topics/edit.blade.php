@extends('layouts.app')
@section('title', 'Edit '.$topic->name.' · Manage')
@section('content')
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('manage.topics.index') }}">Topics</a></li><li class="breadcrumb-item active" aria-current="page">Edit</li></ol></nav>
    <div class="d-flex justify-content-between gap-3 mb-4"><h1 class="h2 mb-0">Edit topic</h1>@include('partials.status-badge', ['status' => $topic->status])</div>
    @include('manage.topics._form')
    <div class="card border-danger mt-5"><div class="card-body"><h2 class="h5">Delete unused draft</h2><p class="text-body-secondary">Only an unreferenced draft can be deleted. Published or referenced topics must be deactivated.</p><form method="POST" action="{{ route('manage.topics.destroy', $topic) }}">@csrf @method('DELETE')<div class="form-check mb-3"><input class="form-check-input" id="confirm_delete" name="confirm_delete" type="checkbox" value="1" required><label class="form-check-label" for="confirm_delete">I understand this permanently deletes this unused draft.</label></div><button class="btn btn-outline-danger" type="submit">Delete unused draft</button></form></div></div>
@endsection
