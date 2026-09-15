<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VocabularyReviewSchedule extends Model
{
    public const RATINGS = ['again', 'hard', 'good', 'easy'];

    protected $fillable = [
        'vocabulary_progress_id',
        'due_at',
        'interval_days',
        'streak',
        'lapses',
        'last_rating',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'interval_days' => 'integer',
            'streak' => 'integer',
            'lapses' => 'integer',
        ];
    }

    public function progress(): BelongsTo
    {
        return $this->belongsTo(VocabularyProgress::class, 'vocabulary_progress_id');
    }
}
