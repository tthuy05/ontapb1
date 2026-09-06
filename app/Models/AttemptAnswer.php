<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttemptAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id', 'question_id', 'section_position', 'question_position', 'question_snapshot',
        'context_snapshot', 'response', 'answered_at', 'is_correct', 'points_awarded', 'max_points', 'save_version',
    ];

    protected function casts(): array
    {
        return [
            'section_position' => 'integer',
            'question_position' => 'integer',
            'question_snapshot' => 'array',
            'context_snapshot' => 'array',
            'response' => 'array',
            'answered_at' => 'datetime',
            'is_correct' => 'boolean',
            'points_awarded' => 'decimal:2',
            'max_points' => 'decimal:2',
            'save_version' => 'integer',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
