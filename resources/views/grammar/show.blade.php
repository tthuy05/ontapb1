@extends('layouts.app')

@section('title', $grammarLesson->title.' · Grammar')

@section('content')
    @if (! empty($managePreview))
        <div class="alert alert-info d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <span>Manage preview · current status: <strong>{{ $grammarLesson->status }}</strong></span>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('manage.grammar.edit', $grammarLesson) }}">Edit lesson</a>
        </div>
    @endif

    <nav aria-label="Breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ ! empty($managePreview) ? route('manage.grammar.index') : route('grammar.index') }}">Grammar</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $grammarLesson->title }}</li>
        </ol>
    </nav>

    <article class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <header class="mb-4">
                <p class="text-primary fw-semibold mb-1">{{ $grammarLesson->topic->name }}</p>
                <h1 class="display-6 mb-3">{{ $grammarLesson->title }}</h1>
                <div class="example-panel rounded-3 p-3">
                    <h2 class="h5">Learning objective</h2>
                    <p class="mb-0 preserve-lines">{{ $grammarLesson->objectives }}</p>
                </div>
            </header>

            @if ($grammarLesson->prerequisites)
                <section class="mb-4" aria-labelledby="prerequisites-heading">
                    <h2 class="h5" id="prerequisites-heading">Before you begin</h2>
                    <p class="preserve-lines mb-0">{{ $grammarLesson->prerequisites }}</p>
                </section>
            @endif

            <section class="lesson-copy mb-4" aria-labelledby="explanation-heading">
                <h2 class="h5" id="explanation-heading">Explanation</h2>
                <div class="preserve-lines">{{ $grammarLesson->body }}</div>
            </section>

            @if ($grammarLesson->examples)
                <section class="mb-4" aria-labelledby="examples-heading">
                    <h2 class="h5" id="examples-heading">Examples</h2>
                    <div class="vstack gap-3">
                        @foreach ($grammarLesson->examples as $example)
                            <div class="example-panel rounded-3 p-3">
                                <p class="fw-semibold mb-1">{{ $example['example'] }}</p>
                                <p class="text-body-secondary mb-0">{{ $example['explanation'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($grammarLesson->common_mistakes)
                <section class="alert alert-warning mb-0" aria-labelledby="mistakes-heading">
                    <h2 class="h5" id="mistakes-heading">Common mistakes</h2>
                    <p class="preserve-lines mb-0">{{ $grammarLesson->common_mistakes }}</p>
                </section>
            @endif
        </div>
    </article>
@endsection
