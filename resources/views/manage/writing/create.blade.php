@extends('layouts.app')
@section('title', 'Create writing prompt')
@section('content')<nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('manage.writing.index') }}">Manage Writing</a></li><li class="breadcrumb-item active">Create</li></ol></nav><h1 class="h2 mb-4">Create writing prompt</h1>@include('manage.writing._form')@endsection
