<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class StudyExportCommand extends Command
{
    protected $signature = 'study:export
                            {--path= : Relative path on the private local disk}
                            {--pretty : Pretty-print the JSON export}';

    protected $description = 'Export study data and migration history without secrets or runtime files';

    private const TABLES = [
        'migrations',
        'topics',
        'vocabularies',
        'vocabulary_progress',
        'grammar_lessons',
        'passages',
        'listening_contents',
        'questions',
        'question_options',
        'exercises',
        'exercise_questions',
        'writing_prompts',
        'writing_submissions',
        'speaking_prompts',
        'speaking_submissions',
        'exams',
        'exam_sections',
        'exam_section_items',
        'attempts',
        'attempt_answers',
    ];

    public function handle(): int
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                $this->error("Cannot export: table {$table} is missing.");

                return self::FAILURE;
            }
        }

        $relativePath = trim((string) ($this->option('path') ?: ''));
        if ($relativePath === '') {
            $relativePath = 'exports/study-export-'.now('UTC')->format('Ymd\\THis\\Z').'.json';
        }
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

        if ($relativePath === '' || str_contains($relativePath, '..') || str_starts_with($relativePath, 'public/')) {
            $this->error('Export path must stay inside the private local disk.');

            return self::FAILURE;
        }

        $payload = [
            'format' => 'b1-english-self-study-export',
            'format_version' => 1,
            'exported_at' => now('UTC')->toIso8601String(),
            'tables' => [],
        ];

        foreach (self::TABLES as $table) {
            $payload['tables'][$table] = DB::table($table)->orderBy('id')->get()->map(
                static fn ($row): array => (array) $row,
            )->all();
        }

        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if ($this->option('pretty')) {
            $flags |= JSON_PRETTY_PRINT;
        }
        $json = json_encode($payload, $flags | JSON_THROW_ON_ERROR);
        Storage::disk('local')->put($relativePath, $json);

        $this->info("Study export written to private storage: {$relativePath}");
        $this->line('The export contains study tables only; it excludes .env, sessions, logs, cache, and audio files.');

        return self::SUCCESS;
    }
}
