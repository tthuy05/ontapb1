<?php

namespace Tests\Feature\SprintFour;

use App\Models\Attempt;
use App\Models\Exercise;
use App\Models\ExerciseQuestion;
use App\Models\Passage;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoryAndReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_and_review_require_owner_authentication(): void
    {
        [$exercise] = $this->activeExercise();
        $attempt = $this->submitExercise($exercise, 'B');
        $answer = $attempt->answers()->sole();
        $this->flushSession();

        $this->get(route('history.index'))->assertRedirectToRoute('login');
        $this->get(route('review.wrong.index'))->assertRedirectToRoute('login');
        $this->get(route('review.wrong.show', $answer))->assertRedirectToRoute('login');
    }

    public function test_history_filters_attempts_and_uses_the_attempt_snapshot(): void
    {
        [$exercise] = $this->activeExercise();
        $this->asOwner()->post(route('practice.attempts.store', $exercise));
        $submitted = Attempt::query()->latest('id')->firstOrFail();
        $this->asOwner()->post(route('attempts.submit', $submitted));
        $exercise->update(['title' => 'Changed live exercise title']);

        $this->asOwner()->post(route('practice.attempts.store', $exercise));

        $this->asOwner()->get(route('history.index', ['status' => 'submitted']))
            ->assertOk()
            ->assertSee('Synthetic attempt exercise')
            ->assertDontSee('Changed live exercise title')
            ->assertSee('Submitted');

        $this->asOwner()->get(route('history.index', ['status' => 'in_progress']))
            ->assertOk()
            ->assertSee('Resume')
            ->assertDontSee('Result');

        $this->asOwner()->get(route('history.index', ['search' => 'does-not-exist']))
            ->assertOk()
            ->assertSee('No attempts match these filters.');
    }

    public function test_result_shows_skill_topic_and_type_breakdowns_from_snapshots(): void
    {
        [$exercise, $topic] = $this->activeExercise();
        $attempt = $this->submitExercise($exercise, 'B');

        $this->asOwner()->get(route('attempts.result', $attempt))
            ->assertOk()
            ->assertSee('Result breakdown')
            ->assertSee('By skill')
            ->assertSee('By topic')
            ->assertSee('By type')
            ->assertSee($topic->name)
            ->assertSee('Review wrong answers')
            ->assertSee('0.00%');
    }

    public function test_wrong_answer_review_only_lists_submitted_incorrect_answers(): void
    {
        [$exercise] = $this->activeExercise();
        $attempt = $this->submitExercise($exercise, 'B');
        $answer = $attempt->answers()->sole();

        $this->asOwner()->get(route('review.wrong.index'))
            ->assertOk()
            ->assertSee('Which answer is correct?')
            ->assertSee('Review answer');

        $this->asOwner()->get(route('review.wrong.index', ['attempt' => $attempt->id, 'skill' => 'listening']))
            ->assertOk()
            ->assertSee('No wrong answers found');

        $this->asOwner()->get(route('review.wrong.show', $answer))
            ->assertOk()
            ->assertSee('Which answer is correct?')
            ->assertSee('The first option matches the passage.')
            ->assertSee('Your answer')
            ->assertSee('Correct fixture answer');

        $this->asOwner()->get(route('review.wrong.show', $answer->id + 1))->assertNotFound();
    }

    public function test_wrong_answer_detail_keeps_snapshot_after_live_question_changes(): void
    {
        [$exercise, $question] = $this->activeExercise();
        $attempt = $this->submitExercise($exercise, 'B');
        $answer = $attempt->answers()->sole();
        $question->update(['prompt' => 'Changed live prompt', 'explanation' => 'Changed live explanation']);

        $this->asOwner()->get(route('review.wrong.show', $answer))
            ->assertOk()
            ->assertSee('Which answer is correct?')
            ->assertSee('The first option matches the passage.')
            ->assertDontSee('Changed live prompt')
            ->assertDontSee('Changed live explanation');
    }

    public function test_dashboard_surfaces_recent_attempts_and_wrong_answer_count(): void
    {
        [$exercise] = $this->activeExercise();
        $this->submitExercise($exercise, 'B');

        $this->asOwner()->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Recent practice')
            ->assertSee('Wrong answers')
            ->assertSee('Synthetic attempt exercise')
            ->assertSee('Open review');
    }

    private function submitExercise(Exercise $exercise, ?string $response): Attempt
    {
        $this->asOwner()->post(route('practice.attempts.store', $exercise));
        $attempt = Attempt::query()->latest('id')->firstOrFail();
        $answer = $attempt->answers()->sole();

        if ($response !== null) {
            $this->asOwner()->putJson(route('attempts.answers.update', [$attempt, $answer]), [
                'response' => $response,
                'save_version' => 0,
            ])->assertOk();
        }

        $this->asOwner()->post(route('attempts.submit', $attempt));

        return $attempt->fresh(['answers']);
    }

    private function activeExercise(): array
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
        $exercise = Exercise::query()->create([
            'topic_id' => $topic->id,
            'title' => 'Synthetic attempt exercise',
            'skill' => 'reading',
            'instructions' => 'Choose the best answer.',
            'difficulty' => 1,
            'time_limit_seconds' => 300,
            'status' => 'active',
        ]);
        ExerciseQuestion::query()->create([
            'exercise_id' => $exercise->id,
            'question_id' => $question->id,
            'position' => 0,
            'points' => 1.00,
        ]);

        return [$exercise, $topic, $question];
    }
}
