<?php

namespace Tests\Feature\SprintOne;

use App\Models\GrammarLesson;
use App\Models\Topic;
use App\Models\Vocabulary;
use App\Models\VocabularyProgress;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_sprint_one_through_three_create_only_the_approved_domain_tables_and_no_user_columns(): void
    {
        foreach (['topics', 'vocabularies', 'vocabulary_progress', 'grammar_lessons', 'passages', 'listening_contents', 'questions', 'question_options'] as $table) {
            $this->assertTrue(Schema::hasTable($table));
            if ($table !== 'question_options') {
                $this->assertFalse(Schema::hasColumn($table, 'user_id'));
            }
        }

        foreach (['exercises', 'exercise_questions', 'writing_prompts', 'speaking_prompts', 'exams', 'exam_sections', 'exam_section_items', 'attempts', 'attempt_answers'] as $table) {
            $this->assertTrue(Schema::hasTable($table));
            $this->assertFalse(Schema::hasColumn($table, 'user_id'));
        }

        foreach (['users', 'exam_questions', 'reading_results', 'listening_results'] as $table) {
            $this->assertFalse(Schema::hasTable($table));
        }
    }

    public function test_sprint_three_schema_has_snapshot_and_ordering_constraints(): void
    {
        $this->assertTrue(Schema::hasColumn('attempts', 'configuration_snapshot'));
        $this->assertTrue(Schema::hasColumn('attempt_answers', 'question_snapshot'));
        $this->assertTrue(Schema::hasColumn('attempt_answers', 'save_version'));
        $this->assertTrue(Schema::hasColumn('attempt_answers', 'is_correct'));

        $indexes = collect(Schema::getIndexes('exercise_questions'))->pluck('name')->all();
        $this->assertContains('exercise_questions_exercise_id_question_id_unique', $indexes);
        $this->assertContains('exercise_questions_exercise_id_position_unique', $indexes);

        $answerIndexes = collect(Schema::getIndexes('attempt_answers'))->pluck('name')->all();
        $this->assertContains('attempt_answers_attempt_id_question_id_unique', $answerIndexes);
    }

    public function test_models_expose_the_approved_relationships(): void
    {
        $topic = Topic::factory()->create();
        $vocabulary = Vocabulary::factory()->for($topic)->create();
        $progress = VocabularyProgress::query()->create([
            'vocabulary_id' => $vocabulary->id,
            'state' => 'learning',
        ]);
        $grammarTopic = Topic::factory()->grammar()->create();
        $lesson = GrammarLesson::factory()->for($grammarTopic)->create();

        $this->assertTrue($topic->vocabularies->first()->is($vocabulary));
        $this->assertTrue($vocabulary->topic->is($topic));
        $this->assertTrue($vocabulary->progress->is($progress));
        $this->assertTrue($progress->vocabulary->is($vocabulary));
        $this->assertTrue($grammarTopic->grammarLessons->first()->is($lesson));
        $this->assertTrue($lesson->topic->is($grammarTopic));
    }

    public function test_database_enforces_one_progress_row_per_vocabulary(): void
    {
        $vocabulary = Vocabulary::factory()->create();
        VocabularyProgress::query()->create([
            'vocabulary_id' => $vocabulary->id,
            'state' => 'learning',
        ]);

        $this->expectException(QueryException::class);

        VocabularyProgress::query()->create([
            'vocabulary_id' => $vocabulary->id,
            'state' => 'review',
        ]);
    }
}
