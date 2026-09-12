@extends('layouts.app')

@section('title', 'Manage · B1 English Self-Study')

@section('content')
    <div class="mb-4">
        <p class="text-primary fw-semibold mb-1">Single-owner workspace</p>
        <h1 class="h2 mb-1">Manage study content</h1>
        <p class="text-body-secondary mb-0">Create, review, activate, and deactivate your personal reference content.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body"><span class="text-body-secondary d-block">Topics</span><strong class="display-6">{{ $counts['topics'] }}</strong></div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body"><span class="text-body-secondary d-block">Vocabulary</span><strong class="display-6">{{ $counts['vocabulary'] }}</strong></div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body"><span class="text-body-secondary d-block">Grammar</span><strong class="display-6">{{ $counts['grammar'] }}</strong></div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body"><span class="text-body-secondary d-block">Draft items</span><strong class="display-6">{{ $counts['drafts'] }}</strong></div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-4">
            <a class="manage-card card h-100 text-decoration-none" href="{{ route('manage.topics.index') }}">
                <div class="card-body p-4"><h2 class="h5 text-body">Topics</h2><p class="text-body-secondary mb-0">Organize vocabulary and grammar by syllabus area.</p></div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a class="manage-card card h-100 text-decoration-none" href="{{ route('manage.vocabulary.index') }}">
                <div class="card-body p-4"><h2 class="h5 text-body">Vocabulary</h2><p class="text-body-secondary mb-0">Curate meanings, examples, pronunciation metadata, and sources.</p></div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a class="manage-card card h-100 text-decoration-none" href="{{ route('manage.grammar.index') }}">
                <div class="card-body p-4"><h2 class="h5 text-body">Grammar</h2><p class="text-body-secondary mb-0">Build ordered reference lessons with examples and common mistakes.</p></div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a class="manage-card card h-100 text-decoration-none" href="{{ route('manage.reading.index') }}">
                <div class="card-body p-4"><h2 class="h5 text-body">Reading</h2><p class="text-body-secondary mb-0">Curate original passages and linked objective content.</p><span class="small text-body-secondary">{{ $counts['reading'] }} passages</span></div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a class="manage-card card h-100 text-decoration-none" href="{{ route('manage.listening.index') }}">
                <div class="card-body p-4"><h2 class="h5 text-body">Listening</h2><p class="text-body-secondary mb-0">Manage transcript and reviewed audio metadata.</p><span class="small text-body-secondary">{{ $counts['listening'] }} items</span></div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a class="manage-card card h-100 text-decoration-none" href="{{ route('manage.questions.index') }}">
                <div class="card-body p-4"><h2 class="h5 text-body">Questions</h2><p class="text-body-secondary mb-0">Author single-choice and true/false questions with answer keys.</p><span class="small text-body-secondary">{{ $counts['questions'] }} questions</span></div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a class="manage-card card h-100 text-decoration-none" href="{{ route('manage.exercises.index') }}">
                <div class="card-body p-4"><h2 class="h5 text-body">Exercises</h2><p class="text-body-secondary mb-0">Assemble reviewed questions into resumable, server-scored practice.</p><span class="small text-body-secondary">{{ $counts['exercises'] }} exercises</span></div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a class="manage-card card h-100 text-decoration-none" href="{{ route('manage.exams.index') }}">
                <div class="card-body p-4"><h2 class="h5 text-body">Mock exams</h2><p class="text-body-secondary mb-0">Compose ordered Reading/Listening simulations from reviewed questions.</p><span class="small text-body-secondary">{{ $counts['exams'] }} exams</span></div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a class="manage-card card h-100 text-decoration-none" href="{{ route('manage.writing.index') }}">
                <div class="card-body p-4"><h2 class="h5 text-body">Writing</h2><p class="text-body-secondary mb-0">Create original Task 1 and Task 2 prompts for practice.</p><span class="small text-body-secondary">{{ $counts['writing'] }} prompts</span></div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a class="manage-card card h-100 text-decoration-none" href="{{ route('manage.speaking.index') }}">
                <div class="card-body p-4"><h2 class="h5 text-body">Speaking</h2><p class="text-body-secondary mb-0">Create timed prompts with local recording and self-review only.</p><span class="small text-body-secondary">{{ $counts['speaking'] }} prompts</span></div>
            </a>
        </div>
    </div>
@endsection
