<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamSection;
use App\Models\Question;
use Illuminate\Database\Seeder;

class SprintFiveContentSeeder extends Seeder
{
    public function run(): void
    {
        $question = Question::query()->where('prompt', 'When does Mai read a short article?')->firstOrFail();

        $exam = Exam::query()->updateOrCreate(
            ['title' => 'B1 objective mock pilot'],
            [
                'format_label' => 'vstep_simulation',
                'description' => 'A short configurable Reading simulation built from reviewed objective content.',
                'instructions' => 'Complete the section, save answers as you work, and submit when you are ready to review the result.',
                'time_limit_seconds' => 300,
                'metadata' => ['sprint' => 5, 'pilot' => true],
                'status' => 'active',
            ],
        );

        $exam->sections()->delete();
        $section = $exam->sections()->create([
            'skill' => 'reading',
            'title' => 'Reading practice',
            'instructions' => 'Read the passage and choose the best answer.',
            'position' => 0,
            'time_limit_seconds' => 300,
            'navigation_mode' => ExamSection::NAVIGATION_MODES[0],
        ]);
        $section->items()->create([
            'question_id' => $question->id,
            'position' => 0,
            'points' => 1.00,
        ]);
    }
}
