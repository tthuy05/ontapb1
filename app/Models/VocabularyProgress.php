<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VocabularyProgress extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    public const STATES = ['new', 'learning', 'learned', 'review'];

    protected $table = 'vocabulary_progress';

    protected $fillable = [
        'vocabulary_id',
        'state',
        'correct_count',
        'incorrect_count',
        'last_reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'correct_count' => 'integer',
            'incorrect_count' => 'integer',
            'last_reviewed_at' => 'datetime',
        ];
    }

    public function vocabulary(): BelongsTo
    {
        return $this->belongsTo(Vocabulary::class);
    }

    public function reviewSchedule(): HasOne
    {
        return $this->hasOne(VocabularyReviewSchedule::class);
    }
}
