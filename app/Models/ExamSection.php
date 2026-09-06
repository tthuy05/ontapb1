<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSection extends Model
{
    use HasFactory;

    public const NAVIGATION_MODES = ['free_within_section'];

    protected $fillable = [
        'exam_id', 'skill', 'title', 'instructions', 'position', 'time_limit_seconds', 'navigation_mode',
    ];

    protected function casts(): array
    {
        return ['position' => 'integer', 'time_limit_seconds' => 'integer'];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExamSectionItem::class)->orderBy('position');
    }

}
