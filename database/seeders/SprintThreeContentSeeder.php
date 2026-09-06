<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\Question;
use Illuminate\Database\Seeder;

class SprintThreeContentSeeder extends Seeder
{
    public function run(): void
    {
        $question = Question::query()->where('prompt', 'When does Mai read a short article?')->firstOrFail();

        $exercise = Exercise::query()->updateOrCreate(
            ['title' => 'Weekly study details practice'],
            [
                'topic_id' => $question->topic_id,
                'skill' => 'reading',
                'instructions' => 'Read the short passage, choose the best answer, and submit when you are ready to review your result.',
                'difficulty' => 1,
                'time_limit_seconds' => 300,
                'status' => 'active',
            ],
        );

        $exercise->exerciseQuestions()->updateOrCreate(
            ['question_id' => $question->id],
            ['position' => 0, 'points' => 1.00],
        );
    }
}
