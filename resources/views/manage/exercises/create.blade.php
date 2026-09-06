@extends('layouts.app')
@section('title', 'Create exercise · Manage')
@section('content')<nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('manage.exercises.index') }}">Exercises</a></li><li class="breadcrumb-item active" aria-current="page">Create</li></ol></nav><h1 class="h2 mb-4">Create practice exercise</h1>@include('manage.exercises._form', ['exercise' => new \App\Models\Exercise()])@endsection
