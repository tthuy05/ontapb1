<?php

namespace App\Console\Commands;

use App\Services\ContentQualityReport;
use Illuminate\Console\Command;

class ContentQualityCommand extends Command
{
    protected $signature = 'content:validate {--json : Emit machine-readable JSON}';

    protected $description = 'Validate study content relationships, metadata, and active-state rules';

    public function handle(ContentQualityReport $qualityReport): int
    {
        $report = $qualityReport->run();

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(
                ['Table', 'Records'],
                collect($report['counts'])->map(fn (int $count, string $table): array => [$table, $count])->values()->all(),
            );
            $this->line(sprintf('Content QA: %d records, %d issues.', $report['record_count'], $report['issue_count']));
            foreach ($report['issues'] as $issue) {
                $this->error(sprintf('%s%s: %s', $issue['table'], $issue['id'] === null ? '' : '#'.$issue['id'], $issue['message']));
            }
        }

        return $report['issue_count'] === 0 ? self::SUCCESS : self::FAILURE;
    }
}
