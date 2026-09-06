<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WritingPrompt extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id', 'task_type', 'title', 'instructions', 'minimum_words', 'recommended_minutes',
        'guidance', 'checklist', 'model_answer', 'source_type', 'source_reference', 'license_name',
        'license_url', 'source_notes', 'status',
    ];

    protected function casts(): array
    {
        return [
            'minimum_words' => 'integer',
            'recommended_minutes' => 'integer',
            'checklist' => 'array',
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function examSectionItems(): HasMany
    {
        return $this->hasMany(ExamSectionItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
