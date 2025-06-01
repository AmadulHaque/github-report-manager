<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\GitHubService;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private GitHubService $githubService)
    {
    }

    public function index()
    {
        $reports = Report::latest()->paginate(10);
        return view('reports.index', compact('reports'));
    }

    public function generate(Request $request, ReportService $reportService)
    {
        $data = $request->validate([
            'repository' => 'required|string',
            'author' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        try {
            [$owner, $repo] = explode('/', $data['repository']);
            $data['owner'] = $owner;
            $data['repo'] = $repo;
            $report = $reportService->generateWeeklyReport($data);
            if ($report) {
                return response()->json(['success'=> true, 'message' => 'Weekly report generated successfully!', 'reportId'=>$report->id], 200);
            }
            return response()->json(['error' => 'Failed to generate report'], 500);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function show(Report $report)
    {
        return view('reports.show', compact('report'));
    }

    public function authors(Request $request)
    {
        try {
            $repository = $request->input('repository');
            [$owner, $repo] = explode('/', $repository);
            $authors = $this->githubService->getRepositoryAuthors($owner, $repo);

            return response()->json($authors);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }



}
