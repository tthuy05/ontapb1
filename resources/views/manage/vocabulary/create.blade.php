@extends('layouts.app')
@section('title', 'Create vocabulary · Manage')
@section('content')
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('manage.vocabulary.index') }}">Vocabulary</a></li><li class="breadcrumb-item active" aria-current="page">Create</li></ol></nav><h1 class="h2 mb-4">Create vocabulary entry</h1>
    @include('manage.vocabulary._form', ['vocabulary' => new \App\Models\Vocabulary()])
@endsection
