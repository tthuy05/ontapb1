<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpeakingPrompt extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id', 'part_type', 'title', 'instructions', 'preparation_seconds', 'speaking_seconds',
        'suggested_ideas', 'follow_up_questions', 'checklist', 'source_type', 'source_reference',
        'license_name', 'license_url', 'source_notes', 'status',
    ];

    protected function casts(): array
    {
        return [
            'preparation_seconds' => 'integer',
            'speaking_seconds' => 'integer',
            'suggested_ideas' => 'array',
            'follow_up_questions' => 'array',
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
