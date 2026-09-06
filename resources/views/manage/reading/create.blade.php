@extends('layouts.app')
@section('title', 'Create Reading passage · Manage')
@section('content')<nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('manage.reading.index') }}">Reading</a></li><li class="breadcrumb-item active" aria-current="page">Create</li></ol></nav><h1 class="h2 mb-4">Create Reading passage</h1>@include('manage.reading._form', ['passage' => new \App\Models\Passage()])@endsection
