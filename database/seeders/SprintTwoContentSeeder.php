<?php

namespace Database\Seeders;

use App\Models\ListeningContent;
use App\Models\Passage;
use App\Models\Question;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class SprintTwoContentSeeder extends Seeder
{
    public function run(): void
    {
        $readingTopic = Topic::query()->updateOrCreate(
            ['slug' => 'reading-daily-plans'],
            [
                'area' => 'reading',
                'name' => 'Reading: daily plans',
                'description' => 'Original short texts for finding practical details and main ideas.',
                'position' => 10,
                'priority' => 1,
                'status' => 'active',
            ],
        );

        $listeningTopic = Topic::query()->updateOrCreate(
            ['slug' => 'listening-study-routines'],
            [
                'area' => 'listening',
                'name' => 'Listening: study routines',
                'description' => 'Original short scripts for gist, detail, and next-action listening.',
                'position' => 10,
                'priority' => 1,
                'status' => 'active',
            ],
        );

        $passage = Passage::query()->updateOrCreate(
            ['title' => 'A small weekly study plan'],
            [
                'topic_id' => $readingTopic->id,
                'body' => 'Mai keeps a simple study plan on her desk. On Monday and Wednesday evenings, she reviews vocabulary for twenty minutes. On Saturday morning, she reads one short article and writes three sentences about it. She changes the plan when work becomes busy, but she always keeps one small task for the next day.',
                'word_count' => 53,
                'cefr_level' => 'B1',
                'difficulty' => 1,
                'source_type' => 'original',
                'source_notes' => 'Original synthetic Sprint 2 pilot passage.',
                'status' => 'active',
            ],
        );

        $listening = ListeningContent::query()->updateOrCreate(
            ['title' => 'Planning a short review session'],
            [
                'topic_id' => $listeningTopic->id,
                'transcript' => 'I have a busy afternoon, so I will review my new words before dinner. I only need fifteen minutes. After that, I will write down one question for tomorrow’s lesson.',
                'audio_path' => 'audio/listening/planning-review-session.mp3',
                'audio_mime' => 'audio/mpeg',
                'audio_size_bytes' => 1,
                'duration_seconds' => 25,
                'speaker_count' => 1,
                'accent_notes' => 'Clear, natural B1-paced speech; original script placeholder for the reviewed pilot recording.',
                'cefr_level' => 'B1',
                'difficulty' => 1,
                'source_type' => 'original',
                'source_notes' => 'Original synthetic Sprint 2 pilot script; audio asset is added only after recording/licence review.',
                'status' => 'draft',
            ],
        );

        $readingQuestion = Question::query()->updateOrCreate(
            ['prompt' => 'When does Mai read a short article?'],
            [
                'topic_id' => $readingTopic->id,
                'passage_id' => $passage->id,
                'listening_content_id' => null,
                'skill' => 'reading',
                'type' => 'single_choice',
                'explanation' => 'The passage says that Mai reads one short article on Saturday morning.',
                'cefr_level' => 'B1',
                'difficulty' => 1,
                'source_type' => 'original',
                'source_notes' => 'Original synthetic Sprint 2 pilot question.',
                'status' => 'active',
            ],
        );
        $readingQuestion->options()->delete();
        $readingQuestion->options()->createMany([
            ['option_key' => 'A', 'content' => 'Monday evening', 'is_correct' => false, 'position' => 0],
            ['option_key' => 'B', 'content' => 'Saturday morning', 'is_correct' => true, 'position' => 1],
            ['option_key' => 'C', 'content' => 'Every afternoon', 'is_correct' => false, 'position' => 2],
        ]);

        $listeningQuestion = Question::query()->updateOrCreate(
            ['prompt' => 'How long will the speaker review new words?'],
            [
                'topic_id' => $listeningTopic->id,
                'passage_id' => null,
                'listening_content_id' => $listening->id,
                'skill' => 'listening',
                'type' => 'true_false',
                'explanation' => 'The speaker says, “I only need fifteen minutes.”',
                'cefr_level' => 'B1',
                'difficulty' => 1,
                'source_type' => 'original',
                'source_notes' => 'Original synthetic Sprint 2 pilot question.',
                'status' => 'draft',
            ],
        );
        $listeningQuestion->options()->delete();
        $listeningQuestion->options()->createMany([
            ['option_key' => 'true', 'content' => 'Fifteen minutes', 'is_correct' => true, 'position' => 0],
            ['option_key' => 'false', 'content' => 'One hour', 'is_correct' => false, 'position' => 1],
        ]);
    }
}
