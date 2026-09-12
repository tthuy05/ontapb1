@extends('layouts.app')
@section('title', 'Edit speaking prompt')
@section('content')<nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('manage.speaking.index') }}">Manage Speaking</a></li><li class="breadcrumb-item active">Edit</li></ol></nav><h1 class="h2 mb-4">Edit speaking prompt</h1>@include('manage.speaking._form')@endsection
