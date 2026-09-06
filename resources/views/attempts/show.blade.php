@extends('layouts.app')

@section('title', 'Practice attempt · B1 English Self-Study')

@section('content')
    <div
        class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4"
        data-attempt-page
        data-expires-at="{{ $attempt->expires_at?->timestamp ?? '' }}"
        data-server-now="{{ $serverNow->timestamp }}"
    >
        <div>
            <p class="text-primary fw-semibold mb-1">{{ $attempt->configuration_snapshot['skill'] ?? 'Practice' }} practice</p>
            <h1 class="h2 mb-1">{{ $attempt->configuration_snapshot['title'] ?? 'Practice attempt' }}</h1>
            <p class="text-body-secondary mb-0">Attempt #{{ $attempt->id }} · Answers save as you work.</p>
        </div>
        <div class="text-md-end">
            @if ($attempt->expires_at)
                <span class="badge text-bg-warning fs-6" data-attempt-timer aria-live="polite">Loading timer…</span>
                <span class="d-block small text-body-secondary mt-1">Server deadline</span>
            @else
                <span class="badge text-bg-light fs-6">Untimed</span>
            @endif
        </div>
    </div>

    @if (!empty($attempt->configuration_snapshot['instructions']))
        <div class="alert alert-primary preserve-lines" role="note">{{ $attempt->configuration_snapshot['instructions'] }}</div>
    @endif

    <form method="POST" action="{{ route('attempts.submit', $attempt) }}" data-attempt-form>
        @csrf
        <div class="vstack gap-4">
            @foreach ($items as $item)
                <article class="card" data-answer-card>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between gap-3 mb-3">
                            <h2 class="h5 mb-0">Question {{ $item['position'] + 1 }}</h2>
                            <span class="small text-body-secondary">Choose one</span>
                        </div>

                        @if (($item['context']['kind'] ?? null) === 'passage')
                            <div class="example-panel rounded p-3 mb-3">
                                <h3 class="h6">{{ $item['context']['title'] ?? 'Reading passage' }}</h3>
                                <p class="preserve-lines mb-0">{{ $item['context']['body'] ?? '' }}</p>
                            </div>
                        @elseif (($item['context']['kind'] ?? null) === 'listening')
                            <div class="example-panel rounded p-3 mb-3">
                                <h3 class="h6">{{ $item['context']['title'] ?? 'Listening item' }}</h3>
                                @if (!empty($item['context']['audio_path']))
                                    <audio class="w-100" controls preload="metadata">
                                        <source src="{{ asset($item['context']['audio_path']) }}" type="{{ $item['context']['audio_mime'] ?? 'audio/mpeg' }}">
                                        Your browser does not support audio playback.
                                    </audio>
                                @else
                                    <p class="mb-0">Audio is not available for this item.</p>
                                @endif
                            </div>
                        @endif

                        <p class="lead mb-4">{{ $item['prompt'] }}</p>
                        <fieldset class="answer-group" data-answer-group
                            data-save-url="{{ $item['save_url'] }}"
                            data-save-version="{{ $item['save_version'] }}"
                            data-answer-id="{{ $item['id'] }}"
                        >
                            <legend class="visually-hidden">Answers for question {{ $item['position'] + 1 }}</legend>
                            <div class="vstack gap-2">
                                @foreach ($item['options'] as $option)
                                    <label class="form-check border rounded p-3">
                                        <input
                                            class="form-check-input me-2"
                                            type="radio"
                                            name="answer_{{ $item['id'] }}"
                                            value="{{ $option['key'] }}"
                                            @checked($item['response'] === $option['key'])
                                            data-answer-input
                                        >
                                        <span>{{ $option['key'] }}. {{ $option['content'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <div class="small mt-2" data-answer-status aria-live="polite">Not saved yet</div>
                        </fieldset>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="sticky-bottom bg-body-tertiary border-top mt-4 py-3" data-submit-bar>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <p class="small text-body-secondary mb-0">You can submit unanswered questions; they will be recorded as unanswered.</p>
                <button class="btn btn-primary" type="submit" data-submit-attempt>Submit attempt</button>
            </div>
        </div>
    </form>
@endsection
