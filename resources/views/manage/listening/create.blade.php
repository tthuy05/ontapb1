@extends('layouts.app')
@section('title', 'Create Listening item · Manage')
@section('content')<nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('manage.listening.index') }}">Listening</a></li><li class="breadcrumb-item active" aria-current="page">Create</li></ol></nav><h1 class="h2 mb-4">Create Listening item</h1>@include('manage.listening._form', ['listeningContent' => new \App\Models\ListeningContent()])@endsection
