@extends('layouts.app')

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            Generate New Report
        </div>
        <div class="card-body">
            <form action="{{ route('reports.generate') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="repository" class="form-label">GitHub Repository (owner/repo)</label>
                    <input type="text" class="form-control" id="repository" name="repository"
                           placeholder="laravel/laravel" required>
                </div>
                <button type="submit" class="btn btn-primary">Generate Weekly Report</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Previous Reports
        </div>
        <div class="card-body">
            @if($reports->isEmpty())
                <p>No reports generated yet.</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Repository</th>
                            <th>Week</th>
                            <th>Commits</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                            <tr>
                                <td>{{ $report->repository }}</td>
                                <td>{{ $report->week }}</td>
                                <td>{{ $report->commit_count }}</td>
                                <td>
                                    <a href="{{ route('reports.show', $report) }}" class="btn btn-sm btn-primary">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $reports->links() }}
            @endif
        </div>
    </div>
@endsection
