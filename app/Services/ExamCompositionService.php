<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamSection;
use App\Models\ExamSectionItem;
use App\Models\Question;

class ExamCompositionService
{
    public function load(Exam $exam): Exam
    {
        return $exam->load([
            'sections' => fn ($query) => $query->orderBy('position')->with([
                'items' => fn ($itemQuery) => $itemQuery->orderBy('position')->with([
                    'question.topic',
                    'question.passage',
                    'question.listeningContent',
                    'question.options',
                    'writingPrompt',
                    'speakingPrompt',
                ]),
            ]),
        ]);
    }

    public function activationErrors(Exam $exam): array
    {
        $this->load($exam);
        $errors = [];
        $sections = $exam->sections;

        if ($exam->title === null || trim($exam->title) === '') {
            $errors[] = 'An active exam needs a title.';
        }
        if (! in_array($exam->format_label, Exam::FORMAT_LABELS, true)) {
            $errors[] = 'Choose a supported exam format.';
        }
        if ($sections->isEmpty()) {
            $errors[] = 'An active exam needs at least one section.';
        }

        $seenSectionPositions = [];
        $seenQuestionIds = [];
        $sectionTime = 0;

        foreach ($sections as $section) {
            if (in_array($section->position, $seenSectionPositions, true)) {
                $errors[] = 'Exam section positions must be unique.';
            }
            $seenSectionPositions[] = $section->position;

            if (! in_array($section->skill, Exam::SKILLS, true)) {
                $errors[] = 'Exam sections must use Reading or Listening skills.';
            }
            if (! in_array($section->navigation_mode, ExamSection::NAVIGATION_MODES, true)) {
                $errors[] = 'Exam sections use an unsupported navigation mode.';
            }
            if ($section->time_limit_seconds !== null) {
                $sectionTime += (int) $section->time_limit_seconds;
            }
            if ($section->items->isEmpty()) {
                $errors[] = 'Every active exam section needs at least one item.';
            }

            $expectedPositions = range(0, max(0, $section->items->count() - 1));
            $actualPositions = $section->items->pluck('position')->map(fn ($position): int => (int) $position)->all();
            if ($actualPositions !== $expectedPositions) {
                $errors[] = 'Items in each exam section must be ordered from position zero.';
            }

            foreach ($section->items as $item) {
                $references = collect([$item->question_id, $item->writing_prompt_id, $item->speaking_prompt_id])
                    ->filter(fn ($value): bool => $value !== null)
                    ->count();
                if ($references !== 1 || $item->question_id === null) {
                    $errors[] = 'Sprint 5 exam items must reference exactly one objective question.';
                    continue;
                }

                if (in_array($item->question_id, $seenQuestionIds, true)) {
                    $errors[] = 'An objective question may appear only once in an exam.';
                }
                $seenQuestionIds[] = $item->question_id;
                $question = $item->question;
                if (! $question) {
                    $errors[] = 'Every exam item must reference an existing question.';
                    continue;
                }
                if ($question->skill !== $section->skill) {
                    $errors[] = 'Every question must match its exam section skill.';
                }
                if ($question->status !== 'active') {
                    $errors[] = 'Every exam question must be active before the exam can be activated.';
                }
                if ($question->topic && $question->topic->status !== 'active') {
                    $errors[] = 'Every exam question topic must be active.';
                }
                if ($question->passage_id !== null && (! $question->passage || $question->passage->status !== 'active')) {
                    $errors[] = 'Every Reading context must be active before the exam can be activated.';
                }
                if ($question->listening_content_id !== null && (! $question->listeningContent || $question->listeningContent->status !== 'active')) {
                    $errors[] = 'Every Listening context must be active before the exam can be activated.';
                }

                $correct = $question->options->where('is_correct', true)->count();
                $validCount = $question->type === 'true_false'
                    ? $question->options->count() === 2
                    : $question->options->count() >= 2;
                if (! in_array($question->type, Question::TYPES, true) || ! $validCount || $correct !== 1) {
                    $errors[] = 'Every exam question must have a valid objective answer structure.';
                }
            }
        }

        $expectedSectionPositions = range(0, max(0, $sections->count() - 1));
        sort($seenSectionPositions);
        if ($seenSectionPositions !== $expectedSectionPositions) {
            $errors[] = 'Exam sections must be ordered from position zero.';
        }

        if ($exam->time_limit_seconds !== null && $sectionTime > (int) $exam->time_limit_seconds) {
            $errors[] = 'The overall exam time must cover its configured section limits.';
        }

        return array_values(array_unique($errors));
    }

    public function snapshot(Exam $exam): array
    {
        $this->load($exam);
        $skills = $exam->sections->pluck('skill')->unique()->values()->all();

        return [
            'snapshot_version' => 1,
            'kind' => 'exam',
            'exam_id' => $exam->id,
            'title' => $exam->title,
            'format_label' => $exam->format_label,
            'description' => $exam->description,
            'instructions' => $exam->instructions,
            'time_limit_seconds' => $exam->time_limit_seconds,
            'skill' => count($skills) === 1 ? $skills[0] : 'mixed',
            'skills' => $skills,
            'sections' => $exam->sections->map(fn (ExamSection $section): array => [
                'section_id' => $section->id,
                'position' => $section->position,
                'skill' => $section->skill,
                'title' => $section->title,
                'instructions' => $section->instructions,
                'time_limit_seconds' => $section->time_limit_seconds,
                'navigation_mode' => $section->navigation_mode,
                'items' => $section->items->map(fn (ExamSectionItem $item): array => [
                    'item_id' => $item->id,
                    'question_id' => $item->question_id,
                    'position' => $item->position,
                    'points' => (float) $item->points,
                ])->values()->all(),
            ])->values()->all(),
        ];
    }

    public function snapshotSection(array $snapshot, int $position): ?array
    {
        return collect($snapshot['sections'] ?? [])->first(
            fn (array $section): bool => (int) ($section['position'] ?? -1) === $position,
        );
    }
}
