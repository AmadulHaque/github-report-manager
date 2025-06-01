@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            Weekly Report for {{ $report->repository }} (Week of {{ $report->week }})
        </div>
        <div class="card-body">
            <div class="mb-3">
                <strong>Total Commits:</strong> {{ $report->commit_count }}
            </div>
            <div class="border p-3 bg-light">
                {!! nl2br(e($report->report_content)) !!}
            </div>
            <a href="{{ route('home') }}" class="btn btn-secondary mt-3">Back to Reports</a>
        </div>
    </div>
@endsection
