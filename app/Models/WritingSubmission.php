<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WritingSubmission extends Model
{
    use HasFactory;

    public const STATUSES = ['draft', 'submitted'];

    protected $fillable = [
        'writing_prompt_id', 'attempt_id', 'exam_section_item_id', 'status', 'response_text',
        'word_count', 'self_check', 'prompt_snapshot', 'save_version', 'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'word_count' => 'integer',
            'self_check' => 'array',
            'prompt_snapshot' => 'array',
            'save_version' => 'integer',
            'submitted_at' => 'datetime',
        ];
    }

    public static function countWords(?string $text): int
    {
        $text = trim((string) $text);
        if ($text === '' || ! preg_match_all('/\S+/u', $text, $matches)) {
            return 0;
        }

        return count($matches[0] ?? []);
    }

    public function writingPrompt(): BelongsTo
    {
        return $this->belongsTo(WritingPrompt::class);
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class);
    }

    public function examSectionItem(): BelongsTo
    {
        return $this->belongsTo(ExamSectionItem::class);
    }
}
