<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::latest()->paginate(10);
        return view('reports.index', compact('reports'));
    }

    public function generate(Request $request, ReportService $reportService)
    {
        $request->validate([
            'repository' => 'required|string',
        ]);

        [$owner, $repo] = explode('/', $request->repository);

        $report = $reportService->generateWeeklyReport($owner, $repo);

        if ($report) {
            return redirect()->route('reports.show', $report)
                ->with('success', 'Weekly report generated successfully!');
        }

        return back()->with('error', 'No commits found for this week.');
    }

    public function show(Report $report)
    {
        return view('reports.show', compact('report'));
    }
}
