<?php

namespace App\Console\Commands;

use App\Services\ContentQualityReport;
use Illuminate\Console\Command;

class ContentInventoryCommand extends Command
{
    protected $signature = 'content:report {--json : Emit machine-readable JSON}';

    protected $description = 'Report content balance, provenance status, and audio budget';

    public function handle(ContentQualityReport $qualityReport): int
    {
        $report = $qualityReport->inventory();

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->table(
            ['Area', 'Topics'],
            collect($report['topics_by_area'])->map(fn (int $count, string $area): array => [$area, $count])->values()->all(),
        );
        $this->table(
            ['Question balance', 'Records'],
            collect($report['active_questions_by_type_and_difficulty'])->map(fn (int $count, string $label): array => [$label, $count])->values()->all(),
        );
        $this->line(sprintf(
            'Audio budget: %d pieces, %d active, %d pending recordings, %d declared bytes.',
            $report['audio_budget']['pieces'],
            $report['audio_budget']['active_pieces'],
            $report['audio_budget']['pending_recordings'],
            $report['audio_budget']['declared_bytes'],
        ));

        return self::SUCCESS;
    }
}
