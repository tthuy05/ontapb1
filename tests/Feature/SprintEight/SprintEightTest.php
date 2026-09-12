<?php

namespace Tests\Feature\SprintEight;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SprintEightTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_content_passes_the_content_quality_command(): void
    {
        $this->seed();

        $this->artisan('content:validate')
            ->expectsOutputToContain('Content QA:')
            ->assertExitCode(0);
    }

    public function test_content_quality_json_is_machine_readable_and_clean(): void
    {
        $this->seed();

        $exitCode = Artisan::call('content:validate', ['--json' => true]);
        $this->assertSame(0, $exitCode);
        $output = trim(Artisan::output());
        $report = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $report['issue_count']);
        $this->assertArrayHasKey('counts', $report);
        $this->assertArrayHasKey('questions', $report['counts']);
    }

    public function test_study_export_writes_allowlisted_tables_to_private_storage(): void
    {
        $this->seed();
        Storage::fake('local');

        $this->artisan('study:export', ['--path' => 'exports/sprint-eight-test.json'])
            ->expectsOutputToContain('private storage')
            ->assertExitCode(0);

        $disk = Storage::disk('local');
        $disk->assertExists('exports/sprint-eight-test.json');
        $payload = json_decode($disk->get('exports/sprint-eight-test.json'), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('b1-english-self-study-export', $payload['format']);
        $this->assertArrayHasKey('vocabulary_progress', $payload['tables']);
        $this->assertArrayNotHasKey('sessions', $payload['tables']);
        $this->assertStringNotContainsString('OWNER_PASSWORD_HASH', $disk->get('exports/sprint-eight-test.json'));
    }

    public function test_study_export_rejects_paths_outside_private_storage(): void
    {
        $this->artisan('study:export', ['--path' => '../outside.json'])
            ->expectsOutputToContain('private local disk')
            ->assertExitCode(1);

        $this->artisan('study:export', ['--path' => 'public/outside.json'])
            ->expectsOutputToContain('private local disk')
            ->assertExitCode(1);
    }

    public function test_layout_exposes_a_skip_link_and_main_landmark(): void
    {
        $this->asOwner()->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Skip to main content')
            ->assertSee('id="main-content"', false)
            ->assertSee('tabindex="-1"', false);
    }

    public function test_missing_page_uses_a_safe_error_view(): void
    {
        $this->asOwner()->get('/sprint-eight-page-that-does-not-exist')
            ->assertNotFound()
            ->assertSee('Page not found')
            ->assertDontSee('Stack trace')
            ->assertDontSee('vendor\\laravel');
    }

    public function test_state_changing_routes_have_the_study_write_limiter(): void
    {
        foreach ([
            'vocabulary.progress.update',
            'writing.submissions.store',
            'speaking.submissions.store',
            'practice.attempts.store',
            'attempts.answers.update',
            'attempts.submit',
            'exams.attempts.store',
            'manage.topics.store',
            'manage.exams.items.order.update',
        ] as $name) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, $name);
            $this->assertContains('throttle:study-write', $route->middleware(), $name);
        }
    }
}
