<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Study workspace' }}</title>
    <style>
        :root { color-scheme: light; font-family: system-ui, sans-serif; }
        body { min-height: 100vh; margin: 0; display: grid; place-items: center; color: #172033; background: #f5f7fb; }
        main { width: min(36rem, calc(100% - 2rem)); padding: 2rem; background: #fff; border: 1px solid #d9e1ee; border-radius: .75rem; box-shadow: 0 .5rem 1.5rem rgb(23 32 51 / 8%); }
        .code { margin: 0 0 .5rem; color: #0d6efd; font-weight: 700; letter-spacing: .08em; }
        h1 { margin: 0 0 .75rem; font-size: clamp(1.5rem, 4vw, 2rem); }
        p { line-height: 1.6; }
        a { display: inline-block; margin-top: .75rem; padding: .7rem 1rem; color: #fff; background: #0d6efd; border-radius: .375rem; text-decoration: none; }
        a:focus-visible { outline: .2rem solid #0d6efd; outline-offset: .2rem; }
    </style>
</head>
<body>
    <main id="main-content" tabindex="-1">
        <p class="code">{{ $statusCode ?? 'Error' }}</p>
        <h1>{{ $title ?? 'Something went wrong' }}</h1>
        <p>{{ $message ?? 'Please try again. If the problem continues, return to the study workspace later.' }}</p>
        <a href="{{ session((string) config('owner.session_key')) === true ? url('/') : url('/login') }}">
            {{ session((string) config('owner.session_key')) === true ? 'Return to dashboard' : 'Go to sign in' }}
        </a>
    </main>
</body>
</html>
