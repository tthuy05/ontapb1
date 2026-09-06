<?php

namespace Database\Factories;

use App\Models\GrammarLesson;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<GrammarLesson> */
class GrammarLessonFactory extends Factory
{
    protected $model = GrammarLesson::class;

    public function definition(): array
    {
        $title = 'Synthetic Grammar '.Str::lower(Str::random(10));

        return [
            'topic_id' => Topic::factory()->grammar(),
            'title' => $title,
            'slug' => Str::slug($title),
            'objectives' => 'Understand a clearly synthetic grammar pattern.',
            'prerequisites' => null,
            'body' => 'This is clearly synthetic grammar content for an automated test.',
            'examples' => [
                [
                    'example' => 'This is a synthetic example.',
                    'explanation' => 'This is a synthetic explanation.',
                ],
            ],
            'common_mistakes' => null,
            'position' => 0,
            'source_type' => 'original',
            'source_reference' => null,
            'license_name' => null,
            'license_url' => null,
            'source_notes' => 'Synthetic test content.',
            'status' => 'draft',
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['status' => 'active']);
    }
}
