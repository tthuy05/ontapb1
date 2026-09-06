@extends('layouts.app')

@section('title', $vocabulary->term.' · Vocabulary')

@section('content')
    @if (! empty($managePreview))
        <div class="alert alert-info d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <span>Manage preview · current status: <strong>{{ $vocabulary->status }}</strong></span>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('manage.vocabulary.edit', $vocabulary) }}">Edit entry</a>
        </div>
    @endif

    <nav aria-label="Breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ ! empty($managePreview) ? route('manage.vocabulary.index') : route('vocabulary.index') }}">Vocabulary</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $vocabulary->term }}</li>
        </ol>
    </nav>

    <article class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
                <div>
                    <p class="text-primary fw-semibold mb-1">{{ $vocabulary->topic->name }}</p>
                    <h1 class="display-6 mb-2">{{ $vocabulary->term }}</h1>
                    <p class="text-body-secondary mb-0">
                        {{ $vocabulary->part_of_speech ?: 'Part of speech not specified' }}
                        @if ($vocabulary->phonetic) · {{ $vocabulary->phonetic }} @endif
                    </p>
                </div>
                @if (empty($managePreview))
                    <form method="POST" action="{{ route('vocabulary.progress.update', $vocabulary) }}">
                        @csrf
                        @method('PATCH')
                        <label class="form-label" for="state">Review state</label>
                        <div class="input-group">
                            <select class="form-select" id="state" name="state">
                                @foreach (['new', 'learning', 'learned', 'review'] as $state)
                                    <option value="{{ $state }}" @selected(($vocabulary->progress?->state ?? 'new') === $state)>{{ ucfirst($state) }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-primary" type="submit">Save</button>
                        </div>
                    </form>
                @endif
            </div>

            <section class="mb-4" aria-labelledby="definition-heading">
                <h2 class="h5" id="definition-heading">Meaning</h2>
                <p class="mb-2">{{ $vocabulary->definition }}</p>
                @if ($vocabulary->translation)
                    <p class="text-body-secondary mb-0"><span class="fw-semibold">Vietnamese:</span> {{ $vocabulary->translation }}</p>
                @endif
            </section>

            @if ($vocabulary->example_sentence)
                <section class="example-panel rounded-3 p-3 mb-4" aria-labelledby="example-heading">
                    <h2 class="h5" id="example-heading">Example</h2>
                    <p class="mb-0">{{ $vocabulary->example_sentence }}</p>
                </section>
            @endif

            @if ($vocabulary->pronunciation_audio_path)
                <section class="mb-4" aria-labelledby="pronunciation-heading">
                    <h2 class="h5" id="pronunciation-heading">Pronunciation</h2>
                    <audio class="w-100" controls preload="none" src="{{ asset($vocabulary->pronunciation_audio_path) }}">
                        Your browser does not support audio playback.
                    </audio>
                </section>
            @endif

            @if ($vocabulary->notes)
                <section aria-labelledby="notes-heading">
                    <h2 class="h5" id="notes-heading">Notes</h2>
                    <p class="mb-0 preserve-lines">{{ $vocabulary->notes }}</p>
                </section>
            @endif
        </div>
    </article>
@endsection
