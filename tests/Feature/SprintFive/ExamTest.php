<?php

namespace Tests\Feature\SprintFive;

use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Topic;
use App\Models\Passage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_open_exam_catalog_or_composer(): void
    {
        $this->get(route('exams.index'))->assertRedirectToRoute('login');
        $this->get(route('manage.exams.index'))->assertRedirectToRoute('login');
        $this->get(route('manage.exams.create'))->assertRedirectToRoute('login');
    }

    public function test_owner_can_create_a_draft_with_ordered_sections_and_items(): void
    {
        $question = $this->question();

        $response = $this->asOwner()->post(route('manage.exams.store'), [
            ...$this->examPayload(),
            'status' => 'draft',
            'sections_text' => 'reading | Reading section | 120 | free_within_section',
            'items_text' => '0 | '.$question->id.' | 2.50',
        ]);

        $exam = Exam::query()->sole();
        $response->assertRedirectToRoute('manage.exams.edit', $exam);
        $this->assertDatabaseHas('exam_sections', [
            'exam_id' => $exam->id,
            'skill' => 'reading',
            'position' => 0,
        ]);
        $this->assertDatabaseHas('exam_section_items', [
            'question_id' => $question->id,
            'position' => 0,
            'points' => 2.50,
        ]);
    }

    public function test_active_exam_rejects_draft_questions(): void
    {
        $question = $this->question(['status' => 'draft']);

        $response = $this->asOwner()->post(route('manage.exams.store'), [
            ...$this->examPayload(),
            'status' => 'active',
            'sections_text' => 'reading | Reading section | 120 | free_within_section',
            'items_text' => '0 | '.$question->id.' | 1.00',
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseCount('exams', 0);
    }

    public function test_active_exam_can_start_navigate_and_submit_without_exposing_answer_keys(): void
    {
        $firstQuestion = $this->question(['prompt' => 'First mock question?']);
        $secondQuestion = $this->question(['prompt' => 'Second mock question?']);
        $exam = $this->examWithItems([$firstQuestion, $secondQuestion]);

        $this->asOwner()->get(route('exams.index'))
            ->assertOk()
            ->assertSee($exam->title);
        $this->asOwner()->get(route('exams.show', $exam))
            ->assertOk()
            ->assertSee('Start mock exam');

        $response = $this->asOwner()->post(route('exams.attempts.store', $exam));
        $attempt = Attempt::query()->sole();
        $response->assertRedirectToRoute('attempts.sections.show', [$attempt, 0]);

        $this->asOwner()->get(route('attempts.sections.show', [$attempt, 0]))
            ->assertOk()
            ->assertSee('First mock question?')
            ->assertDontSee('correct_keys');
        $this->asOwner()->get(route('attempts.sections.show', [$attempt, 1]))
            ->assertOk()
            ->assertSee('Second mock question?');

        $answers = $attempt->answers()->get();
        $this->asOwner()->putJson(route('attempts.answers.update', [$attempt, $answers[0]]), [
            'response' => 'A',
            'save_version' => 0,
        ])->assertOk();
        $this->asOwner()->post(route('attempts.submit', $attempt))
            ->assertRedirectToRoute('attempts.result', $attempt);

        $attempt->refresh();
        $this->assertSame('submitted', $attempt->status);
        $this->assertSame(1, $attempt->correct_count);
        $this->asOwner()->get(route('attempts.result', $attempt))
            ->assertOk()
            ->assertSee('Mock exam result')
            ->assertSee('Reading section 1')
            ->assertSee('First mock question?');
    }

    public function test_exam_result_keeps_the_attempt_snapshot_after_live_content_changes(): void
    {
        $question = $this->question(['prompt' => 'Snapshot question?']);
        $exam = $this->examWithItems([$question]);
        $this->asOwner()->post(route('exams.attempts.store', $exam));
        $attempt = Attempt::query()->sole();

        $exam->update(['title' => 'Changed live title']);
        $question->update(['prompt' => 'Changed live prompt']);
        $this->asOwner()->post(route('attempts.submit', $attempt));

        $this->asOwner()->get(route('attempts.result', $attempt))
            ->assertOk()
            ->assertSee('B1 mock exam')
            ->assertSee('Snapshot question?')
            ->assertDontSee('Changed live title')
            ->assertDontSee('Changed live prompt');
    }

    public function test_invalid_section_position_is_not_a_valid_exam_page(): void
    {
        $question = $this->question();
        $exam = $this->examWithItems([$question]);
        $this->asOwner()->post(route('exams.attempts.store', $exam));
        $attempt = Attempt::query()->sole();

        $this->asOwner()->get(route('attempts.sections.show', [$attempt, 9]))->assertNotFound();
    }

    private function examPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'B1 mock exam',
            'format_label' => 'vstep_simulation',
            'description' => 'Synthetic exam fixture.',
            'instructions' => 'Choose the best answer.',
            'time_limit_seconds' => 300,
        ], $overrides);
    }

    private function examWithItems(array $questions): Exam
    {
        $exam = Exam::query()->create($this->examPayload(['status' => 'active']));
        foreach ($questions as $position => $question) {
            $section = $exam->sections()->create([
                'skill' => 'reading',
                'title' => 'Reading section '.($position + 1),
                'instructions' => 'Read carefully.',
                'position' => $position,
                'time_limit_seconds' => 120,
                'navigation_mode' => 'free_within_section',
            ]);
            $section->items()->create([
                'question_id' => $question->id,
                'position' => 0,
                'points' => 1.00,
            ]);
        }

        return $exam->fresh('sections.items');
    }

    private function question(array $overrides = []): Question
    {
        $topic = Topic::factory()->active()->create(['area' => 'reading']);
        $passage = Passage::query()->create([
            'topic_id' => $topic->id,
            'title' => 'Synthetic mock passage',
            'body' => 'A short passage for configurable exam tests.',
            'word_count' => 8,
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
            'prompt' => 'Which mock answer is correct?',
            'explanation' => 'The first option is correct.',
            'cefr_level' => 'B1',
            'difficulty' => 1,
            'source_type' => 'original',
            'source_notes' => 'Synthetic fixture.',
            'status' => 'active',
        ], $overrides));
        QuestionOption::query()->insert([
            ['question_id' => $question->id, 'option_key' => 'A', 'content' => 'Correct answer', 'is_correct' => true, 'position' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['question_id' => $question->id, 'option_key' => 'B', 'content' => 'Incorrect answer', 'is_correct' => false, 'position' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        return $question->load(['topic', 'passage', 'options']);
    }
}
