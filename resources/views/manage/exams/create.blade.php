@extends('layouts.app')
@section('title', 'Create mock exam · Manage')
@section('content')<nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('manage.exams.index') }}">Mock exams</a></li><li class="breadcrumb-item active" aria-current="page">Create</li></ol></nav><h1 class="h2 mb-4">Create mock exam</h1>@include('manage.exams._form', ['exam' => new \App\Models\Exam()])@endsection
