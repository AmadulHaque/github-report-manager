<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Support\Facades\Http;
use OpenAI\Laravel\Facades\OpenAI;

class ReportService
{
    protected $githubService;

    public function __construct(GitHubService $githubService)
    {
        $this->githubService = $githubService;
    }

    public function generateWeeklyReport($data)
    {
       try {
            $commits = $this->githubService->getWeeklyCommits($data);
            $commitCount = count($commits);

            if ($commitCount === 0) {
                return null;
            }

            $reportContent = $this->generateHumanLikeReport($data, $commits);

            return Report::create([
                'repository'        => "{$data['owner']}/{$data['repo']}",
                'week'              => now()->startOfWeek()->format('Y-m-d'),
                'commit_count'      => $commitCount,
                'report_content'    => $reportContent,
                'author'            => $data['author'],
                'start_date'        => $data['start_date'],
                'end_date'          => $data['end_date'],
            ]);
       } catch (\Throwable $th) {
            throw $th;
       }
    }


    protected function generateHumanLikeReport($data ,$commits)
    {
        // Filter out merge commits
        $filteredCommits = array_filter($commits, function ($commit) {
            return !isset($commit['commit']['message']) ||
                !str_starts_with($commit['commit']['message'], 'Merge');
        });

        if (count($filteredCommits) === 0) {
            return "No non-merge commits found this week.";
        }

        $commitMessages = array_map(function ($commit) {
            return $commit['commit']['message'];
        }, $filteredCommits);

        $prompt = "Write a concise, human-like weekly development report for {$data['owner']}/{$data['repo']}. ";
        $prompt .= "\nPeriod: {$data['start_date']} to {$data['end_date']}";
        if ($data['author']) {
        }
        $prompt .= "\nTotal commits: " . count($commits) . "\n";
        $prompt .= "Commit messages:\n" . implode("\n", $commitMessages) . "\n\n";
        $prompt .= "Highlight key activities and changes in professional but conversational tone.";
        $prompt .= count($filteredCommits) . " non-merge commits this week. ";
        $prompt .= "Commit messages:\n" . implode("\n", $commitMessages) . "\n\n";
        $prompt .= "Highlight key activities, progress, and notable changes. ";
        $prompt .= "Professional but conversational tone.";

        try {
            $apiKey = env('GEMINI_API_KEY');
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$apiKey}";

            $response = Http::post($url, [
                "contents" => [[
                    "parts" => [["text" => $prompt]]
                ]],
                "generationConfig" => [
                    "temperature" => 0.7,
                    "topP" => 0.8,
                    "topK" => 40,
                    "maxOutputTokens" => 2048
                ]
            ]);

            if ($response->successful()) {
                $content = $response->json();
                return $content['candidates'][0]['content']['parts'][0]['text'] ??
                    $this->generateSimpleReport($data['owner'], $data['repo'], $filteredCommits);
            }

            throw new \Exception('API request failed');
        } catch (\Exception $e) {
            \Log::error('Gemini API Error: ' . $e->getMessage());
            return $this->generateSimpleReport($data['owner'], $data['repo'],  $filteredCommits);
        }
    }

    protected function generateSimpleReport($owner, $repo, $commits)
    {
        $report = "## Weekly Report for $owner/$repo\n";
        $report .= "**Week of:** " . now()->startOfWeek()->format('Y-m-d') . "\n";
        $report .= "**Non-merge commits:** " . count($commits) . "\n\n";
        $report .= "### Commit Summary:\n";

        foreach ($commits as $commit) {
            $msg = substr(trim($commit['commit']['message']), 0, 100);
            $report .= "- {$msg}\n";
        }

        return $report;
    }






}
