<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        * {
            margin: 5px;
            padding: 5px;
        }

        table {
            width: 100%;
            margin: 2px 0 2px 0;
            padding: 2px 0 2px 0;
        }

        .package-table, .package-table th, .package-table td {
            border: 1px solid black;
            border-collapse: collapse;
            text-align: center;
        }

        .in-words-table {
            border-bottom: 1px solid black;
            border-top: 1px solid black;
        }

        p, td, th {
            font-size: 11px;
        }
    </style>
</head>
<body>
@section('pdf-header')
@show

@yield('pdf-content')

@section('pdf-footer')
@show
</body>
</html>
