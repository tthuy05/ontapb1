<?php

namespace Tests\Feature\SprintThree;

use App\Models\Attempt;
use App\Models\Exercise;
use App\Models\ExerciseQuestion;
use App\Models\Passage;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Topic;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttemptTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_start_is_transactional_and_snapshots_questions_before_taking(): void
    {
        [$exercise, $question] = $this->activeExercise();

        $response = $this->asOwner()->post(route('practice.attempts.store', $exercise));
        $attempt = Attempt::query()->sole();
        $answer = $attempt->answers()->sole();

        $response->assertRedirectToRoute('attempts.show', $attempt);
        $this->assertSame('Correct fixture answer', $answer->question_snapshot['options'][0]['content']);
        $this->assertSame(['A'], $answer->question_snapshot['correct_keys']);
        $this->assertSame($question->id, $answer->question_id);
        $this->assertSame(0, $answer->save_version);

        $this->asOwner()->get(route('attempts.show', $attempt))
            ->assertOk()
            ->assertDontSee('The first option matches the passage.')
            ->assertDontSee('correct_keys');
    }

    public function test_save_uses_optimistic_versioning_and_submit_is_idempotent(): void
    {
        [$exercise] = $this->activeExercise();
        $this->asOwner()->post(route('practice.attempts.store', $exercise));
        $attempt = Attempt::query()->sole();
        $answer = $attempt->answers()->sole();

        $this->asOwner()->putJson(route('attempts.answers.update', [$attempt, $answer]), [
            'response' => 'A',
            'save_version' => 0,
        ])->assertOk()->assertJsonPath('save_version', 1);

        $this->asOwner()->putJson(route('attempts.answers.update', [$attempt, $answer]), [
            'response' => 'B',
            'save_version' => 0,
        ])->assertStatus(409)->assertJsonPath('save_version', 1);

        $this->asOwner()->post(route('attempts.submit', $attempt))->assertRedirectToRoute('attempts.result', $attempt);
        $attempt->refresh();
        $this->assertSame('submitted', $attempt->status);
        $this->assertSame(1, $attempt->correct_count);

        $this->asOwner()->post(route('attempts.submit', $attempt))->assertRedirectToRoute('attempts.result', $attempt);
        $this->assertSame(1, Attempt::query()->where('status', 'submitted')->count());
    }

    public function test_result_uses_immutable_snapshot_after_source_question_changes(): void
    {
        [$exercise, $question] = $this->activeExercise();
        $this->asOwner()->post(route('practice.attempts.store', $exercise));
        $attempt = Attempt::query()->sole();
        $answer = $attempt->answers()->sole();

        $question->update(['prompt' => 'Changed after start', 'explanation' => 'Changed explanation']);
        $this->asOwner()->putJson(route('attempts.answers.update', [$attempt, $answer]), [
            'response' => 'A',
            'save_version' => 0,
        ])->assertOk();
        $this->asOwner()->post(route('attempts.submit', $attempt));

        $this->asOwner()->get(route('attempts.result', $attempt))
            ->assertOk()
            ->assertSee('Which answer is correct?')
            ->assertSee('The first option matches the passage.')
            ->assertDontSee('Changed after start')
            ->assertDontSee('Changed explanation');
    }

    public function test_answer_cannot_be_saved_after_submission(): void
    {
        [$exercise] = $this->activeExercise();
        $this->asOwner()->post(route('practice.attempts.store', $exercise));
        $attempt = Attempt::query()->sole();
        $answer = $attempt->answers()->sole();
        $this->asOwner()->post(route('attempts.submit', $attempt));

        $this->asOwner()->putJson(route('attempts.answers.update', [$attempt, $answer]), [
            'response' => 'A',
            'save_version' => 0,
        ])->assertStatus(409);
    }

    public function test_server_deadline_finalizes_the_attempt_even_if_client_did_not_submit(): void
    {
        [$exercise] = $this->activeExercise(['time_limit_seconds' => 30]);
        Carbon::setTestNow('2026-09-06 10:00:00');
        $this->asOwner()->post(route('practice.attempts.store', $exercise));
        $attempt = Attempt::query()->sole();

        Carbon::setTestNow($attempt->expires_at->copy()->addSecond());
        $this->asOwner()->get(route('attempts.show', $attempt))->assertRedirectToRoute('attempts.result', $attempt);
        $attempt->refresh();

        $this->assertSame('submitted', $attempt->status);
        $this->assertSame('deadline', $attempt->completion_reason);
    }

    public function test_start_policy_allows_a_new_attempt_without_mutating_the_previous_snapshot(): void
    {
        [$exercise] = $this->activeExercise();
        $this->asOwner()->post(route('practice.attempts.store', $exercise));
        $first = Attempt::query()->sole();
        $this->asOwner()->post(route('practice.attempts.store', $exercise));

        $this->assertCount(2, Attempt::query()->where('exercise_id', $exercise->id)->get());
        $this->assertSame('in_progress', $first->fresh()->status);
    }

    private function activeExercise(array $overrides = []): array
    {
        $topic = Topic::factory()->active()->create(['area' => 'reading']);
        $passage = Passage::query()->create([
            'topic_id' => $topic->id,
            'title' => 'Synthetic attempt passage',
            'body' => 'A short passage for attempt tests.',
            'word_count' => 7,
            'cefr_level' => 'B1',
            'difficulty' => 1,
            'source_type' => 'original',
            'source_notes' => 'Synthetic fixture.',
            'status' => 'active',
        ]);
        $question = Question::query()->create([
            'topic_id' => $topic->id,
            'passage_id' => $passage->id,
            'skill' => 'reading',
            'type' => 'single_choice',
            'prompt' => 'Which answer is correct?',
            'explanation' => 'The first option matches the passage.',
            'cefr_level' => 'B1',
            'difficulty' => 1,
            'source_type' => 'original',
            'source_notes' => 'Synthetic fixture.',
            'status' => 'active',
        ]);
        QuestionOption::query()->insert([
            ['question_id' => $question->id, 'option_key' => 'A', 'content' => 'Correct fixture answer', 'is_correct' => true, 'position' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $question->id, 'option_key' => 'B', 'content' => 'Incorrect fixture answer', 'is_correct' => false, 'position' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
        $exercise = Exercise::query()->create(array_merge([
            'topic_id' => $topic->id,
            'title' => 'Synthetic attempt exercise',
            'skill' => 'reading',
            'instructions' => 'Choose the best answer.',
            'difficulty' => 1,
            'time_limit_seconds' => 300,
            'status' => 'active',
        ], $overrides));
        ExerciseQuestion::query()->create([
            'exercise_id' => $exercise->id,
            'question_id' => $question->id,
            'position' => 0,
            'points' => 1.00,
        ]);

        return [$exercise, $question];
    }
}
