<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpeakingSubmission extends Model
{
    use HasFactory;

    public const STATUSES = ['draft', 'submitted'];

    protected $fillable = [
        'speaking_prompt_id', 'attempt_id', 'exam_section_item_id', 'status', 'prompt_snapshot',
        'duration_seconds', 'self_assessment', 'notes', 'save_version', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'prompt_snapshot' => 'array',
            'self_assessment' => 'array',
            'save_version' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function speakingPrompt(): BelongsTo
    {
        return $this->belongsTo(SpeakingPrompt::class);
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class);
    }

    public function examSectionItem(): BelongsTo
    {
        return $this->belongsTo(ExamSectionItem::class);
    }
}
