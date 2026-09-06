<?php

namespace Tests\Feature\Http\Controllers\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class OwnerSessionControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('owner.login', 'owner');
        config()->set('owner.password_hash', Hash::make('correct-password'));
    }

    public function test_login_page_renders_for_guest(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk()
            ->assertViewIs('auth.login')
            ->assertSee('Owner login')
            ->assertSee('name="_token"', false);
    }

    public function test_required_credentials_return_validation_errors(): void
    {
        $response = $this->from(route('login'))->post(route('login.store'));

        $response->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'login' => 'The login field is required.',
                'password' => 'The password field is required.',
            ]);
    }

    public function test_incorrect_credentials_fail_with_generic_message(): void
    {
        $response = $this->from(route('login'))->post(route('login.store'), [
            'login' => 'owner',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'login' => 'The provided credentials are incorrect.',
            ])
            ->assertSessionMissing((string) config('owner.session_key'));
    }

    public function test_login_escapes_untrusted_old_input(): void
    {
        $dangerousLogin = '<script>alert("xss")</script>';
        $this->from(route('login'))->post(route('login.store'), [
            'login' => $dangerousLogin,
            'password' => 'wrong-password',
        ]);

        $response = $this->get(route('login'));

        $response->assertSee($dangerousLogin)
            ->assertDontSee($dangerousLogin, false);
    }

    public function test_correct_credentials_authenticate_owner_and_regenerate_session(): void
    {
        Session::start();
        $originalSessionId = Session::getId();

        $response = $this->post(route('login.store'), [
            'login' => 'owner',
            'password' => 'correct-password',
        ]);

        $response->assertRedirectToRoute('dashboard')
            ->assertSessionHas((string) config('owner.session_key'), true);
        $this->assertNotSame($originalSessionId, Session::getId());
    }

    public function test_authenticated_owner_is_redirected_away_from_login_page(): void
    {
        $response = $this->withSession([
            (string) config('owner.session_key') => true,
        ])->get(route('login'));

        $response->assertRedirectToRoute('dashboard');
    }

    public function test_sixth_failed_login_attempt_returns_429(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->from(route('login'))->post(route('login.store'), [
                'login' => 'owner',
                'password' => 'wrong-password',
            ])->assertRedirect(route('login'));
        }

        $response = $this->post(route('login.store'), [
            'login' => 'owner',
            'password' => 'wrong-password',
        ]);

        $response->assertTooManyRequests();
    }

    public function test_logout_invalidates_owner_session(): void
    {
        $response = $this->withSession([
            (string) config('owner.session_key') => true,
        ])->post(route('logout'));

        $response->assertRedirectToRoute('login')
            ->assertSessionMissing((string) config('owner.session_key'));
    }

    public function test_logout_without_csrf_token_returns_419(): void
    {
        $originalEnvironment = $this->app->environment();
        $this->app['env'] = 'production';

        try {
            $response = $this->withSession([
                (string) config('owner.session_key') => true,
            ])->post(route('logout'));
        } finally {
            $this->app['env'] = $originalEnvironment;
        }

        $response->assertStatus(419);
    }
}
