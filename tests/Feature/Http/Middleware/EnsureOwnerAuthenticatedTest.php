<?php

namespace Tests\Feature\Http\Middleware;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureOwnerAuthenticatedTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_with_intended_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirectToRoute('login')
            ->assertSessionHas('url.intended', route('dashboard'));
    }

    public function test_authenticated_owner_can_open_dashboard(): void
    {
        $response = $this->withSession([
            (string) config('owner.session_key') => true,
        ])->get(route('dashboard'));

        $response->assertOk()
            ->assertViewIs('dashboard.index')
            ->assertSee('VSTEP Level 3 / B1 study workspace');
    }
}
