@extends('layouts.app')
@section('title', 'Edit '.$grammarLesson->title.' · Manage')
@section('content')
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('manage.grammar.index') }}">Grammar</a></li><li class="breadcrumb-item active" aria-current="page">Edit</li></ol></nav><div class="d-flex justify-content-between gap-3 mb-4"><h1 class="h2 mb-0">Edit grammar lesson</h1>@include('partials.status-badge', ['status' => $grammarLesson->status])</div>
    @include('manage.grammar._form')
    <div class="card border-danger mt-5"><div class="card-body"><h2 class="h5">Delete unused draft</h2><p class="text-body-secondary">Published lessons are preserved for future integrity. Deactivate them instead.</p><form method="POST" action="{{ route('manage.grammar.destroy', $grammarLesson) }}">@csrf @method('DELETE')<div class="form-check mb-3"><input class="form-check-input" id="confirm_delete" name="confirm_delete" type="checkbox" value="1" required><label class="form-check-label" for="confirm_delete">I understand this permanently deletes this unused draft.</label></div><button class="btn btn-outline-danger" type="submit">Delete unused draft</button></form></div></div>
@endsection
