<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSectionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_section_id', 'question_id', 'writing_prompt_id', 'speaking_prompt_id', 'position', 'points',
    ];

    protected function casts(): array
    {
        return ['position' => 'integer', 'points' => 'decimal:2'];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ExamSection::class, 'exam_section_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function writingPrompt(): BelongsTo
    {
        return $this->belongsTo(WritingPrompt::class);
    }

    public function speakingPrompt(): BelongsTo
    {
        return $this->belongsTo(SpeakingPrompt::class);
    }
}
