@extends('layouts.app')

@section('title', 'Dashboard · B1 English Self-Study')

@section('content')
    <section class="hero-card rounded-4 p-4 p-md-5" aria-labelledby="dashboard-heading">
        <p class="text-primary fw-semibold mb-2">Personal study desk</p>
        <h1 class="display-6 fw-semibold mb-3" id="dashboard-heading">VSTEP Level 3 / B1 study workspace</h1>
        <p class="lead text-body-secondary mb-0">
            Curate your own content, study active vocabulary and grammar, and use practice feedback to guide your next review.
        </p>
    </section>

    <section class="mt-4" aria-labelledby="study-summary-heading">
        <h2 class="h4 mb-3" id="study-summary-heading">Study summary</h2>
        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <a class="summary-card card h-100 text-decoration-none" href="{{ route('vocabulary.index') }}">
                    <div class="card-body">
                        <span class="d-block text-body-secondary">Active vocabulary</span>
                        <strong class="display-6 text-body">{{ $activeVocabularyCount }}</strong>
                    </div>
                </a>
            </div>
            <div class="col-6 col-lg-3">
                <a class="summary-card card h-100 text-decoration-none" href="{{ route('grammar.index') }}">
                    <div class="card-body">
                        <span class="d-block text-body-secondary">Active grammar</span>
                        <strong class="display-6 text-body">{{ $activeGrammarCount }}</strong>
                    </div>
                </a>
            </div>
            <div class="col-6 col-lg-3">
                <a class="summary-card card h-100 text-decoration-none" href="{{ route('vocabulary.index', ['state' => 'learning']) }}">
                    <div class="card-body">
                        <span class="d-block text-body-secondary">Learning</span>
                        <strong class="display-6 text-body">{{ $progressCounts['learning'] }}</strong>
                    </div>
                </a>
            </div>
            <div class="col-6 col-lg-3">
                <a class="summary-card card h-100 text-decoration-none" href="{{ route('vocabulary.index', ['state' => 'review']) }}">
                    <div class="card-body">
                        <span class="d-block text-body-secondary">Review</span>
                        <strong class="display-6 text-body">{{ $progressCounts['review'] }}</strong>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <section class="row g-3 mt-4" aria-label="Practice overview">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                        <h2 class="h4 mb-0">Recent practice</h2>
                        <a href="{{ route('history.index') }}">View history</a>
                    </div>
                    @forelse ($recentAttempts as $recentAttempt)
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 border-top py-3">
                            <div>
                                <a class="fw-semibold" href="{{ $recentAttempt->status === 'submitted' ? route('attempts.result', $recentAttempt) : ($recentAttempt->status === 'in_progress' ? route('attempts.show', $recentAttempt) : route('history.index')) }}">
                                    {{ $recentAttempt->snapshot_title }}
                                </a>
                                <div class="small text-body-secondary">{{ $recentAttempt->started_at?->format('Y-m-d H:i') }} · {{ ucfirst(str_replace('_', ' ', $recentAttempt->status)) }}</div>
                            </div>
                            @if ($recentAttempt->status === 'submitted')
                                <span class="text-body-secondary">{{ $recentAttempt->percentage }}%</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-body-secondary mb-0">No practice attempts yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body p-4">
                    <h2 class="h4">Wrong answers</h2>
                    <p class="display-6 fw-semibold mb-2">{{ $wrongAnswerCount }}</p>
                    <p class="text-body-secondary">Snapshot-based feedback from submitted practice.</p>
                    <a class="btn btn-outline-primary" href="{{ route('review.wrong.index') }}">Open review</a>
                </div>
            </div>
        </div>
    </section>
@endsection
