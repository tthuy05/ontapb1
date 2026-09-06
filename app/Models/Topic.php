<?php

namespace App\Models;

use Database\Factories\TopicFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    /** @use HasFactory<TopicFactory> */
    use HasFactory;

    public const AREAS = [
        'vocabulary',
        'grammar',
        'reading',
        'listening',
        'writing',
        'speaking',
        'general',
    ];

    public const STATUSES = ['draft', 'active', 'inactive'];

    protected $fillable = [
        'area',
        'name',
        'slug',
        'description',
        'position',
        'priority',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'priority' => 'integer',
        ];
    }

    public function vocabularies(): HasMany
    {
        return $this->hasMany(Vocabulary::class);
    }

    public function grammarLessons(): HasMany
    {
        return $this->hasMany(GrammarLesson::class);
    }

    public function passages(): HasMany
    {
        return $this->hasMany(Passage::class);
    }

    public function listeningContents(): HasMany
    {
        return $this->hasMany(ListeningContent::class);
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
