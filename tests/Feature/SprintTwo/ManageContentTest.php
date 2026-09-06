<?php

namespace Tests\Feature\SprintTwo;

use App\Models\Passage;
use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_reading_content_with_server_calculated_word_count(): void
    {
        $topic = Topic::factory()->active()->create(['area' => 'reading']);
        $payload = $this->passagePayload($topic);

        $response = $this->asOwner()->post(route('manage.reading.store'), $payload);

        $passage = Passage::query()->sole();
        $response->assertRedirectToRoute('manage.reading.edit', $passage);
        $this->assertSame(8, $passage->word_count);
    }

    public function test_active_reading_requires_active_topic_and_provenance(): void
    {
        $topic = Topic::factory()->create(['area' => 'reading', 'status' => 'draft']);
        $response = $this->asOwner()->post(route('manage.reading.store'), $this->passagePayload($topic, [
            'status' => 'active',
            'source_notes' => null,
        ]));

        $response->assertSessionHasErrors(['topic_id', 'source_notes']);
        $this->assertDatabaseCount('passages', 0);
    }

    public function test_owner_can_create_listening_content_and_invalid_audio_path_is_rejected(): void
    {
        $topic = Topic::factory()->active()->create(['area' => 'listening']);
        $this->asOwner()->post(route('manage.listening.store'), $this->listeningPayload($topic))
            ->assertRedirect();
        $this->assertDatabaseHas('listening_contents', ['title' => 'Synthetic listening item']);

        $response = $this->asOwner()->post(route('manage.listening.store'), $this->listeningPayload($topic, ['audio_path' => '../secret.mp3']));
        $response->assertSessionHasErrors('audio_path');
        $this->assertDatabaseCount('listening_contents', 1);
    }

    public function test_draft_content_can_be_deleted_but_published_content_is_preserved(): void
    {
        $topic = Topic::factory()->active()->create(['area' => 'reading']);
        $draft = Passage::query()->create($this->passagePayload($topic));
        $this->asOwner()->delete(route('manage.reading.destroy', $draft), ['confirm_delete' => '1'])
            ->assertRedirectToRoute('manage.reading.index');
        $this->assertDatabaseMissing('passages', ['id' => $draft->id]);

        $active = Passage::query()->create($this->passagePayload($topic, ['status' => 'active']));
        $this->asOwner()->delete(route('manage.reading.destroy', $active), ['confirm_delete' => '1'])
            ->assertRedirect();
        $this->assertDatabaseHas('passages', ['id' => $active->id, 'status' => 'active']);
    }

    private function passagePayload(Topic $topic, array $overrides = []): array
    {
        return array_merge([
            'topic_id' => $topic->id,
            'title' => 'Synthetic reading item',
            'body' => 'One two three four five six seven eight.',
            'cefr_level' => 'B1',
            'difficulty' => 1,
            'source_type' => 'original',
            'source_notes' => 'Synthetic test fixture.',
            'status' => 'draft',
        ], $overrides);
    }

    private function listeningPayload(Topic $topic, array $overrides = []): array
    {
        return array_merge([
            'topic_id' => $topic->id,
            'title' => 'Synthetic listening item',
            'transcript' => 'A short listening script.',
            'audio_path' => 'audio/listening/synthetic.mp3',
            'audio_mime' => 'audio/mpeg',
            'audio_size_bytes' => 1,
            'cefr_level' => 'B1',
            'difficulty' => 1,
            'source_type' => 'original',
            'source_notes' => 'Synthetic test fixture.',
            'status' => 'draft',
        ], $overrides);
    }
}
