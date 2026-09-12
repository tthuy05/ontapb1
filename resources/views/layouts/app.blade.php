<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Private VSTEP Level 3 / B1 English self-study workspace.">
    <title>@yield('title', 'B1 English Self-Study')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-body-tertiary">
    <header class="border-bottom bg-white">
        <nav class="navbar navbar-expand-lg container py-3" aria-label="Primary navigation">
            <a class="navbar-brand fw-semibold" href="{{ route('dashboard') }}">
                B1 English Self-Study
            </a>

            @if (session((string) config('owner.session_key')) === true)
                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#primary-navigation"
                    aria-controls="primary-navigation"
                    aria-expanded="false"
                    aria-label="Toggle navigation"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="primary-navigation">
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                        <li class="nav-item"><a class="nav-link" href="{{ route('vocabulary.index') }}">Vocabulary</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('grammar.index') }}">Grammar</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('reading.index') }}">Reading</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('listening.index') }}">Listening</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('writing.index') }}">Writing</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('speaking.index') }}">Speaking</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('practice.index') }}">Practice</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('exams.index') }}">Mock exams</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('history.index') }}">History</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('review.wrong.index') }}">Review</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('manage.dashboard') }}">Manage</a></li>
                        <li class="nav-item ms-lg-2">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="btn btn-outline-secondary btn-sm" type="submit">Log out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endif
        </nav>
    </header>

    <main class="container py-4 py-md-5">
        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert" aria-labelledby="validation-heading">
                <h2 class="h6" id="validation-heading">Please correct the highlighted fields.</h2>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
