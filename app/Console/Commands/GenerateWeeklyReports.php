<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReportService;

class GenerateWeeklyReports extends Command
{
    protected $signature = 'reports:generate {repo} {owner}';
    protected $description = 'Generate weekly reports for a GitHub repository';

    public function handle(ReportService $reportService)
    {
        $owner = $this->argument('owner');
        $repo = $this->argument('repo');

        $this->info("Generating weekly report for $owner/$repo...");

        $report = $reportService->generateWeeklyReport($owner, $repo);

        if ($report) {
            $this->info("Report generated successfully!");
            $this->line("Commit count: " . $report->commit_count);
            $this->line("Report content:\n" . $report->report_content);
        } else {
            $this->warn("No commits found for this week.");
        }
    }
}
