<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    public const TYPES = ['single_choice', 'true_false'];

    public const SKILLS = ['reading', 'listening'];

    public const SOURCE_TYPES = Vocabulary::SOURCE_TYPES;

    public const STATUSES = Topic::STATUSES;

    public const DIFFICULTIES = [1, 2, 3];

    protected $fillable = [
        'topic_id', 'passage_id', 'listening_content_id', 'skill', 'type', 'prompt', 'explanation',
        'answer_config', 'cefr_level', 'difficulty', 'metadata', 'source_type', 'source_reference',
        'license_name', 'license_url', 'source_notes', 'status',
    ];

    protected function casts(): array
    {
        return ['answer_config' => 'array', 'metadata' => 'array', 'difficulty' => 'integer'];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function passage(): BelongsTo
    {
        return $this->belongsTo(Passage::class);
    }

    public function listeningContent(): BelongsTo
    {
        return $this->belongsTo(ListeningContent::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('position');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
