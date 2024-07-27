<!DOCTYPE html>
<html lang="en">

<head>
    @include('admin.css')
    <style>
        /* Custom styles for the table */
        .table tbody tr:hover {
            cursor: pointer;
        }

        .table tbody td {
            vertical-align: middle;
        }

        .table-bordered {
            border: 1px solid #dee2e6;
        }

        .table td,
        .table th {
            color: #333;
        }

        .page-header {
            padding-top: 20px;
            padding-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h2 {
            font-size: 2rem;
        }

        .challenge-row:hover {
            background-color: inherit !important;
        }

        .btn-not-started {
            background-color: orange;
            color: white;
        }
    </style>

</head>

<body>
    @include('admin.arrange')
    @include('admin.sidebar')
    @include('admin.navbar')
    @php
        use Carbon\Carbon;
    @endphp
 u
            <div class="container mt-5">
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="page-header">
                    <h2>Challenges</h2>
                    <a href="{{ route('addchallenge') }}" class="btn btn-primary">Add Challenge</a>
                </div>

                @if ($challenges->isEmpty())
                    <div class="alert alert-info">
                        No challenges available.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Challenge Number</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Duration (minutes)</th>
                                    <th>Number of Questions</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            @foreach ($challenges as $challenge)
                                <tr data-challenge-id="{{ $challenge->challengeNumber }}" class="challenge">
                                    <td>{{ $challenge->challengeNumber }}</td>
                                    <td>{{ $challenge->start_date }}</td>
                                    <td>{{ $challenge->end_date }}</td>
                                    <td>{{ $challenge->duration }}</td>
                                    <td>{{ $challenge->num_questions }}</td>
                                    <td>
                                        @if (\Carbon\Carbon::now()->lt(\Carbon\Carbon::parse($challenge->start_date)))
                                            <button class="btn btn-not-started">Not Started</button>
                                        @elseif (\Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($challenge->end_date)))
                                            <button class="btn btn-danger">Closed</button>
                                        @else
                                            <button class="btn btn-success">Open</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.challenge');
            rows.forEach(row => {
                row.addEventListener('click', function() {
                    const challengeId = this.getAttribute('data-challenge-id');
                    window.location.href = `/challenge/${challengeId}/questions`;
                });
            });
        });
    </script>
    @include('admin.script')
</body>

</html>
