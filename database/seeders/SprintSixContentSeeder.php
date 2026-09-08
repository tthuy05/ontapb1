<?php

namespace Database\Seeders;

use App\Models\WritingPrompt;
use Illuminate\Database\Seeder;

class SprintSixContentSeeder extends Seeder
{
    public function run(): void
    {
        WritingPrompt::query()->updateOrCreate(['title' => 'Write to a study-group organizer'], [
            'topic_id' => null, 'task_type' => 'task_1',
            'instructions' => 'Write an email of at least 120 words to a study-group organizer. Ask about the next meeting, explain one topic you want to practise, and suggest a useful activity.',
            'minimum_words' => 120, 'recommended_minutes' => 20,
            'guidance' => 'Plan the purpose, audience, three required points, and a polite closing before drafting.',
            'checklist' => ['I answered every part of the task.', 'My tone suits an email to a group organizer.', 'My ideas follow a clear order.', 'I checked useful vocabulary and grammar.', 'I checked spelling, punctuation, and word count.'],
            'model_answer' => null, 'source_type' => 'original', 'source_reference' => null,
            'license_name' => null, 'license_url' => null, 'source_notes' => 'Original synthetic Sprint 6 pilot prompt.', 'status' => 'active',
        ]);

        WritingPrompt::query()->updateOrCreate(['title' => 'Balancing study and free time'], [
            'topic_id' => null, 'task_type' => 'task_2',
            'instructions' => 'Write an essay of at least 250 words about this question: Is it better for students to plan their free time carefully or to be spontaneous? Give reasons and examples.',
            'minimum_words' => 250, 'recommended_minutes' => 40,
            'guidance' => 'Choose a position, outline two reasons, add an example, and reserve time to revise links and accuracy.',
            'checklist' => ['My position is clear.', 'Each paragraph has a focused idea.', 'I used linking expressions accurately.', 'I used varied vocabulary and grammar.', 'I checked mechanics and word count.'],
            'model_answer' => null, 'source_type' => 'original', 'source_reference' => null,
            'license_name' => null, 'license_url' => null, 'source_notes' => 'Original synthetic Sprint 6 pilot prompt.', 'status' => 'active',
        ]);
    }
}
