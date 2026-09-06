@extends('layouts.app')
@section('title', 'Create question · Manage')
@section('content')<nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('manage.questions.index') }}">Questions</a></li><li class="breadcrumb-item active" aria-current="page">Create</li></ol></nav><h1 class="h2 mb-4">Create objective question</h1>@include('manage.questions._form', ['question' => new \App\Models\Question()])@endsection
