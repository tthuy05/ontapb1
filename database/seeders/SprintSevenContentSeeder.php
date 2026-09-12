<?php

namespace Database\Seeders;

use App\Models\SpeakingPrompt;
use Illuminate\Database\Seeder;

class SprintSevenContentSeeder extends Seeder
{
    public function run(): void
    {
        SpeakingPrompt::query()->updateOrCreate(['title' => 'Describe your daily study routine'], [
            'topic_id' => null,
            'part_type' => 'social_interaction',
            'instructions' => 'Talk about how you usually organize a day of English study. Include when you study, what you practise, and one part you enjoy.',
            'preparation_seconds' => 5,
            'speaking_seconds' => 45,
            'suggested_ideas' => ['time of day', 'practice activities', 'a useful habit'],
            'follow_up_questions' => ['What makes this routine useful?', 'What would you like to change?'],
            'checklist' => ['I answered all parts of the prompt.', 'I used a clear beginning, middle, and ending.', 'I gave at least one specific example.', 'I noticed one pronunciation or fluency target.'],
            'source_type' => 'original',
            'source_reference' => null,
            'license_name' => null,
            'license_url' => null,
            'source_notes' => 'Original synthetic Sprint 7 pilot prompt.',
            'status' => 'active',
        ]);

        SpeakingPrompt::query()->updateOrCreate(['title' => 'Choose a useful study activity'], [
            'topic_id' => null,
            'part_type' => 'solution_discussion',
            'instructions' => 'Imagine that a friend wants to improve English but has only twenty minutes each day. Recommend one study activity and explain why it would help.',
            'preparation_seconds' => 60,
            'speaking_seconds' => 120,
            'suggested_ideas' => ['the activity', 'how to do it', 'benefits and limitations'],
            'follow_up_questions' => ['Would this activity suit every learner?', 'How could the learner measure progress?'],
            'checklist' => ['I made a clear recommendation.', 'I explained two reasons.', 'I used an example or comparison.', 'I spoke continuously and linked my ideas.'],
            'source_type' => 'original',
            'source_reference' => null,
            'license_name' => null,
            'license_url' => null,
            'source_notes' => 'Original synthetic Sprint 7 pilot prompt.',
            'status' => 'active',
        ]);

        SpeakingPrompt::query()->updateOrCreate(['title' => 'Talk about a helpful learning habit'], [
            'topic_id' => null,
            'part_type' => 'topic_development',
            'instructions' => 'Describe a learning habit that has helped you or someone you know. Explain how it started, what happened over time, and whether you would recommend it.',
            'preparation_seconds' => 60,
            'speaking_seconds' => 120,
            'suggested_ideas' => ['the habit', 'a change over time', 'a personal example'],
            'follow_up_questions' => ['Why do some habits last longer than others?', 'What advice would you give a beginner?'],
            'checklist' => ['I developed the topic with a sequence of ideas.', 'I used past and present time references accurately.', 'I supported my view with detail.', 'I chose one target improvement for a second attempt.'],
            'source_type' => 'original',
            'source_reference' => null,
            'license_name' => null,
            'license_url' => null,
            'source_notes' => 'Original synthetic Sprint 7 pilot prompt.',
            'status' => 'active',
        ]);
    }
}
