<?php

namespace Tests\Feature\SprintSeven;

use App\Models\SpeakingPrompt;
use App\Models\SpeakingSubmission;
use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SpeakingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_open_speaking_or_manage_speaking(): void
    {
        $this->get(route('speaking.index'))->assertRedirectToRoute('login');
        $this->get(route('manage.speaking.index'))->assertRedirectToRoute('login');
    }

    public function test_active_prompts_are_visible_and_drafts_or_inactive_topics_are_hidden(): void
    {
        $active = $this->prompt(['title' => 'Active speaking prompt', 'status' => 'active']);
        $this->prompt(['title' => 'Draft speaking prompt', 'status' => 'draft']);
        $inactiveTopic = Topic::query()->create(['area' => 'speaking', 'name' => 'Inactive speaking topic', 'slug' => 'inactive-speaking-topic', 'status' => 'inactive']);
        $hidden = $this->prompt(['title' => 'Hidden topic prompt', 'topic_id' => $inactiveTopic->id, 'status' => 'active']);

        $this->asOwner()->get(route('speaking.index'))->assertOk()->assertSee($active->title)->assertDontSee('Draft speaking prompt')->assertDontSee('Hidden topic prompt');
        $this->asOwner()->get(route('speaking.show', $active))->assertOk()->assertSee('Prompt instructions');
        $this->asOwner()->get(route('speaking.show', $hidden))->assertNotFound();
    }

    public function test_owner_can_save_a_draft_and_submit_metadata_for_self_review(): void
    {
        $prompt = $this->prompt(['status' => 'active']);
        $draftResponse = $this->asOwner()->post(route('speaking.submissions.store', $prompt), [
            'status' => 'draft',
            'self_assessment' => ['fluency' => 3],
            'notes' => 'Slow down after the opening sentence.',
        ]);

        $submission = SpeakingSubmission::query()->sole();
        $draftResponse->assertRedirectToRoute('speaking.submissions.show', $submission);
        $this->assertSame('draft', $submission->status);
        $this->assertSame('Snapshot title', $submission->prompt_snapshot['title']);

        $this->asOwner()->patch(route('speaking.submissions.update', $submission), [
            'status' => 'submitted',
            'duration_seconds' => 42,
            'self_assessment' => ['content' => 4, 'fluency' => 3, 'pronunciation' => 4],
            'notes' => 'Repeat once with clearer linking.',
            'save_version' => 0,
        ])->assertRedirectToRoute('speaking.submissions.show', $submission);

        $submission->refresh();
        $this->assertSame('submitted', $submission->status);
        $this->assertSame(42, $submission->duration_seconds);
        $this->assertNotNull($submission->completed_at);
        $this->asOwner()->get(route('speaking.submissions.show', $submission))->assertOk()->assertSee('not an official VSTEP score')->assertSee('Repeat once with clearer linking.');
    }

    public function test_submitted_practice_is_immutable_and_snapshot_survives_prompt_edits(): void
    {
        $prompt = $this->prompt(['status' => 'active']);
        $this->asOwner()->post(route('speaking.submissions.store', $prompt), ['status' => 'submitted', 'duration_seconds' => 30]);
        $submission = SpeakingSubmission::query()->sole();

        $prompt->update(['title' => 'Changed live title', 'instructions' => 'Changed live instructions.']);
        $submission->refresh();
        $this->assertSame('Snapshot title', $submission->prompt_snapshot['title']);
        $this->asOwner()->patch(route('speaking.submissions.update', $submission), ['status' => 'draft', 'duration_seconds' => 31, 'save_version' => 0])->assertStatus(409);
    }

    public function test_submitted_practice_requires_duration_and_is_bounded(): void
    {
        $prompt = $this->prompt(['status' => 'active']);
        $this->asOwner()->post(route('speaking.submissions.store', $prompt), ['status' => 'submitted'])->assertSessionHasErrors('duration_seconds');
        $this->asOwner()->post(route('speaking.submissions.store', $prompt), ['status' => 'draft', 'notes' => str_repeat('x', 20001)])->assertSessionHasErrors('notes');
        $this->asOwner()->post(route('speaking.submissions.store', $prompt), ['status' => 'draft', 'duration_seconds' => 3601])->assertSessionHasErrors('duration_seconds');
        $this->assertDatabaseCount('speaking_submissions', 0);
    }

    public function test_management_can_create_preview_and_publish_original_prompt(): void
    {
        $payload = [
            'part_type' => 'topic_development', 'title' => 'Managed speaking prompt', 'instructions' => 'Develop a short answer.',
            'preparation_seconds' => 30, 'speaking_seconds' => 90, 'suggested_ideas_text' => "First idea\nSecond idea",
            'follow_up_questions_text' => 'What changed?', 'checklist_text' => "Clear opening\nUseful detail",
            'source_type' => 'original', 'source_notes' => 'Synthetic test prompt.', 'status' => 'draft',
        ];
        $response = $this->asOwner()->post(route('manage.speaking.store'), $payload);
        $prompt = SpeakingPrompt::query()->where('title', 'Managed speaking prompt')->sole();
        $response->assertRedirectToRoute('manage.speaking.edit', $prompt);
        $this->asOwner()->get(route('manage.speaking.preview', $prompt))->assertOk()->assertSee('Managed speaking prompt');
        $this->asOwner()->patch(route('manage.speaking.status.update', $prompt), ['status' => 'active'])->assertSessionHas('status');
        $this->assertDatabaseHas('speaking_prompts', ['id' => $prompt->id, 'status' => 'active', 'suggested_ideas' => '["First idea","Second idea"]']);
    }

    public function test_speaking_submission_schema_has_forward_only_metadata_constraints(): void
    {
        $this->assertTrue(Schema::hasTable('speaking_submissions'));
        $this->assertTrue(Schema::hasColumns('speaking_submissions', ['speaking_prompt_id', 'prompt_snapshot', 'duration_seconds', 'self_assessment', 'notes', 'save_version', 'completed_at']));
        $this->assertFalse(Schema::hasColumns('speaking_submissions', ['audio_path', 'audio_blob', 'recording']));
        $indexes = collect(Schema::getIndexes('speaking_submissions'))->pluck('name')->all();
        $this->assertContains('speaking_submissions_attempt_item_unique', $indexes);
        $this->assertContains('speaking_submissions_speaking_prompt_id_status_index', $indexes);
    }

    private function prompt(array $overrides = []): SpeakingPrompt
    {
        return SpeakingPrompt::query()->create(array_merge([
            'topic_id' => null, 'part_type' => 'social_interaction', 'title' => 'Snapshot title',
            'instructions' => 'Prompt instructions.', 'preparation_seconds' => 5, 'speaking_seconds' => 45,
            'suggested_ideas' => ['Idea one'], 'follow_up_questions' => ['Question one'], 'checklist' => ['Check content.'],
            'source_type' => 'original', 'source_notes' => 'Synthetic fixture.', 'status' => 'active',
        ], $overrides));
    }
}
