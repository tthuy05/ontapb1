<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Passage extends Model
{
    use HasFactory;

    public const SOURCE_TYPES = Vocabulary::SOURCE_TYPES;

    public const STATUSES = Topic::STATUSES;

    public const DIFFICULTIES = [1, 2, 3];

    protected $fillable = [
        'topic_id', 'title', 'body', 'word_count', 'cefr_level', 'difficulty',
        'source_type', 'source_reference', 'license_name', 'license_url', 'source_notes', 'status',
    ];

    protected function casts(): array
    {
        return ['word_count' => 'integer', 'difficulty' => 'integer'];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
