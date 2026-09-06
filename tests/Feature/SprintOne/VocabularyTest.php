<?php

namespace Tests\Feature\SprintOne;

use App\Models\Topic;
use App\Models\Vocabulary;
use App\Models\VocabularyProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VocabularyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_vocabulary_linked_to_an_approved_topic_area(): void
    {
        $topic = Topic::factory()->active()->create();

        $response = $this->asOwner()->post(route('manage.vocabulary.store'), $this->payload($topic));

        $vocabulary = Vocabulary::query()->sole();
        $response->assertRedirectToRoute('manage.vocabulary.edit', $vocabulary);
        $this->assertDatabaseHas('vocabularies', [
            'topic_id' => $topic->id,
            'term' => 'keep track of',
            'part_of_speech' => 'phrase',
            'source_type' => 'original',
        ]);
        $this->assertTrue($vocabulary->topic->is($topic));
    }

    public function test_vocabulary_validation_rejects_missing_fields_invalid_topic_and_unsafe_audio_path(): void
    {
        $grammarTopic = Topic::factory()->grammar()->create();

        $response = $this->asOwner()->post(route('manage.vocabulary.store'), $this->payload($grammarTopic, [
            'term' => '',
            'definition' => '',
            'source_type' => 'scraped',
            'status' => 'published',
            'pronunciation_audio_path' => '../public/shell.php',
        ]));

        $response->assertSessionHasErrors([
            'topic_id',
            'term',
            'definition',
            'source_type',
            'status',
            'pronunciation_audio_path',
        ]);
        $this->assertDatabaseCount('vocabularies', 0);
    }

    public function test_duplicate_term_part_of_speech_within_a_topic_is_rejected(): void
    {
        $topic = Topic::factory()->create();
        Vocabulary::factory()->for($topic)->create([
            'term' => 'deadline',
            'part_of_speech' => 'noun',
        ]);

        $this->asOwner()->post(route('manage.vocabulary.store'), $this->payload($topic, [
            'term' => 'deadline',
            'part_of_speech' => 'noun',
        ]))->assertSessionHasErrors('term');

        $this->assertDatabaseCount('vocabularies', 1);
    }

    public function test_owner_can_update_vocabulary_without_triggering_its_own_duplicate_rule(): void
    {
        $topic = Topic::factory()->create();
        $vocabulary = Vocabulary::factory()->for($topic)->create([
            'term' => 'deadline',
            'part_of_speech' => 'noun',
        ]);

        $this->asOwner()->put(route('manage.vocabulary.update', $vocabulary), $this->payload($topic, [
            'term' => 'deadline',
            'part_of_speech' => 'noun',
            'definition' => 'The final time by which work must be completed.',
        ]))->assertRedirectToRoute('manage.vocabulary.edit', $vocabulary);

        $this->assertDatabaseHas('vocabularies', [
            'id' => $vocabulary->id,
            'definition' => 'The final time by which work must be completed.',
        ]);
    }

    public function test_learner_list_shows_only_active_content_and_supports_topic_filtering(): void
    {
        $selectedTopic = Topic::factory()->active()->create(['name' => 'Education and study']);
        $otherTopic = Topic::factory()->active()->create(['name' => 'Daily life']);
        $inactiveTopic = Topic::factory()->create(['status' => 'inactive']);

        Vocabulary::factory()->for($selectedTopic)->active()->create(['term' => 'deadline']);
        Vocabulary::factory()->for($otherTopic)->active()->create(['term' => 'household task']);
        Vocabulary::factory()->for($selectedTopic)->create(['term' => 'draft word']);
        Vocabulary::factory()->for($inactiveTopic)->active()->create(['term' => 'hidden topic word']);

        $response = $this->asOwner()->get(route('vocabulary.index', ['topic' => $selectedTopic->id]));

        $response->assertOk()
            ->assertSee('deadline')
            ->assertDontSee('household task')
            ->assertDontSee('draft word')
            ->assertDontSee('hidden topic word');
    }

    public function test_learner_detail_escapes_content_and_inactive_content_returns_404(): void
    {
        $topic = Topic::factory()->active()->create();
        $dangerous = '<script>alert("word")</script>';
        $active = Vocabulary::factory()->for($topic)->active()->create(['definition' => $dangerous]);
        $inactive = Vocabulary::factory()->for($topic)->create(['status' => 'inactive']);

        $this->asOwner()->get(route('vocabulary.show', $active))
            ->assertOk()
            ->assertSee($dangerous)
            ->assertDontSee($dangerous, false);
        $this->asOwner()->get(route('vocabulary.show', $inactive))->assertNotFound();
    }

    public function test_owner_can_update_vocabulary_progress_without_a_user_id(): void
    {
        $topic = Topic::factory()->active()->create();
        $vocabulary = Vocabulary::factory()->for($topic)->active()->create();

        $this->asOwner()->patch(route('vocabulary.progress.update', $vocabulary), [
            'state' => 'learning',
        ])->assertRedirect()->assertSessionHas('status', 'Vocabulary progress updated.');

        $progress = VocabularyProgress::query()->sole();
        $this->assertSame($vocabulary->id, $progress->vocabulary_id);
        $this->assertSame('learning', $progress->state);
        $this->assertSame(0, $progress->correct_count);
        $this->assertSame(0, $progress->incorrect_count);
        $this->assertNotNull($progress->last_reviewed_at);

        $this->asOwner()->patch(route('vocabulary.progress.update', $vocabulary), [
            'state' => 'review',
        ]);
        $this->assertDatabaseCount('vocabulary_progress', 1);
        $this->assertDatabaseHas('vocabulary_progress', ['state' => 'review']);
    }

    public function test_invalid_progress_state_and_inactive_vocabulary_are_rejected(): void
    {
        $topic = Topic::factory()->active()->create();
        $active = Vocabulary::factory()->for($topic)->active()->create();
        $inactive = Vocabulary::factory()->for($topic)->create(['status' => 'inactive']);

        $this->asOwner()->patch(route('vocabulary.progress.update', $active), ['state' => 'mastered'])
            ->assertSessionHasErrors('state');
        $this->asOwner()->patch(route('vocabulary.progress.update', $inactive), ['state' => 'learning'])
            ->assertNotFound();
        $this->assertDatabaseCount('vocabulary_progress', 0);
    }

    public function test_entry_with_progress_cannot_be_deleted(): void
    {
        $topic = Topic::factory()->create();
        $vocabulary = Vocabulary::factory()->for($topic)->create(['status' => 'draft']);
        VocabularyProgress::query()->create(['vocabulary_id' => $vocabulary->id, 'state' => 'learning']);

        $this->asOwner()->delete(route('manage.vocabulary.destroy', $vocabulary), ['confirm_delete' => '1'])
            ->assertSessionHas('error');
        $this->assertDatabaseHas('vocabularies', ['id' => $vocabulary->id]);
    }

    public function test_owner_can_deactivate_and_reactivate_vocabulary(): void
    {
        $topic = Topic::factory()->create();
        $vocabulary = Vocabulary::factory()->for($topic)->active()->create();

        $this->asOwner()->patch(route('manage.vocabulary.status.update', $vocabulary), ['status' => 'inactive']);
        $this->assertDatabaseHas('vocabularies', ['id' => $vocabulary->id, 'status' => 'inactive']);

        $this->asOwner()->patch(route('manage.vocabulary.status.update', $vocabulary), ['status' => 'active']);
        $this->assertDatabaseHas('vocabularies', ['id' => $vocabulary->id, 'status' => 'active']);
    }

    private function payload(Topic $topic, array $overrides = []): array
    {
        return array_merge([
            'topic_id' => $topic->id,
            'term' => 'keep track of',
            'part_of_speech' => 'phrase',
            'phonetic' => null,
            'definition' => 'To continue to know what is happening with something.',
            'translation' => 'theo dõi',
            'example_sentence' => 'I keep track of my weekly study tasks.',
            'notes' => 'Synthetic test content.',
            'pronunciation_audio_path' => null,
            'source_type' => 'original',
            'source_reference' => null,
            'license_name' => null,
            'license_url' => null,
            'source_notes' => 'Original test fixture.',
            'status' => 'draft',
        ], $overrides);
    }
}
