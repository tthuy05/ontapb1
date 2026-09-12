<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Exercise;
use App\Models\GrammarLesson;
use App\Models\ListeningContent;
use App\Models\Passage;
use App\Models\Question;
use App\Models\SpeakingPrompt;
use App\Models\SpeakingSubmission;
use App\Models\Topic;
use App\Models\Vocabulary;
use App\Models\WritingPrompt;
use App\Models\WritingSubmission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ContentQualityReport
{
    public function run(): array
    {
        $issues = [];
        $counts = [];

        $this->scanTopics($issues, $counts);
        $this->scanVocabularies($issues, $counts);
        $this->scanGrammar($issues, $counts);
        $this->scanPassages($issues, $counts);
        $this->scanListening($issues, $counts);
        $this->scanQuestions($issues, $counts);
        $this->scanExercises($issues, $counts);
        $this->scanExams($issues, $counts);
        $this->scanPrompts(WritingPrompt::class, 'writing_prompts', WritingPrompt::STATUSES, $issues, $counts);
        $this->scanPrompts(SpeakingPrompt::class, 'speaking_prompts', SpeakingPrompt::STATUSES, $issues, $counts);
        $this->scanSubmissions($issues, $counts);

        return [
            'checked_at' => now('UTC')->toIso8601String(),
            'counts' => $counts,
            'issues' => $issues,
            'issue_count' => count($issues),
            'record_count' => array_sum($counts),
        ];
    }

    private function scanTopics(array &$issues, array &$counts): void
    {
        if (! $this->hasTable('topics')) {
            $this->missingTable('topics', $issues, $counts);

            return;
        }

        $topics = Topic::query()->get();
        $counts['topics'] = $topics->count();

        foreach ($topics as $topic) {
            if (! in_array($topic->area, Topic::AREAS, true)) {
                $this->issue($issues, 'topics', $topic->id, 'area must use an approved value.');
            }
            if (trim((string) $topic->name) === '' || trim((string) $topic->slug) === '') {
                $this->issue($issues, 'topics', $topic->id, 'name and slug are required.');
            }
            if (! in_array($topic->status, Topic::STATUSES, true)) {
                $this->issue($issues, 'topics', $topic->id, 'status must use an approved value.');
            }
        }
    }

    private function scanVocabularies(array &$issues, array &$counts): void
    {
        if (! $this->hasTable('vocabularies')) {
            $this->missingTable('vocabularies', $issues, $counts);

            return;
        }

        $items = Vocabulary::query()->with('topic')->get();
        $counts['vocabularies'] = $items->count();

        foreach ($items as $item) {
            $this->validateSource($item, 'vocabularies', Vocabulary::SOURCE_TYPES, $issues);
            $this->validateTopic($item, 'vocabularies', $issues);
            if (trim((string) $item->term) === '' || trim((string) $item->definition) === '') {
                $this->issue($issues, 'vocabularies', $item->id, 'term and definition are required.');
            }
        }
    }

    private function scanGrammar(array &$issues, array &$counts): void
    {
        if (! $this->hasTable('grammar_lessons')) {
            $this->missingTable('grammar_lessons', $issues, $counts);

            return;
        }

        $items = GrammarLesson::query()->with('topic')->get();
        $counts['grammar_lessons'] = $items->count();

        foreach ($items as $item) {
            $this->validateSource($item, 'grammar_lessons', GrammarLesson::SOURCE_TYPES, $issues);
            $this->validateTopic($item, 'grammar_lessons', $issues);
            if (trim((string) $item->title) === '' || trim((string) $item->body) === '') {
                $this->issue($issues, 'grammar_lessons', $item->id, 'title and body are required.');
            }
        }
    }

    private function scanPassages(array &$issues, array &$counts): void
    {
        if (! $this->hasTable('passages')) {
            $this->missingTable('passages', $issues, $counts);

            return;
        }

        $items = Passage::query()->with('topic')->get();
        $counts['passages'] = $items->count();

        foreach ($items as $item) {
            $this->validateSource($item, 'passages', Passage::SOURCE_TYPES, $issues);
            $this->validateTopic($item, 'passages', $issues);
            if (trim((string) $item->title) === '' || trim((string) $item->body) === '') {
                $this->issue($issues, 'passages', $item->id, 'title and body are required.');
            }
            if ($item->word_count !== $this->countWords($item->body)) {
                $this->issue($issues, 'passages', $item->id, 'word_count does not match the passage body.');
            }
        }
    }

    private function scanListening(array &$issues, array &$counts): void
    {
        if (! $this->hasTable('listening_contents')) {
            $this->missingTable('listening_contents', $issues, $counts);

            return;
        }

        $items = ListeningContent::query()->with('topic')->get();
        $counts['listening_contents'] = $items->count();

        foreach ($items as $item) {
            $this->validateSource($item, 'listening_contents', ListeningContent::SOURCE_TYPES, $issues);
            $this->validateTopic($item, 'listening_contents', $issues);
            if (trim((string) $item->title) === '' || trim((string) $item->transcript) === '') {
                $this->issue($issues, 'listening_contents', $item->id, 'title and transcript are required.');
            }
            if (! in_array($item->audio_mime, ListeningContent::AUDIO_MIMES, true)) {
                $this->issue($issues, 'listening_contents', $item->id, 'audio_mime is not an approved audio type.');
            }
            if ((int) $item->audio_size_bytes < 1 || trim((string) $item->audio_path) === '') {
                $this->issue($issues, 'listening_contents', $item->id, 'audio path and positive audio size are required.');
            }
        }
    }

    private function scanQuestions(array &$issues, array &$counts): void
    {
        if (! $this->hasTable('questions')) {
            $this->missingTable('questions', $issues, $counts);

            return;
        }

        $items = Question::query()->with(['topic', 'passage', 'listeningContent', 'options'])->get();
        $counts['questions'] = $items->count();

        foreach ($items as $item) {
            $this->validateSource($item, 'questions', Question::SOURCE_TYPES, $issues);
            $this->validateTopic($item, 'questions', $issues);

            if (! in_array($item->skill, Question::SKILLS, true) || ! in_array($item->type, Question::TYPES, true)) {
                $this->issue($issues, 'questions', $item->id, 'skill and type must use approved values.');
            }

            $contextCount = (int) ($item->passage_id !== null) + (int) ($item->listening_content_id !== null);
            if ($item->status === 'active' && ($contextCount !== 1 || ($item->skill === 'reading' && $item->passage_id === null) || ($item->skill === 'listening' && $item->listening_content_id === null))) {
                $this->issue($issues, 'questions', $item->id, 'active questions must have exactly one matching Reading or Listening context.');
            }

            if ($item->status === 'active') {
                $correctCount = $item->options->where('is_correct', true)->count();
                $keys = $item->options->pluck('option_key')->map(fn ($key): string => (string) $key)->unique();
                if ($item->options->count() < 2 || $correctCount !== 1 || $keys->count() !== $item->options->count()) {
                    $this->issue($issues, 'questions', $item->id, 'active questions need at least two unique options and exactly one correct option.');
                }
                if ($item->type === 'true_false' && $keys->sort()->values()->all() !== ['false', 'true']) {
                    $this->issue($issues, 'questions', $item->id, 'true_false questions must have true and false options.');
                }
            }
        }
    }

    private function scanExercises(array &$issues, array &$counts): void
    {
        if (! $this->hasTable('exercises')) {
            $this->missingTable('exercises', $issues, $counts);

            return;
        }

        $items = Exercise::query()->with(['topic', 'exerciseQuestions.question'])->get();
        $counts['exercises'] = $items->count();

        foreach ($items as $item) {
            $this->validateTopic($item, 'exercises', $issues);
            if (! in_array($item->skill, Exercise::SKILLS, true)) {
                $this->issue($issues, 'exercises', $item->id, 'skill must use an approved value.');
            }
            if ($item->status === 'active') {
                if ($item->exerciseQuestions->isEmpty()) {
                    $this->issue($issues, 'exercises', $item->id, 'active exercises need at least one question.');
                }
                foreach ($item->exerciseQuestions as $link) {
                    if (! $link->question || $link->question->status !== 'active' || $link->question->skill !== $item->skill) {
                        $this->issue($issues, 'exercises', $item->id, 'active exercises may link only active questions with the same skill.');
                    }
                }
            }
        }
    }

    private function scanExams(array &$issues, array &$counts): void
    {
        if (! $this->hasTable('exams')) {
            $this->missingTable('exams', $issues, $counts);

            return;
        }

        $items = Exam::query()->with(['sections.items.question'])->get();
        $counts['exams'] = $items->count();

        foreach ($items as $item) {
            if (! in_array($item->format_label, Exam::FORMAT_LABELS, true)) {
                $this->issue($issues, 'exams', $item->id, 'format_label must use an approved value.');
            }
            if ($item->status !== 'active') {
                continue;
            }
            if ($item->sections->isEmpty()) {
                $this->issue($issues, 'exams', $item->id, 'active exams need at least one section.');
            }
            foreach ($item->sections as $section) {
                if (! in_array($section->skill, Exam::SKILLS, true) || $section->items->isEmpty()) {
                    $this->issue($issues, 'exams', $item->id, 'active exam sections need an approved skill and at least one item.');
                }
                foreach ($section->items as $entry) {
                    if (! $entry->question || $entry->question->status !== 'active' || $entry->question->skill !== $section->skill) {
                        $this->issue($issues, 'exams', $item->id, 'active exams may link only active questions matching the section skill.');
                    }
                }
            }
        }
    }

    private function scanPrompts(string $modelClass, string $table, array $statuses, array &$issues, array &$counts): void
    {
        if (! $this->hasTable($table)) {
            $this->missingTable($table, $issues, $counts);

            return;
        }

        $items = $modelClass::query()->with('topic')->get();
        $counts[$table] = $items->count();

        foreach ($items as $item) {
            $sourceTypes = $modelClass === WritingPrompt::class ? WritingPrompt::SOURCE_TYPES : SpeakingPrompt::SOURCE_TYPES;
            $this->validateSource($item, $table, $sourceTypes, $issues);
            $this->validateTopic($item, $table, $issues);
            if (! in_array($item->status, $statuses, true) || trim((string) $item->title) === '' || trim((string) $item->instructions) === '') {
                $this->issue($issues, $table, $item->id, 'status, title, and instructions must be valid.');
            }
        }
    }

    private function scanSubmissions(array &$issues, array &$counts): void
    {
        if ($this->hasTable('writing_submissions')) {
            $items = WritingSubmission::query()->get();
            $counts['writing_submissions'] = $items->count();
            foreach ($items as $item) {
                if (! in_array($item->status, WritingSubmission::STATUSES, true) || ! is_array($item->prompt_snapshot)) {
                    $this->issue($issues, 'writing_submissions', $item->id, 'status and prompt_snapshot must be valid.');
                }
                if ($item->status === 'submitted' && ($item->submitted_at === null || trim((string) $item->response_text) === '' || $item->word_count !== WritingSubmission::countWords($item->response_text))) {
                    $this->issue($issues, 'writing_submissions', $item->id, 'submitted writing must have text, matching word_count, and submitted_at.');
                }
            }
        }

        if ($this->hasTable('speaking_submissions')) {
            $items = SpeakingSubmission::query()->get();
            $counts['speaking_submissions'] = $items->count();
            foreach ($items as $item) {
                if (! in_array($item->status, SpeakingSubmission::STATUSES, true) || ! is_array($item->prompt_snapshot)) {
                    $this->issue($issues, 'speaking_submissions', $item->id, 'status and prompt_snapshot must be valid.');
                }
                if ($item->status === 'submitted' && ($item->completed_at === null || (int) $item->duration_seconds < 1)) {
                    $this->issue($issues, 'speaking_submissions', $item->id, 'submitted speaking must have a positive duration and completed_at.');
                }
            }
        }
    }

    private function validateSource(Model $item, string $table, array $sourceTypes, array &$issues): void
    {
        if (! in_array($item->source_type, $sourceTypes, true)) {
            $this->issue($issues, $table, $item->id, 'source_type must use an approved value.');
        }
        if ($item->status === 'active' && $item->source_type === 'original' && trim((string) $item->source_notes) === '') {
            $this->issue($issues, $table, $item->id, 'active original content needs source_notes.');
        }
        if ($item->status === 'active' && $item->source_type !== 'original' && trim((string) $item->source_reference) === '') {
            $this->issue($issues, $table, $item->id, 'active non-original content needs source_reference.');
        }
    }

    private function validateTopic(Model $item, string $table, array &$issues): void
    {
        if ($item->status === 'active' && $item->topic_id !== null && $item->topic?->status !== 'active') {
            $this->issue($issues, $table, $item->id, 'active content must use an active topic or no topic.');
        }
    }

    private function missingTable(string $table, array &$issues, array &$counts): void
    {
        $counts[$table] = 0;
        $this->issue($issues, $table, null, 'required table is missing.');
    }

    private function issue(array &$issues, string $table, int|string|null $id, string $message): void
    {
        $issues[] = compact('table', 'id', 'message');
    }

    private function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    private function countWords(?string $text): int
    {
        return preg_match_all('/\S+/u', trim((string) $text), $matches) ?: 0;
    }
}
