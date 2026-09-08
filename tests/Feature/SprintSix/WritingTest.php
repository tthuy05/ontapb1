<?php

namespace Tests\Feature\SprintSix;

use App\Models\WritingPrompt;
use App\Models\WritingSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WritingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_open_writing_or_manage_writing(): void
    {
        $this->get(route('writing.index'))->assertRedirectToRoute('login');
        $this->get(route('manage.writing.index'))->assertRedirectToRoute('login');
    }

    public function test_active_prompts_are_visible_and_drafts_are_hidden(): void
    {
        $active = $this->prompt(['title' => 'Active writing prompt', 'status' => 'active']);
        $this->prompt(['title' => 'Draft writing prompt', 'status' => 'draft']);

        $this->asOwner()->get(route('writing.index'))->assertOk()->assertSee($active->title)->assertDontSee('Draft writing prompt');
        $this->asOwner()->get(route('writing.show', $active))->assertOk()->assertSee('Task instructions');
    }

    public function test_owner_can_save_and_submit_a_response_with_server_word_count(): void
    {
        $prompt = $this->prompt(['status' => 'active']);
        $draftResponse = $this->asOwner()->post(route('writing.submissions.store', $prompt), [
            'status' => 'draft',
            'response_text' => "One\nshort, response! café",
            'self_check' => ['grammar' => 4],
        ]);

        $submission = WritingSubmission::query()->sole();
        $draftResponse->assertRedirectToRoute('writing.submissions.show', $submission);
        $this->assertSame(4, $submission->word_count);
        $this->assertSame('Snapshot title', $submission->prompt_snapshot['title']);

        $this->asOwner()->patch(route('writing.submissions.update', $submission), [
            'status' => 'submitted',
            'response_text' => 'A durable submitted response with enough words.',
            'save_version' => 0,
        ])->assertRedirectToRoute('writing.submissions.show', $submission);

        $submission->refresh();
        $this->assertSame('submitted', $submission->status);
        $this->assertSame(7, $submission->word_count);
        $this->assertNotNull($submission->submitted_at);
        $this->asOwner()->get(route('writing.submissions.show', $submission))->assertOk()->assertSee('not an official VSTEP score')->assertSee('durable submitted response');
    }

    public function test_prompt_snapshot_is_immutable_and_submitted_response_cannot_be_reopened(): void
    {
        $prompt = $this->prompt(['title' => 'Snapshot title', 'instructions' => 'Original task instructions.', 'status' => 'active']);
        $this->asOwner()->post(route('writing.submissions.store', $prompt), ['status' => 'submitted', 'response_text' => 'Keep this historical response.']);
        $submission = WritingSubmission::query()->sole();

        $prompt->update(['title' => 'Changed live title', 'instructions' => 'Changed live task.']);
        $submission->refresh();
        $this->assertSame('Snapshot title', $submission->prompt_snapshot['title']);
        $this->asOwner()->patch(route('writing.submissions.update', $submission), ['status' => 'draft', 'response_text' => 'Changed response', 'save_version' => 0])->assertStatus(409);
    }

    public function test_word_count_handles_unicode_whitespace_and_punctuation_and_submission_is_bounded(): void
    {
        $this->assertSame(5, WritingSubmission::countWords("Don't stop, café 2026!\nnow"));
        $prompt = $this->prompt(['status' => 'active']);
        $this->asOwner()->post(route('writing.submissions.store', $prompt), ['status' => 'submitted', 'response_text' => ''])->assertSessionHasErrors('response_text');
        $this->asOwner()->post(route('writing.submissions.store', $prompt), ['status' => 'draft', 'response_text' => str_repeat('x ', 50001)])->assertSessionHasErrors('response_text');
        $this->assertDatabaseCount('writing_submissions', 0);
    }

    public function test_management_can_create_preview_and_publish_original_prompt(): void
    {
        $payload = [
            'task_type' => 'task_2', 'title' => 'Managed writing prompt', 'instructions' => 'Write a short essay.',
            'minimum_words' => 100, 'recommended_minutes' => 30, 'guidance' => 'Plan first.',
            'checklist_text' => "Content\nOrganization", 'source_type' => 'original', 'source_notes' => 'Synthetic test prompt.', 'status' => 'draft',
        ];
        $response = $this->asOwner()->post(route('manage.writing.store'), $payload);
        $prompt = WritingPrompt::query()->where('title', 'Managed writing prompt')->sole();
        $response->assertRedirectToRoute('manage.writing.edit', $prompt);
        $this->asOwner()->get(route('manage.writing.preview', $prompt))->assertOk()->assertSee('Managed writing prompt');
        $this->asOwner()->patch(route('manage.writing.status.update', $prompt), ['status' => 'active'])->assertSessionHas('status');
        $this->assertDatabaseHas('writing_prompts', ['id' => $prompt->id, 'status' => 'active', 'checklist' => '["Content","Organization"]']);
    }

    public function test_writing_submission_schema_has_history_constraints(): void
    {
        $this->assertTrue(Schema::hasTable('writing_submissions'));
        $indexes = collect(Schema::getIndexes('writing_submissions'))->pluck('name')->all();
        $this->assertContains('writing_submissions_attempt_item_unique', $indexes);
        $this->assertContains('writing_submissions_writing_prompt_id_status_index', $indexes);
    }

    private function prompt(array $overrides = []): WritingPrompt
    {
        return WritingPrompt::query()->create(array_merge([
            'topic_id' => null, 'task_type' => 'task_1', 'title' => 'Snapshot title',
            'instructions' => 'Write a response.', 'minimum_words' => 10, 'recommended_minutes' => 10,
            'guidance' => 'Plan.', 'checklist' => ['Check content.'], 'source_type' => 'original',
            'source_notes' => 'Synthetic fixture.', 'status' => 'active',
        ], $overrides));
    }
}
