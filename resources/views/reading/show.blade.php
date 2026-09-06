@extends('layouts.app')

@section('title', $passage->title.' · Reading')

@section('content')
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('reading.index') }}">Reading</a></li><li class="breadcrumb-item active" aria-current="page">{{ $passage->title }}</li></ol></nav>
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4"><div><p class="text-primary fw-semibold mb-1">Reading passage</p><h1 class="h2 mb-1">{{ $passage->title }}</h1>@if ($passage->topic)<p class="text-body-secondary mb-0">{{ $passage->topic->name }} · B1</p>@endif</div><span class="badge text-bg-light align-self-start">{{ $passage->word_count }} words</span></div>

    <article class="card mb-4"><div class="card-body p-4 p-lg-5"><div class="preserve-lines fs-5">{{ $passage->body }}</div></div></article>

    <section aria-labelledby="questions-heading"><div class="d-flex justify-content-between align-items-center gap-3 mb-3"><h2 class="h4 mb-0" id="questions-heading">Study questions</h2><span class="small text-body-secondary">Answer keys stay hidden until a practice attempt.</span></div>
        @forelse ($passage->questions as $question)
            <article class="card mb-3"><div class="card-body"><h3 class="h5">{{ $loop->iteration }}. {{ $question->prompt }}</h3><fieldset class="mb-0"><legend class="visually-hidden">Options for question {{ $loop->iteration }}</legend>@foreach ($question->options as $option)<div class="form-check"><input class="form-check-input" type="radio" disabled id="reading-question-{{ $question->id }}-{{ $option->option_key }}"><label class="form-check-label" for="reading-question-{{ $question->id }}-{{ $option->option_key }}">{{ $option->option_key }}. {{ $option->content }}</label></div>@endforeach</fieldset></div></article>
        @empty
            <div class="card card-body"><p class="mb-0 text-body-secondary">No active questions are linked yet.</p></div>
        @endforelse
    </section>
@endsection
