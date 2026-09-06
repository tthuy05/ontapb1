<?php

namespace Tests\Feature\SprintTwo;

use App\Models\ListeningContent;
use App\Models\Passage;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_reading_list_and_detail_show_only_active_content_and_hide_answer_keys(): void
    {
        $topic = Topic::factory()->active()->create(['area' => 'reading', 'name' => 'Reading topic']);
        $inactiveTopic = Topic::factory()->create(['area' => 'reading', 'name' => 'Hidden topic']);
        $active = Passage::query()->create($this->passage($topic, ['title' => 'Active passage', 'status' => 'active']));
        $draft = Passage::query()->create($this->passage($topic, ['title' => 'Draft passage']));
        $hidden = Passage::query()->create($this->passage($inactiveTopic, ['title' => 'Hidden passage', 'status' => 'active']));
        $question = Question::query()->create($this->question($topic, $active));
        QuestionOption::query()->create(['question_id' => $question->id, 'option_key' => 'A', 'content' => 'Visible option', 'is_correct' => true, 'position' => 0]);

        $this->asOwner()->get(route('reading.index'))
            ->assertOk()
            ->assertSee('Active passage')
            ->assertDontSee('Draft passage')
            ->assertDontSee('Hidden passage');

        $this->asOwner()->get(route('reading.show', $active))
            ->assertOk()
            ->assertSee('Visible option')
            ->assertDontSee('is_correct')
            ->assertDontSee('correct', false);
        $this->asOwner()->get(route('reading.show', $draft))->assertNotFound();
        $this->asOwner()->get(route('reading.show', $hidden))->assertNotFound();
    }

    public function test_listening_list_and_detail_show_active_items_without_transcript_by_default(): void
    {
        $topic = Topic::factory()->active()->create(['area' => 'listening']);
        $active = ListeningContent::query()->create($this->listening($topic, ['title' => 'Active audio', 'status' => 'active']));
        $draft = ListeningContent::query()->create($this->listening($topic, ['title' => 'Draft audio']));

        $this->asOwner()->get(route('listening.index'))
            ->assertOk()
            ->assertSee('Active audio')
            ->assertDontSee('Draft audio');

        $response = $this->asOwner()->get(route('listening.show', $active));
        $response->assertOk()->assertSee('Active audio')->assertSee('<details', false)
            ->assertSee('A short synthetic transcript.');
        $this->asOwner()->get(route('listening.show', $draft))->assertNotFound();
    }

    private function passage(Topic $topic, array $overrides = []): array
    {
        return array_merge([
            'topic_id' => $topic->id,
            'title' => 'Test passage',
            'body' => 'A short synthetic reading passage for tests.',
            'word_count' => 7,
            'cefr_level' => 'B1',
            'difficulty' => 1,
            'source_type' => 'original',
            'source_notes' => 'Test fixture.',
            'status' => 'draft',
        ], $overrides);
    }

    private function listening(Topic $topic, array $overrides = []): array
    {
        return array_merge([
            'topic_id' => $topic->id,
            'title' => 'Test listening',
            'transcript' => 'A short synthetic transcript.',
            'audio_path' => 'audio/listening/test.mp3',
            'audio_mime' => 'audio/mpeg',
            'audio_size_bytes' => 1,
            'cefr_level' => 'B1',
            'difficulty' => 1,
            'source_type' => 'original',
            'source_notes' => 'Test fixture.',
            'status' => 'draft',
        ], $overrides);
    }

    private function question(Topic $topic, Passage $passage): array
    {
        return [
            'topic_id' => $topic->id,
            'passage_id' => $passage->id,
            'skill' => 'reading',
            'type' => 'single_choice',
            'prompt' => 'What is this?',
            'explanation' => 'The passage says so.',
            'cefr_level' => 'B1',
            'difficulty' => 1,
            'source_type' => 'original',
            'source_notes' => 'Test fixture.',
            'status' => 'active',
        ];
    }
}
