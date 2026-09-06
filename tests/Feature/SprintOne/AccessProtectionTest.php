<?php

namespace Tests\Feature\SprintOne;

use App\Models\GrammarLesson;
use App\Models\ListeningContent;
use App\Models\Passage;
use App\Models\Question;
use App\Models\Topic;
use App\Models\Vocabulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_every_sprint_one_page_family(): void
    {
        $topic = Topic::factory()->create();
        $vocabulary = Vocabulary::factory()->for($topic)->create();
        $grammarTopic = Topic::factory()->grammar()->create();
        $grammarLesson = GrammarLesson::factory()->for($grammarTopic)->create();
        $readingTopic = Topic::factory()->create(['area' => 'reading']);
        $passage = Passage::query()->create($this->passagePayload($readingTopic));
        $listeningTopic = Topic::factory()->create(['area' => 'listening']);
        $listening = ListeningContent::query()->create($this->listeningPayload($listeningTopic));
        $question = Question::query()->create($this->questionPayload($readingTopic, $passage));

        $urls = [
            route('vocabulary.index'),
            route('vocabulary.show', $vocabulary),
            route('grammar.index'),
            route('grammar.show', $grammarLesson),
            route('manage.dashboard'),
            route('manage.topics.index'),
            route('manage.topics.create'),
            route('manage.topics.edit', $topic),
            route('manage.vocabulary.index'),
            route('manage.vocabulary.create'),
            route('manage.vocabulary.show', $vocabulary),
            route('manage.vocabulary.edit', $vocabulary),
            route('manage.grammar.index'),
            route('manage.grammar.create'),
            route('manage.grammar.show', $grammarLesson),
            route('manage.grammar.edit', $grammarLesson),
            route('reading.index'),
            route('reading.show', $passage),
            route('listening.index'),
            route('listening.show', $listening),
            route('manage.reading.index'),
            route('manage.reading.create'),
            route('manage.reading.show', $passage),
            route('manage.reading.edit', $passage),
            route('manage.listening.index'),
            route('manage.listening.create'),
            route('manage.listening.show', $listening),
            route('manage.listening.edit', $listening),
            route('manage.questions.index'),
            route('manage.questions.create'),
            route('manage.questions.show', $question),
            route('manage.questions.edit', $question),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertRedirectToRoute('login');
        }
    }

    public function test_guests_cannot_use_sprint_one_write_routes(): void
    {
        $topic = Topic::factory()->create();
        $vocabulary = Vocabulary::factory()->for($topic)->create();
        $grammarTopic = Topic::factory()->grammar()->create();
        $grammarLesson = GrammarLesson::factory()->for($grammarTopic)->create();
        $readingTopic = Topic::factory()->create(['area' => 'reading']);
        $passage = Passage::query()->create($this->passagePayload($readingTopic));
        $listeningTopic = Topic::factory()->create(['area' => 'listening']);
        $listening = ListeningContent::query()->create($this->listeningPayload($listeningTopic));
        $question = Question::query()->create($this->questionPayload($readingTopic, $passage));

        $this->post(route('manage.topics.store'))->assertRedirectToRoute('login');
        $this->put(route('manage.topics.update', $topic))->assertRedirectToRoute('login');
        $this->patch(route('manage.topics.status.update', $topic))->assertRedirectToRoute('login');
        $this->post(route('manage.vocabulary.store'))->assertRedirectToRoute('login');
        $this->put(route('manage.vocabulary.update', $vocabulary))->assertRedirectToRoute('login');
        $this->patch(route('vocabulary.progress.update', $vocabulary))->assertRedirectToRoute('login');
        $this->post(route('manage.grammar.store'))->assertRedirectToRoute('login');
        $this->put(route('manage.grammar.update', $grammarLesson))->assertRedirectToRoute('login');
        $this->post(route('manage.reading.store'))->assertRedirectToRoute('login');
        $this->put(route('manage.reading.update', $passage))->assertRedirectToRoute('login');
        $this->patch(route('manage.reading.status.update', $passage))->assertRedirectToRoute('login');
        $this->post(route('manage.listening.store'))->assertRedirectToRoute('login');
        $this->put(route('manage.listening.update', $listening))->assertRedirectToRoute('login');
        $this->patch(route('manage.listening.status.update', $listening))->assertRedirectToRoute('login');
        $this->post(route('manage.questions.store'))->assertRedirectToRoute('login');
        $this->put(route('manage.questions.update', $question))->assertRedirectToRoute('login');
        $this->patch(route('manage.questions.status.update', $question))->assertRedirectToRoute('login');
    }

    public function test_owner_write_without_csrf_token_is_rejected_in_production_mode(): void
    {
        $originalEnvironment = $this->app->environment();
        $this->app['env'] = 'production';

        try {
            $response = $this->asOwner()->post(route('manage.topics.store'), $this->validTopicPayload());
        } finally {
            $this->app['env'] = $originalEnvironment;
        }

        $response->assertStatus(419);
        $this->assertDatabaseCount('topics', 0);
    }

    private function validTopicPayload(): array
    {
        return [
            'area' => 'vocabulary',
            'name' => 'Education and study',
            'slug' => 'education-study',
            'description' => 'Synthetic topic.',
            'position' => 10,
            'priority' => 1,
            'status' => 'draft',
        ];
    }

    private function passagePayload(Topic $topic): array
    {
        return [
            'topic_id' => $topic->id,
            'title' => 'Synthetic reading passage',
            'body' => 'A short synthetic reading passage for access tests.',
            'word_count' => 9,
            'cefr_level' => 'B1',
            'difficulty' => 1,
            'source_type' => 'original',
            'source_notes' => 'Test fixture.',
            'status' => 'draft',
        ];
    }

    private function listeningPayload(Topic $topic): array
    {
        return [
            'topic_id' => $topic->id,
            'title' => 'Synthetic listening item',
            'transcript' => 'A short synthetic transcript.',
            'audio_path' => 'audio/listening/test.mp3',
            'audio_mime' => 'audio/mpeg',
            'audio_size_bytes' => 1,
            'cefr_level' => 'B1',
            'difficulty' => 1,
            'source_type' => 'original',
            'source_notes' => 'Test fixture.',
            'status' => 'draft',
        ];
    }

    private function questionPayload(Topic $topic, Passage $passage): array
    {
        return [
            'topic_id' => $topic->id,
            'passage_id' => $passage->id,
            'skill' => 'reading',
            'type' => 'single_choice',
            'prompt' => 'What is this?',
            'cefr_level' => 'B1',
            'difficulty' => 1,
            'source_type' => 'original',
            'source_notes' => 'Test fixture.',
            'status' => 'draft',
        ];
    }
}
