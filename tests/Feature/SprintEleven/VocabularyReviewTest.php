<?php

namespace Tests\Feature\SprintEleven;

use App\Models\Topic;
use App\Models\Vocabulary;
use App\Models\VocabularyProgress;
use App\Models\VocabularyReviewSchedule;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VocabularyReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_schedule_schema_is_forward_only_and_one_to_one_with_progress(): void
    {
        $this->assertTrue(Schema::hasColumns('vocabulary_review_schedules', [
            'id',
            'vocabulary_progress_id',
            'due_at',
            'interval_days',
            'streak',
            'lapses',
            'last_rating',
            'created_at',
            'updated_at',
        ]));

        $indexes = collect(Schema::getIndexes('vocabulary_review_schedules'))->pluck('name')->all();
        $this->assertContains('vocabulary_review_schedules_vocabulary_progress_id_unique', $indexes);
        $this->assertContains('vocabulary_review_schedules_due_at_index', $indexes);

        $progress = $this->progressFor($this->activeVocabulary('schema-card'));
        VocabularyReviewSchedule::query()->create([
            'vocabulary_progress_id' => $progress->id,
            'due_at' => now(),
        ]);

        $this->expectException(QueryException::class);
        VocabularyReviewSchedule::query()->create([
            'vocabulary_progress_id' => $progress->id,
            'due_at' => now()->addDay(),
        ]);
    }

    public function test_guest_cannot_open_or_submit_the_review_queue(): void
    {
        $vocabulary = $this->activeVocabulary('protected-card');

        $this->get(route('vocabulary.review.index'))->assertRedirectToRoute('login');
        $this->post(route('vocabulary.review.store', $vocabulary), ['rating' => 'good'])
            ->assertRedirectToRoute('login');
    }

    public function test_deleting_progress_cascades_its_schedule_without_deleting_vocabulary(): void
    {
        $vocabulary = $this->activeVocabulary('cascade-card');
        $this->scheduleFor($vocabulary, now());

        $vocabulary->progress()->delete();

        $this->assertDatabaseCount('vocabulary_review_schedules', 0);
        $this->assertDatabaseHas('vocabularies', ['id' => $vocabulary->id]);
    }

    public function test_queue_prioritizes_due_and_legacy_cards_then_limits_new_cards(): void
    {
        Carbon::setTestNow('2026-09-13 09:00:00');

        $due = $this->activeVocabulary('due-card');
        $this->scheduleFor($due, now()->subMinute());

        $future = $this->activeVocabulary('future-card');
        $this->scheduleFor($future, now()->addDay());

        $legacy = $this->activeVocabulary('legacy-card');
        $this->progressFor($legacy, 'review');

        foreach (range(1, 6) as $number) {
            $this->activeVocabulary('new-card-'.$number);
        }

        $inactive = $this->activeVocabulary('inactive-card');
        $inactive->update(['status' => 'inactive']);
        $this->scheduleFor($inactive, now()->subHour());

        $inactiveTopic = Topic::factory()->create(['status' => 'inactive']);
        $hiddenByTopic = Vocabulary::factory()->for($inactiveTopic)->active()->create(['term' => 'inactive-topic-card']);
        $this->scheduleFor($hiddenByTopic, now()->subHour());

        $response = $this->asOwner()->get(route('vocabulary.review.index'));

        $response->assertOk()
            ->assertSee('2 due now')
            ->assertSee('6 new available')
            ->assertSeeInOrder(['due-card', 'legacy-card', 'new-card-1'])
            ->assertSee('new-card-5')
            ->assertDontSee('new-card-6')
            ->assertDontSee('future-card')
            ->assertDontSee('inactive-card')
            ->assertDontSee('inactive-topic-card');
    }

    public function test_good_rating_creates_progress_and_a_one_day_schedule(): void
    {
        Carbon::setTestNow('2026-09-13 10:00:00');
        $vocabulary = $this->activeVocabulary('good-card');

        $this->asOwner()->post(route('vocabulary.review.store', $vocabulary), ['rating' => 'good'])
            ->assertRedirectToRoute('vocabulary.review.index')
            ->assertSessionHas('status');

        $progress = VocabularyProgress::query()->sole();
        $schedule = VocabularyReviewSchedule::query()->sole();

        $this->assertSame('learning', $progress->state);
        $this->assertSame(1, $progress->correct_count);
        $this->assertSame(0, $progress->incorrect_count);
        $this->assertTrue($progress->last_reviewed_at->equalTo(now()));
        $this->assertSame(1, $schedule->interval_days);
        $this->assertSame(1, $schedule->streak);
        $this->assertSame(0, $schedule->lapses);
        $this->assertSame('good', $schedule->last_rating);
        $this->assertTrue($schedule->due_at->equalTo(now()->addDay()));
    }

    public function test_easy_rating_updates_the_existing_rows_and_expands_the_interval(): void
    {
        Carbon::setTestNow('2026-09-13 10:00:00');
        $vocabulary = $this->activeVocabulary('easy-card');
        $this->asOwner()->post(route('vocabulary.review.store', $vocabulary), ['rating' => 'good']);

        Carbon::setTestNow('2026-09-14 10:00:00');
        $this->asOwner()->post(route('vocabulary.review.store', $vocabulary), ['rating' => 'easy'])
            ->assertRedirectToRoute('vocabulary.review.index');

        $this->assertDatabaseCount('vocabulary_progress', 1);
        $this->assertDatabaseCount('vocabulary_review_schedules', 1);

        $progress = VocabularyProgress::query()->sole();
        $schedule = VocabularyReviewSchedule::query()->sole();
        $this->assertSame(2, $progress->correct_count);
        $this->assertSame(3, $schedule->interval_days);
        $this->assertSame(2, $schedule->streak);
        $this->assertSame('easy', $schedule->last_rating);
        $this->assertTrue($schedule->due_at->equalTo(now()->addDays(3)));
    }

    public function test_again_rating_resets_the_streak_and_schedules_ten_minutes(): void
    {
        Carbon::setTestNow('2026-09-13 10:00:00');
        $vocabulary = $this->activeVocabulary('again-card');
        $progress = $this->progressFor($vocabulary, 'review', 4, 1);
        VocabularyReviewSchedule::query()->create([
            'vocabulary_progress_id' => $progress->id,
            'due_at' => now()->subMinute(),
            'interval_days' => 12,
            'streak' => 4,
            'lapses' => 1,
            'last_rating' => 'good',
        ]);

        $this->asOwner()->post(route('vocabulary.review.store', $vocabulary), ['rating' => 'again']);

        $progress->refresh();
        $schedule = $progress->reviewSchedule;
        $this->assertSame('learning', $progress->state);
        $this->assertSame(4, $progress->correct_count);
        $this->assertSame(2, $progress->incorrect_count);
        $this->assertSame(0, $schedule->interval_days);
        $this->assertSame(0, $schedule->streak);
        $this->assertSame(2, $schedule->lapses);
        $this->assertSame('again', $schedule->last_rating);
        $this->assertTrue($schedule->due_at->equalTo(now()->addMinutes(10)));
    }

    public function test_hard_and_easy_ratings_move_a_long_interval_from_review_to_learned(): void
    {
        Carbon::setTestNow('2026-09-13 10:00:00');
        $vocabulary = $this->activeVocabulary('long-interval-card');
        $progress = $this->progressFor($vocabulary, 'review');
        VocabularyReviewSchedule::query()->create([
            'vocabulary_progress_id' => $progress->id,
            'due_at' => now(),
            'interval_days' => 10,
            'streak' => 3,
            'last_rating' => 'good',
        ]);

        $this->asOwner()->post(route('vocabulary.review.store', $vocabulary), ['rating' => 'hard']);
        $progress->refresh();
        $this->assertSame('review', $progress->state);
        $this->assertSame(12, $progress->reviewSchedule->interval_days);
        $this->assertTrue($progress->reviewSchedule->due_at->equalTo(now()->addDays(12)));

        Carbon::setTestNow('2026-09-25 10:00:00');
        $this->asOwner()->post(route('vocabulary.review.store', $vocabulary), ['rating' => 'easy']);
        $progress->refresh();
        $this->assertSame('learned', $progress->state);
        $this->assertSame(30, $progress->reviewSchedule->interval_days);
        $this->assertSame(5, $progress->reviewSchedule->streak);
        $this->assertSame(2, $progress->correct_count);
        $this->assertTrue($progress->reviewSchedule->due_at->equalTo(now()->addDays(30)));
    }

    public function test_invalid_rating_and_inactive_content_are_rejected(): void
    {
        $active = $this->activeVocabulary('invalid-rating-card');
        $inactive = $this->activeVocabulary('inactive-rating-card');
        $inactive->update(['status' => 'inactive']);

        $this->asOwner()->post(route('vocabulary.review.store', $active), ['rating' => 'perfect'])
            ->assertSessionHasErrors('rating');
        $this->asOwner()->post(route('vocabulary.review.store', $inactive), ['rating' => 'good'])
            ->assertNotFound();

        $this->assertDatabaseCount('vocabulary_progress', 0);
        $this->assertDatabaseCount('vocabulary_review_schedules', 0);
    }

    public function test_dashboard_shows_only_due_active_reviews(): void
    {
        Carbon::setTestNow('2026-09-13 10:00:00');
        $due = $this->activeVocabulary('dashboard-due');
        $this->scheduleFor($due, now()->subMinute());
        $future = $this->activeVocabulary('dashboard-future');
        $this->scheduleFor($future, now()->addDay());
        $this->activeVocabulary('dashboard-new');

        $this->asOwner()->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Vocabulary due')
            ->assertSee('1 new available');
    }

    public function test_infinityfree_sql_is_forward_only_and_does_not_rewrite_existing_data(): void
    {
        $sql = file_get_contents(database_path('infinityfree/sprint-11-update.sql'));
        $executable = preg_replace('/^\s*--.*$/m', '', $sql);

        $this->assertIsString($sql);
        $this->assertSame(1, preg_match_all('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS/i', $executable));
        $this->assertStringContainsString('2026_09_13_002000_create_vocabulary_review_schedules_table', $executable);
        $this->assertDoesNotMatchRegularExpression('/\b(?:ALTER\s+TABLE|DROP\s+TABLE|TRUNCATE\s+TABLE|DELETE\s+FROM)\b/i', $executable);
        $this->assertDoesNotMatchRegularExpression('/\bCREATE\s+(?:DATABASE|SCHEMA)\b/i', $executable);
        $this->assertDoesNotMatchRegularExpression('/\bmigrate\s*:\s*fresh\b/i', $executable);
        $this->assertDoesNotMatchRegularExpression('/(?:INSERT\s+INTO|UPDATE)\s+`?(?:topics|vocabularies|vocabulary_progress|grammar_lessons|passages|listening_contents|questions|question_options|exercises|attempts|writing_submissions|speaking_submissions)`?/i', $executable);
    }

    private function activeVocabulary(string $term): Vocabulary
    {
        return Vocabulary::factory()
            ->for(Topic::factory()->active())
            ->active()
            ->create(['term' => $term]);
    }

    private function progressFor(
        Vocabulary $vocabulary,
        string $state = 'learning',
        int $correctCount = 0,
        int $incorrectCount = 0,
    ): VocabularyProgress {
        return VocabularyProgress::query()->create([
            'vocabulary_id' => $vocabulary->id,
            'state' => $state,
            'correct_count' => $correctCount,
            'incorrect_count' => $incorrectCount,
        ]);
    }

    private function scheduleFor(Vocabulary $vocabulary, Carbon $dueAt): VocabularyReviewSchedule
    {
        $progress = $this->progressFor($vocabulary, 'review');

        return VocabularyReviewSchedule::query()->create([
            'vocabulary_progress_id' => $progress->id,
            'due_at' => $dueAt,
            'interval_days' => 7,
            'streak' => 3,
            'last_rating' => 'good',
        ]);
    }
}
