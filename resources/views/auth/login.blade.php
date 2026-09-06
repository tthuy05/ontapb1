@extends('layouts.app')

@section('title', 'Owner login · B1 English Self-Study')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-5">
            <section class="card border-0 shadow-sm" aria-labelledby="login-heading">
                <div class="card-body p-4 p-md-5">
                    <p class="text-primary fw-semibold mb-2">Private study workspace</p>
                    <h1 class="h3 mb-2" id="login-heading">Owner login</h1>
                    <p class="text-body-secondary mb-4">Sign in with the single configured owner credential.</p>

                    <form method="POST" action="{{ route('login.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="login">Login</label>
                            <input
                                class="form-control @error('login') is-invalid @enderror"
                                id="login"
                                name="login"
                                type="text"
                                value="{{ old('login') }}"
                                maxlength="100"
                                autocomplete="username"
                                required
                                autofocus
                            >
                            @error('login')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="password">Password</label>
                            <input
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                type="password"
                                maxlength="4096"
                                autocomplete="current-password"
                                required
                            >
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button class="btn btn-primary w-100" type="submit">Log in</button>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection
