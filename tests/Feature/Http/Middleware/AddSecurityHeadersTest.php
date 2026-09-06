<?php

namespace Tests\Feature\Http\Middleware;

use Tests\TestCase;

class AddSecurityHeadersTest extends TestCase
{
    public function test_every_response_has_baseline_security_headers(): void
    {
        $response = $this->get(route('login'));

        $response->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), geolocation=(), microphone=(self)');
    }

    public function test_secure_production_response_has_csp_and_hsts_headers(): void
    {
        $this->app['env'] = 'production';

        $response = $this->get('https://example.test/login');

        $response->assertHeader(
            'Content-Security-Policy',
            "default-src 'self'; base-uri 'self'; connect-src 'self'; font-src 'self'; form-action 'self'; frame-ancestors 'none'; img-src 'self' data:; media-src 'self' blob:; object-src 'none'; script-src 'self'; style-src 'self'",
        )->assertHeader(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains',
        );
    }

    public function test_http_request_redirects_to_configured_https_url_when_forced(): void
    {
        config()->set('app.force_https', true);
        config()->set('app.url', 'https://study.example.test');

        $response = $this->get('http://study.example.test/login?from=bookmark');

        $response->assertRedirect('https://study.example.test/login?from=bookmark')
            ->assertStatus(301);
    }
}
