<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    public const FORMAT_LABELS = ['vstep_simulation', 'general_b1'];

    public const STATUSES = Topic::STATUSES;

    protected $fillable = [
        'title', 'format_label', 'description', 'instructions', 'time_limit_seconds', 'metadata', 'status',
    ];

    protected function casts(): array
    {
        return ['time_limit_seconds' => 'integer', 'metadata' => 'array'];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ExamSection::class)->orderBy('position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
