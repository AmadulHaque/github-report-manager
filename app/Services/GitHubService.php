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

    public function getRepositoryCommits($data)
    {
        try {
            $url = "/repos/{$data['owner']}/{$data['repo']}/commits";

            $query = [];
            if ($data['start_date']) {
                $query['since'] = $data['start_date'];
            }
            if ($data['end_date']) {
                $query['until'] = $data['end_date'];
            }
            if ($data['author']) {
                $query['author'] = $data['author'];
            }

            $response = $this->client->get($url, ['query' => $query]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error("GitHub API Error: " . $e->getMessage());
            return [];
        }
    }


    public function getRepositoryAuthors($owner, $repo)
    {
        try {
            $url = "/repos/{$owner}/{$repo}/contributors";
            $response = $this->client->get($url);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error("GitHub API Error: " . $e->getMessage());
            return [];
        }
    }

    public function getWeeklyCommits($data)
    {
        return $this->getRepositoryCommits($data);
    }
}
