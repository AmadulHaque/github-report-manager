<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class GitHubService
{
    protected $client;
    protected $token;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => 'token ' . env('GITHUB_API_TOKEN'),
            ]
        ]);
    }

    public function getRepositoryCommits($owner, $repo, $since = null)
    {
        try {
            $url = "/repos/{$owner}/{$repo}/commits";

            $query = [];
            if ($since) {
                $query['since'] = $since;
            }

            $response = $this->client->get($url, ['query' => $query]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error("GitHub API Error: " . $e->getMessage());
            return [];
        }
    }

    public function getWeeklyCommits($owner, $repo)
    {
        $oneWeekAgo = now()->subWeek()->toIso8601String();
        return $this->getRepositoryCommits($owner, $repo, $oneWeekAgo);
    }
}
