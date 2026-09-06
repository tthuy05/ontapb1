<?php

namespace App\Models;

use Database\Factories\GrammarLessonFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrammarLesson extends Model
{
    /** @use HasFactory<GrammarLessonFactory> */
    use HasFactory;

    public const SOURCE_TYPES = Vocabulary::SOURCE_TYPES;

    public const STATUSES = Topic::STATUSES;

    protected $fillable = [
        'topic_id',
        'title',
        'slug',
        'objectives',
        'prerequisites',
        'body',
        'examples',
        'common_mistakes',
        'position',
        'source_type',
        'source_reference',
        'license_name',
        'license_url',
        'source_notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'examples' => 'array',
            'position' => 'integer',
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
