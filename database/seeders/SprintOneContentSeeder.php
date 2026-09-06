<?php

namespace Database\Seeders;

use App\Models\GrammarLesson;
use App\Models\Topic;
use App\Models\Vocabulary;
use Illuminate\Database\Seeder;

class SprintOneContentSeeder extends Seeder
{
    public function run(): void
    {
        $dailyLife = Topic::query()->updateOrCreate(
            ['slug' => 'home-daily-life-time'],
            [
                'area' => 'vocabulary',
                'name' => 'Home, daily life and time',
                'description' => 'Original starter vocabulary for routines, schedules, and everyday responsibilities.',
                'position' => 10,
                'priority' => 1,
                'status' => 'active',
            ],
        );

        $education = Topic::query()->updateOrCreate(
            ['slug' => 'education-study'],
            [
                'area' => 'vocabulary',
                'name' => 'Education and study',
                'description' => 'Original starter vocabulary for study habits, courses, and learning progress.',
                'position' => 20,
                'priority' => 1,
                'status' => 'active',
            ],
        );

        $coreGrammar = Topic::query()->updateOrCreate(
            ['slug' => 'core-tenses'],
            [
                'area' => 'grammar',
                'name' => 'Core tenses',
                'description' => 'Original reference lessons for common B1 tense choices.',
                'position' => 10,
                'priority' => 1,
                'status' => 'active',
            ],
        );

        Vocabulary::query()->updateOrCreate(
            ['topic_id' => $dailyLife->id, 'term' => 'keep track of', 'part_of_speech' => 'phrase'],
            [
                'definition' => 'To continue to know what is happening with something.',
                'translation' => 'theo dõi',
                'example_sentence' => 'I use a weekly plan to keep track of my study tasks.',
                'notes' => 'Often followed by a noun such as time, progress, or expenses.',
                'source_type' => 'original',
                'source_notes' => 'Original Sprint 1 pilot content.',
                'status' => 'active',
            ],
        );

        Vocabulary::query()->updateOrCreate(
            ['topic_id' => $education->id, 'term' => 'make progress', 'part_of_speech' => 'collocation'],
            [
                'definition' => 'To improve or move closer to a goal.',
                'translation' => 'tiến bộ',
                'example_sentence' => 'Regular review helps me make progress in English.',
                'notes' => 'Use make, not do, with progress.',
                'source_type' => 'original',
                'source_notes' => 'Original Sprint 1 pilot content.',
                'status' => 'active',
            ],
        );

        Vocabulary::query()->updateOrCreate(
            ['topic_id' => $education->id, 'term' => 'deadline', 'part_of_speech' => 'noun'],
            [
                'phonetic' => '/ˈdedlaɪn/',
                'definition' => 'The latest time or date by which something must be completed.',
                'translation' => 'hạn chót',
                'example_sentence' => 'The deadline for the assignment is Friday afternoon.',
                'source_type' => 'original',
                'source_notes' => 'Original Sprint 1 pilot content.',
                'status' => 'active',
            ],
        );

        GrammarLesson::query()->updateOrCreate(
            ['slug' => 'present-simple-and-present-continuous'],
            [
                'topic_id' => $coreGrammar->id,
                'title' => 'Present simple and present continuous',
                'objectives' => 'Choose between routines or facts and actions happening around now.',
                'prerequisites' => 'Basic subject–verb agreement and the verb be.',
                'body' => "Use the present simple for routines, repeated actions, and facts.\n\nUse the present continuous for actions happening now or temporary situations around the present time.",
                'examples' => [
                    [
                        'example' => 'I study vocabulary every evening.',
                        'explanation' => 'Every evening signals a repeated routine.',
                    ],
                    [
                        'example' => 'I am preparing for an exam this month.',
                        'explanation' => 'This month describes a temporary situation around now.',
                    ],
                ],
                'common_mistakes' => 'Avoid the continuous form with many state verbs: write “I understand,” not “I am understanding.”',
                'position' => 10,
                'source_type' => 'original',
                'source_notes' => 'Original Sprint 1 pilot content.',
                'status' => 'active',
            ],
        );
    }
}
