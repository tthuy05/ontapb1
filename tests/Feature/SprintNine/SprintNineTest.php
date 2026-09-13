<?php

namespace Tests\Feature\SprintNine;

use App\Models\Vocabulary;
use App\Models\VocabularyProgress;
use Database\Seeders\SprintFiveContentSeeder;
use Database\Seeders\SprintNineContentSeeder;
use Database\Seeders\SprintOneContentSeeder;
use Database\Seeders\SprintSevenContentSeeder;
use Database\Seeders\SprintSixContentSeeder;
use Database\Seeders\SprintThreeContentSeeder;
use Database\Seeders\SprintTwoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SprintNineTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_batch_adds_reviewed_content_without_touching_existing_progress(): void
    {
        $this->seed(SprintOneContentSeeder::class);
        $this->seed(SprintTwoContentSeeder::class);
        $this->seed(SprintThreeContentSeeder::class);
        $this->seed(SprintFiveContentSeeder::class);
        $this->seed(SprintSixContentSeeder::class);
        $this->seed(SprintSevenContentSeeder::class);

        $existing = Vocabulary::query()->where('term', 'keep track of')->firstOrFail();
        $progress = VocabularyProgress::query()->create([
            'vocabulary_id' => $existing->id,
            'state' => 'learning',
            'correct_count' => 4,
            'incorrect_count' => 1,
            'last_reviewed_at' => '2026-09-11 10:00:00',
        ]);
        $before = [
            'vocabulary_id' => $progress->vocabulary_id,
            'state' => $progress->state,
            'correct_count' => $progress->correct_count,
            'incorrect_count' => $progress->incorrect_count,
            'last_reviewed_at' => $progress->getRawOriginal('last_reviewed_at'),
        ];
        $beforeVocabularyCount = Vocabulary::query()->count();

        $this->seed(SprintNineContentSeeder::class);

        $after = VocabularyProgress::query()->findOrFail($progress->id);
        $this->assertSame($before, [
            'vocabulary_id' => $after->vocabulary_id,
            'state' => $after->state,
            'correct_count' => $after->correct_count,
            'incorrect_count' => $after->incorrect_count,
            'last_reviewed_at' => $after->getRawOriginal('last_reviewed_at'),
        ]);
        $this->assertSame($beforeVocabularyCount + 32, Vocabulary::query()->count());
        $this->assertDatabaseHas('topics', ['slug' => 'work-and-careers', 'status' => 'active']);
        $this->assertDatabaseHas('grammar_lessons', ['slug' => 'past-simple-and-present-perfect', 'status' => 'active']);
        $this->assertDatabaseHas('passages', ['title' => 'Repair before replacement', 'status' => 'active']);
        $this->assertDatabaseHas('listening_contents', ['title' => 'A change to the library workshop', 'status' => 'draft']);
        $this->assertDatabaseHas('writing_prompts', ['title' => 'Are digital reminders helpful for learners?', 'status' => 'active']);
        $this->assertDatabaseHas('speaking_prompts', ['title' => 'Choose a low-waste change', 'status' => 'active']);
    }

    public function test_content_batch_is_safe_to_apply_without_schema_or_destructive_statements(): void
    {
        $sql = file_get_contents(base_path('database/infinityfree/sprint-9-update.sql'));

        $this->assertIsString($sql);
        $this->assertDoesNotMatchRegularExpression('/\b(?:drop|truncate|alter|delete\s+from|create\s+table)\b/i', $sql);
        $this->assertStringNotContainsString('migrations', strtolower($sql));
        $this->assertStringContainsString('START TRANSACTION', $sql);
        $this->assertStringContainsString('COMMIT', $sql);
    }

    public function test_content_report_exposes_balance_and_pending_audio_budget(): void
    {
        $this->seed();

        $exitCode = Artisan::call('content:report', ['--json' => true]);
        $this->assertSame(0, $exitCode);
        $report = json_decode(trim(Artisan::output()), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(35, array_sum($report['active_vocabulary_by_topic']));
        $this->assertSame(4, $report['active_vocabulary_by_topic']['work-and-careers']);
        $this->assertSame(9, $report['active_questions_by_skill']['reading']);
        $this->assertSame(2, $report['audio_budget']['pending_recordings']);
        $this->assertSame(3, $report['writing_by_task_and_status']['task_1:active']);
        $this->assertSame(3, $report['speaking_by_part_and_status']['social_interaction:active']);
    }
}
