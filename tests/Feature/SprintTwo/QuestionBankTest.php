<?php

namespace Tests\Feature\SprintTwo;

use App\Models\Passage;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionBankTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_valid_active_reading_question_and_sync_options(): void
    {
        $topic = Topic::factory()->active()->create(['area' => 'reading']);
        $passage = Passage::query()->create($this->passage($topic, ['status' => 'active']));

        $response = $this->asOwner()->post(route('manage.questions.store'), $this->questionPayload($topic, $passage));

        $question = Question::query()->sole();
        $response->assertRedirectToRoute('manage.questions.edit', $question);
        $this->assertCount(3, $question->options);
        $this->assertSame('B', $question->options->firstWhere('is_correct', true)->option_key);
    }

    public function test_active_question_rejects_wrong_context_and_invalid_option_count(): void
    {
        $topic = Topic::factory()->active()->create(['area' => 'reading']);
        $passage = Passage::query()->create($this->passage($topic, ['status' => 'active']));
        $payload = $this->questionPayload($topic, $passage, [
            'listening_content_id' => $passage->id,
            'options_text' => 'A | Only option | correct',
        ]);

        $response = $this->asOwner()->post(route('manage.questions.store'), $payload);

        $response->assertSessionHasErrors(['passage_id', 'listening_content_id', 'options']);
        $this->assertDatabaseCount('questions', 0);
    }

    public function test_active_question_requires_explanation_and_source_provenance(): void
    {
        $topic = Topic::factory()->active()->create(['area' => 'reading']);
        $passage = Passage::query()->create($this->passage($topic, ['status' => 'active']));
        $response = $this->asOwner()->post(route('manage.questions.store'), $this->questionPayload($topic, $passage, [
            'explanation' => '',
            'source_notes' => '',
        ]));

        $response->assertSessionHasErrors(['explanation', 'source_notes']);
    }

    public function test_draft_question_is_hidden_from_learner_and_can_be_deactivated(): void
    {
        $topic = Topic::factory()->active()->create(['area' => 'reading']);
        $passage = Passage::query()->create($this->passage($topic, ['status' => 'active']));
        $question = Question::query()->create($this->questionPayload($topic, $passage, [
            'status' => 'draft',
            'explanation' => null,
        ], false));
        QuestionOption::query()->create(['question_id' => $question->id, 'option_key' => 'A', 'content' => 'A', 'position' => 0]);

        $this->asOwner()->get(route('reading.show', $passage))->assertDontSee('What is this?');
        $this->asOwner()->patch(route('manage.questions.status.update', $question), ['status' => 'inactive'])
            ->assertRedirect();
        $this->assertDatabaseHas('questions', ['id' => $question->id, 'status' => 'inactive']);
    }

    private function passage(Topic $topic, array $overrides = []): array
    {
        return array_merge([
            'topic_id' => $topic->id,
            'title' => 'Test passage',
            'body' => 'A short synthetic reading passage for question tests.',
            'word_count' => 9,
            'cefr_level' => 'B1',
            'difficulty' => 1,
            'source_type' => 'original',
            'source_notes' => 'Test fixture.',
            'status' => 'draft',
        ], $overrides);
    }

    private function questionPayload(Topic $topic, Passage $passage, array $overrides = [], bool $active = true): array
    {
        return array_merge([
            'topic_id' => $topic->id,
            'passage_id' => $passage->id,
            'skill' => 'reading',
            'type' => 'single_choice',
            'prompt' => 'What is this?',
            'explanation' => $active ? 'The passage provides the answer.' : null,
            'cefr_level' => 'B1',
            'difficulty' => 1,
            'source_type' => 'original',
            'source_notes' => 'Test fixture.',
            'status' => $active ? 'active' : 'draft',
            'options_text' => "A | First option |\nB | Correct option | correct\nC | Third option |",
        ], $overrides);
    }
}
