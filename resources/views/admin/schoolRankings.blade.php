<!-- resources/views/admin/schoolRankings.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <title>School Rankings</title>
    @include('admin.css')
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .table thead th {
            background-color: #343a40;
            color: white;
            font-weight: bold;
        }

        .table tbody td {
            background-color: white;
            color: black;
        }

        .container-fluid.page-body-wrapper {
            padding-top: 50px;
        }

        h1.text-center {
            color: #343a40;
            font-size: 2.5em;
            font-weight: bold;
        }

        .table {
            border: 1px solid #343a40;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #343a40;
        }
    </style>
</head>
<body>
    @include('admin.arrange')
    @include('admin.sidebar')
    @include('admin.navbar')

    <div class="container-fluid page-body-wrapper">
        <div class="container mt-5" style="color: black;" align="center">
            <h1 class="text-center mb-4">School Rankings</h1>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>School Name</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @foreach($schools as $school)
                        <tr>
                            <td>{{ $school['rank'] }}</td>
                            <td>{{ $school['school_name'] }}</td>
                            
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    @include('admin.script')
</body>
</html>
