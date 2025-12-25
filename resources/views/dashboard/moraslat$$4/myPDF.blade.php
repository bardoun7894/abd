<!DOCTYPE html>

<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laravel 9 Generate PDF Example - ItSolutionStuff.com</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

</head>

<body>

    <h1>{{ $title }}</h1>

    <p>{{ $date }}</p>

    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod

    tempor incididunt ut labore et dolore magna aliqua.</p>



    <table class="table table-bordered">

        <tr>

            <th>ID</th>

            <th>Name</th>

            <th>Email</th>

        </tr>

        @foreach($worker as $user)

        <tr>

            <td>{{ $user->worker_id }}</td>

            <td>{{ $user->worker_name }}</td>

            <td>{{ $user->worker_name }}</td>

        </tr>

        @endforeach

    </table>



</body>

</html>
