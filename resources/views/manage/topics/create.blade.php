@extends('layouts.app')
@section('title', 'Create topic · Manage')
@section('content')
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('manage.topics.index') }}">Topics</a></li><li class="breadcrumb-item active" aria-current="page">Create</li></ol></nav>
    <h1 class="h2 mb-4">Create topic</h1>
    @include('manage.topics._form', ['topic' => new \App\Models\Topic()])
@endsection
