@extends('layouts.app')
@section('title', 'Create grammar lesson · Manage')
@section('content')
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('manage.grammar.index') }}">Grammar</a></li><li class="breadcrumb-item active" aria-current="page">Create</li></ol></nav><h1 class="h2 mb-4">Create grammar lesson</h1>
    @include('manage.grammar._form', ['grammarLesson' => new \App\Models\GrammarLesson()])
@endsection
