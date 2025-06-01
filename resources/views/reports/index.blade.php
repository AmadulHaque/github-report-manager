@extends('layouts.app')

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            Generate New Report
        </div>
        <div class="card-body">
            <form id="generate-report-form" action="{{ route('reports.generate') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="mb-3">
                            <label for="repository" class="form-label">GitHub Repository (owner/repo)</label>
                            <input type="text" class="form-control" id="repository" name="repository" placeholder="laravel/laravel" required>
                        </div>
                    </div>
                    <div class="col-md-6 mdb-3">
                        <div class="mb-3">
                            <label for="repository" class="form-label">Owner</label>
                            <select name="author" class="form-select" id="author">
                                <option value="">-- select owner --</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 mdb-3">
                        <div class="mb-3">
                            <label for="repository" class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                    </div>
                     <div class="col-md-6 mdb-3">
                        <div class="mb-3">
                            <label for="repository" class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Generate Weekly Report</button>
            </form>
        </div>
    </div>

    <div id="report-results"></div>

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

    <script>
        $(document).ready(function() {

            $('#generate-report-form').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    method: $(this).attr('method'),
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#report-results').empty();
                        if (response.success) {
                            $('#generate-report-form').trigger('reset');
                            $('#report-results').append('<div class="alert alert-success">' + response.message + '</div>');
                            $('#report-results').append('<div class="alert alert-success">Report ID: <a href="/reports/' + response.reportId + '" class="text-decoration-none">View Report</a></div>');

                        }
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('#report-results').append('<div class="alert alert-danger">' + value + '</div>');
                            });
                        } else {
                            console.log(error);
                        }
                    }
                });
            })

            $('#author').select2({
                templateResult: formatOwner,
                templateSelection: formatOwner,
                escapeMarkup: function(markup) {
                    return markup;
                }
            });

            $('#repository').on('change', function() {
                var repository = $(this).val();
                if (repository) {
                    $('#author').empty();
                    $.ajax({
                        url: "{{ route('authors') }}",
                        type: "GET",
                        data: {
                            repository: repository
                        },
                        success: function(data) {
                            $('#owner').empty();
                            $('#owner').append('<option></option>'); // Add placeholder

                            $.each(data, function(index, owner) {
                                $('#author').append(
                                    '<option value="' + owner.login + '" data-avatar="' + owner.avatar_url + '">' +
                                    owner.login + '</option>'
                                );
                            });

                            // Refresh select2
                            $('#author').trigger('change');
                        }
                    });
                }
            });

            function formatOwner(state) {
                if (!state.id) return state.text;

                var avatar = $(state.element).data('avatar');
                var username = state.text;
                var markup = `
                    <div style="display: flex; align-items: center;">
                        <img src="${avatar}" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 10px;" />
                        <span>${username}</span>
                    </div>
                `;
                return markup;
            }
        });

    </script>
@endsection
