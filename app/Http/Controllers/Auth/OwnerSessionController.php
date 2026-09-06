<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerSessionController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->session()->get((string) config('owner.session_key')) === true) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:4096'],
        ]);

        $configuredLogin = (string) config('owner.login');
        $configuredPasswordHash = (string) config('owner.password_hash');
        $passwordHash = $configuredPasswordHash !== ''
            ? $configuredPasswordHash
            : (string) config('owner.dummy_password_hash');

        $loginMatches = $configuredLogin !== ''
            && hash_equals($configuredLogin, $credentials['login']);
        $passwordMatches = password_verify($credentials['password'], $passwordHash);

        if (! $loginMatches || ! $passwordMatches || $configuredPasswordHash === '') {
            return back()
                ->withErrors(['login' => 'The provided credentials are incorrect.'])
                ->onlyInput('login');
        }

        $request->session()->regenerate();
        $request->session()->put((string) config('owner.session_key'), true);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
