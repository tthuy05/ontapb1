<?php

namespace Database\Factories;

use App\Models\Topic;
use App\Models\Vocabulary;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Vocabulary> */
class VocabularyFactory extends Factory
{
    protected $model = Vocabulary::class;

    public function definition(): array
    {
        return [
            'topic_id' => Topic::factory(),
            'term' => 'synthetic-'.Str::lower(Str::random(10)),
            'part_of_speech' => 'noun',
            'phonetic' => null,
            'definition' => 'A clearly synthetic definition for an automated test.',
            'translation' => null,
            'example_sentence' => 'This is a clearly synthetic example sentence.',
            'notes' => null,
            'pronunciation_audio_path' => null,
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
