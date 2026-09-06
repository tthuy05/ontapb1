<?php

namespace Tests\Feature\SprintThree;

use App\Models\Exercise;
use App\Models\ExerciseQuestion;
use App\Models\Passage;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExerciseTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_practice_or_manage_exercises(): void
    {
        $exercise = Exercise::query()->create($this->exerciseAttributes());

        $this->get(route('practice.index'))->assertRedirectToRoute('login');
        $this->get(route('practice.show', $exercise))->assertRedirectToRoute('login');
        $this->get(route('manage.exercises.index'))->assertRedirectToRoute('login');
        $this->get(route('manage.exercises.create'))->assertRedirectToRoute('login');
    }

    public function test_owner_can_create_a_draft_exercise_with_ordered_question_assignments(): void
    {
        $question = $this->question();

        $response = $this->asOwner()->post(route('manage.exercises.store'), [
            ...$this->exerciseAttributes(),
            'status' => 'draft',
            'items_text' => $question->id.' | 2.50',
        ]);

        $exercise = Exercise::query()->sole();
        $response->assertRedirectToRoute('manage.exercises.edit', $exercise);
        $this->assertDatabaseHas('exercise_questions', [
            'exercise_id' => $exercise->id,
            'question_id' => $question->id,
            'position' => 0,
            'points' => 2.50,
        ]);
    }

    public function test_active_exercise_rejects_inactive_question(): void
    {
        $question = $this->question(['status' => 'draft']);

        $response = $this->asOwner()->post(route('manage.exercises.store'), [
            ...$this->exerciseAttributes(),
            'status' => 'active',
            'items_text' => $question->id.' | 1.00',
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseCount('exercises', 0);
    }

    public function test_active_exercise_appears_in_practice_catalog_and_can_start(): void
    {
        $question = $this->question();
        $exercise = Exercise::query()->create($this->exerciseAttributes(['status' => 'active']));
        ExerciseQuestion::query()->create([
            'exercise_id' => $exercise->id,
            'question_id' => $question->id,
            'position' => 0,
            'points' => 1.00,
        ]);

        $this->asOwner()->get(route('practice.index'))
            ->assertOk()
            ->assertSee($exercise->title);
        $this->asOwner()->get(route('practice.show', $exercise))
            ->assertOk()
            ->assertSee('Start new attempt');
    }

    private function exerciseAttributes(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Synthetic practice exercise',
            'skill' => 'reading',
            'instructions' => 'Choose the best answer.',
            'difficulty' => 1,
            'time_limit_seconds' => 300,
            'status' => 'draft',
        ], $overrides);
    }

    private function question(array $overrides = []): Question
    {
        $topic = Topic::factory()->active()->create(['area' => 'reading']);
        $passage = Passage::query()->create([
            'topic_id' => $topic->id,
            'title' => 'Synthetic exercise passage',
            'body' => 'A short passage for exercise tests.',
            'word_count' => 7,
            'cefr_level' => 'B1',
            'difficulty' => 1,
            'source_type' => 'original',
            'source_notes' => 'Synthetic fixture.',
            'status' => 'active',
        ]);
        $question = Question::query()->create(array_merge([
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
        ], $overrides));
        QuestionOption::query()->insert([
            ['question_id' => $question->id, 'option_key' => 'A', 'content' => 'Correct fixture answer', 'is_correct' => true, 'position' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $question->id, 'option_key' => 'B', 'content' => 'Incorrect fixture answer', 'is_correct' => false, 'position' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        return $question->load(['topic', 'passage', 'options']);
    }
}
