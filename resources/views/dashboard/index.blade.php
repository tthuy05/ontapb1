@extends('layouts.app')

@section('title', 'Dashboard · B1 English Self-Study')

@section('content')
    <section class="hero-card rounded-4 p-4 p-md-5" aria-labelledby="dashboard-heading">
        <p class="text-primary fw-semibold mb-2">Personal study desk</p>
        <h1 class="display-6 fw-semibold mb-3" id="dashboard-heading">VSTEP Level 3 / B1 study workspace</h1>
        <p class="lead text-body-secondary mb-0">
            Curate your own content, study active vocabulary and grammar, and keep a simple vocabulary review state.
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
@endsection
