<?php

namespace App\Models;

use Database\Factories\VocabularyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vocabulary extends Model
{
    /** @use HasFactory<VocabularyFactory> */
    use HasFactory;

    public const SOURCE_TYPES = [
        'original',
        'public_domain',
        'open_license',
        'licensed',
        'personal_provided',
    ];

    public const STATUSES = ['draft', 'active', 'inactive'];

    protected $fillable = [
        'topic_id',
        'term',
        'part_of_speech',
        'phonetic',
        'definition',
        'translation',
        'example_sentence',
        'notes',
        'pronunciation_audio_path',
        'source_type',
        'source_reference',
        'license_name',
        'license_url',
        'source_notes',
        'status',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function progress(): HasOne
    {
        return $this->hasOne(VocabularyProgress::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
