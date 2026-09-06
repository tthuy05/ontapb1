<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attempt extends Model
{
    use HasFactory;

    public const STATUSES = ['in_progress', 'submitted', 'abandoned'];

    public const COMPLETION_REASONS = ['manual', 'deadline'];

    protected $fillable = [
        'exercise_id', 'exam_id', 'status', 'completion_reason', 'started_at', 'expires_at', 'submitted_at',
        'score_awarded', 'max_score', 'percentage', 'correct_count', 'incorrect_count', 'unanswered_count',
        'ungraded_count', 'duration_seconds', 'configuration_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'submitted_at' => 'datetime',
            'score_awarded' => 'decimal:2',
            'max_score' => 'decimal:2',
            'percentage' => 'decimal:2',
            'correct_count' => 'integer',
            'incorrect_count' => 'integer',
            'unanswered_count' => 'integer',
            'ungraded_count' => 'integer',
            'duration_seconds' => 'integer',
            'configuration_snapshot' => 'array',
        ];
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class)->orderBy('section_position')->orderBy('question_position');
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('status', 'submitted');
    }

    public function getSnapshotTitleAttribute(): string
    {
        return (string) data_get($this->configuration_snapshot, 'title', $this->exercise?->title ?? $this->exam?->title ?? 'Practice attempt');
    }

    public function getSnapshotSkillAttribute(): ?string
    {
        return data_get($this->configuration_snapshot, 'skill', $this->exercise?->skill ?? ($this->exam_id !== null ? 'mixed' : null));
    }
}
