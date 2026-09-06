<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    use HasFactory;

    public const SKILLS = Question::SKILLS;

    public const STATUSES = Topic::STATUSES;

    public const DIFFICULTIES = [1, 2, 3];

    protected $fillable = [
        'topic_id', 'title', 'skill', 'instructions', 'difficulty', 'time_limit_seconds', 'metadata', 'status',
    ];

    protected function casts(): array
    {
        return [
            'difficulty' => 'integer',
            'time_limit_seconds' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function exerciseQuestions(): HasMany
    {
        return $this->hasMany(ExerciseQuestion::class)->orderBy('position');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exercise_questions')
            ->withPivot(['id', 'position', 'points'])
            ->orderBy('exercise_questions.position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
