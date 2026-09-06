<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use RuntimeException;
use Tests\TestCase;

class HealthControllerTest extends TestCase
{
    public function test_healthy_database_returns_minimal_ok_response(): void
    {
        $response = $this->getJson(route('health'));

        $response->assertOk()
            ->assertExactJson(['status' => 'ok'])
            ->assertCookieMissing('XSRF-TOKEN')
            ->assertCookieMissing((string) config('session.cookie'));
    }

    public function test_database_failure_returns_503_without_sensitive_details(): void
    {
        Exceptions::fake();
        DB::shouldReceive('selectOne')
            ->once()
            ->with('SELECT 1')
            ->andThrow(new RuntimeException('database-password=super-secret'));

        $response = $this->getJson(route('health'));

        $response->assertServiceUnavailable()
            ->assertExactJson(['status' => 'unavailable'])
            ->assertDontSee('database-password')
            ->assertDontSee('super-secret');
        Exceptions::assertReported(RuntimeException::class);
    }
}
