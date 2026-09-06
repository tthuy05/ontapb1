@extends('layouts.app')

@section('title', 'Practice result · B1 English Self-Study')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
        <div>
            <p class="text-primary fw-semibold mb-1">Practice result · not an official VSTEP score</p>
            <h1 class="h2 mb-1">{{ $attempt->exercise?->title ?? 'Practice result' }}</h1>
            <p class="text-body-secondary mb-0">Attempt #{{ $attempt->id }} · Submitted {{ $attempt->submitted_at?->format('Y-m-d H:i') }}</p>
        </div>
        <div class="text-md-end">
            <div class="display-6 fw-semibold">{{ $attempt->score_awarded }} / {{ $attempt->max_score }}</div>
            <div class="text-body-secondary">{{ $attempt->percentage }}%</div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-body-secondary d-block">Correct</span><strong class="h3">{{ $attempt->correct_count }}</strong></div></div></div>
        <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-body-secondary d-block">Incorrect</span><strong class="h3">{{ $attempt->incorrect_count }}</strong></div></div></div>
        <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-body-secondary d-block">Unanswered</span><strong class="h3">{{ $attempt->unanswered_count }}</strong></div></div></div>
        <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-body-secondary d-block">Completed</span><strong class="h3">{{ ucfirst($attempt->completion_reason ?? 'manual') }}</strong></div></div></div>
    </div>

    <div class="vstack gap-4">
        @foreach ($items as $item)
            <article class="card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <h2 class="h5 mb-0">Question {{ $item['position'] + 1 }}</h2>
                        @if ($item['isCorrect'] === true)
                            <span class="badge text-bg-success">Correct · {{ $item['pointsAwarded'] }}/{{ $item['maxPoints'] }}</span>
                        @elseif ($item['isCorrect'] === false)
                            <span class="badge text-bg-danger">Incorrect · {{ $item['pointsAwarded'] }}/{{ $item['maxPoints'] }}</span>
                        @else
                            <span class="badge text-bg-secondary">Unanswered · 0/{{ $item['maxPoints'] }}</span>
                        @endif
                    </div>

                    @if (($item['context']['kind'] ?? null) === 'passage')
                        <div class="example-panel rounded p-3 mb-3">
                            <h3 class="h6">{{ $item['context']['title'] ?? 'Reading passage' }}</h3>
                            <p class="preserve-lines mb-0">{{ $item['context']['body'] ?? '' }}</p>
                        </div>
                    @elseif (($item['context']['kind'] ?? null) === 'listening')
                        <div class="example-panel rounded p-3 mb-3">
                            <h3 class="h6">{{ $item['context']['title'] ?? 'Listening item' }}</h3>
                            <p class="preserve-lines mb-0">{{ $item['context']['transcript'] ?? '' }}</p>
                        </div>
                    @endif

                    <p class="lead">{{ $item['prompt'] }}</p>
                    <ol class="mb-4">
                        @foreach ($item['options'] as $option)
                            <li class="{{ in_array($option['key'], array_column($item['correctOptions'], 'key'), true) ? 'text-success fw-semibold' : '' }}">
                                {{ $option['key'] }}. {{ $option['content'] }}
                                @if ($item['response'] === $option['key']) <span class="badge text-bg-light">Your answer</span> @endif
                                @if (in_array($option['key'], array_column($item['correctOptions'], 'key'), true)) <span class="small">Correct</span> @endif
                            </li>
                        @endforeach
                    </ol>
                    @if ($item['explanation'])
                        <div class="border-top pt-3"><h3 class="h6">Explanation</h3><p class="preserve-lines mb-0">{{ $item['explanation'] }}</p></div>
                    @endif
                </div>
            </article>
        @endforeach
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
        <a class="btn btn-primary" href="{{ route('practice.show', $attempt->exercise) }}">Try again</a>
        <a class="btn btn-outline-secondary" href="{{ route('practice.index') }}">Back to practice</a>
    </div>
@endsection
