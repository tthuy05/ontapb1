@extends('layouts.app')
@section('title', 'Create speaking prompt')
@section('content')<nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('manage.speaking.index') }}">Manage Speaking</a></li><li class="breadcrumb-item active">Create</li></ol></nav><h1 class="h2 mb-4">Create speaking prompt</h1>@include('manage.speaking._form')@endsection
