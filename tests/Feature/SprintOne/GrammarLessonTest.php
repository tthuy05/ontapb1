<?php

namespace Tests\Feature\SprintOne;

use App\Models\GrammarLesson;
use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrammarLessonTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_grammar_lesson_with_validated_examples(): void
    {
        $topic = Topic::factory()->grammar()->active()->create();

        $response = $this->asOwner()->post(route('manage.grammar.store'), $this->payload($topic));

        $lesson = GrammarLesson::query()->sole();
        $response->assertRedirectToRoute('manage.grammar.edit', $lesson);
        $this->assertSame($topic->id, $lesson->topic_id);
        $this->assertSame([
            ['example' => 'I study every day.', 'explanation' => 'This describes a routine.'],
            ['example' => 'I am studying now.', 'explanation' => 'This is happening now.'],
        ], $lesson->examples);
    }

    public function test_grammar_validation_rejects_required_invalid_and_malformed_example_values(): void
    {
        $vocabularyTopic = Topic::factory()->create(['area' => 'vocabulary']);

        $response = $this->asOwner()->post(route('manage.grammar.store'), $this->payload($vocabularyTopic, [
            'title' => '',
            'slug' => 'not valid!',
            'objectives' => '',
            'body' => '',
            'examples_text' => 'Example without an explanation',
            'source_type' => 'unknown',
            'status' => 'published',
        ]));

        $response->assertSessionHasErrors([
            'topic_id',
            'title',
            'slug',
            'objectives',
            'body',
            'examples.0.explanation',
            'source_type',
            'status',
        ]);
        $this->assertDatabaseCount('grammar_lessons', 0);
    }

    public function test_duplicate_slug_is_rejected_but_owner_can_update_existing_lesson(): void
    {
        $topic = Topic::factory()->grammar()->create();
        $lesson = GrammarLesson::factory()->for($topic)->create(['slug' => 'present-simple']);

        $this->asOwner()->post(route('manage.grammar.store'), $this->payload($topic, ['slug' => 'present-simple']))
            ->assertSessionHasErrors('slug');

        $this->asOwner()->put(route('manage.grammar.update', $lesson), $this->payload($topic, [
            'slug' => 'present-simple',
            'title' => 'Present simple for routines',
        ]))->assertRedirectToRoute('manage.grammar.edit', $lesson);

        $this->assertDatabaseHas('grammar_lessons', [
            'id' => $lesson->id,
            'title' => 'Present simple for routines',
        ]);
    }

    public function test_learner_list_and_detail_show_only_active_lessons_under_active_topics(): void
    {
        $topic = Topic::factory()->grammar()->active()->create(['name' => 'Core tenses']);
        $inactiveTopic = Topic::factory()->grammar()->create(['status' => 'inactive']);
        $active = GrammarLesson::factory()->for($topic)->active()->create(['title' => 'Present simple']);
        $draft = GrammarLesson::factory()->for($topic)->create(['title' => 'Draft lesson']);
        $hiddenByTopic = GrammarLesson::factory()->for($inactiveTopic)->active()->create(['title' => 'Hidden lesson']);

        $this->asOwner()->get(route('grammar.index'))
            ->assertOk()
            ->assertSee('Present simple')
            ->assertDontSee('Draft lesson')
            ->assertDontSee('Hidden lesson');

        $this->asOwner()->get(route('grammar.show', $active))
            ->assertOk()
            ->assertSee('Present simple');
        $this->asOwner()->get(route('grammar.show', $draft))->assertNotFound();
        $this->asOwner()->get(route('grammar.show', $hiddenByTopic))->assertNotFound();
    }

    public function test_learner_grammar_detail_escapes_lesson_and_example_content(): void
    {
        $topic = Topic::factory()->grammar()->active()->create();
        $dangerous = '<script>alert("grammar")</script>';
        $lesson = GrammarLesson::factory()->for($topic)->active()->create([
            'body' => $dangerous,
            'examples' => [['example' => $dangerous, 'explanation' => 'Synthetic warning.']],
        ]);

        $this->asOwner()->get(route('grammar.show', $lesson))
            ->assertOk()
            ->assertSee($dangerous)
            ->assertDontSee($dangerous, false);
    }

    public function test_owner_can_deactivate_and_reactivate_a_grammar_lesson(): void
    {
        $topic = Topic::factory()->grammar()->create();
        $lesson = GrammarLesson::factory()->for($topic)->active()->create();

        $this->asOwner()->patch(route('manage.grammar.status.update', $lesson), ['status' => 'inactive']);
        $this->assertDatabaseHas('grammar_lessons', ['id' => $lesson->id, 'status' => 'inactive']);

        $this->asOwner()->patch(route('manage.grammar.status.update', $lesson), ['status' => 'active']);
        $this->assertDatabaseHas('grammar_lessons', ['id' => $lesson->id, 'status' => 'active']);
    }

    private function payload(Topic $topic, array $overrides = []): array
    {
        return array_merge([
            'topic_id' => $topic->id,
            'title' => 'Present simple and present continuous',
            'slug' => 'present-simple-and-present-continuous',
            'objectives' => 'Choose the right present tense for a routine or action happening now.',
            'prerequisites' => 'Basic subject–verb agreement.',
            'body' => 'Use the present simple for routines. Use the present continuous for actions happening now.',
            'examples_text' => "I study every day. | This describes a routine.\nI am studying now. | This is happening now.",
            'common_mistakes' => 'Do not use the continuous form with many state verbs.',
            'position' => 10,
            'source_type' => 'original',
            'source_reference' => null,
            'license_name' => null,
            'license_url' => null,
            'source_notes' => 'Original test fixture.',
            'status' => 'draft',
        ], $overrides);
    }
}
