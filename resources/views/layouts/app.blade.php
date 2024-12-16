<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'MVC FRAMRWORK'))</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <style>
        .notice {
            padding: 15px;
            background-color: #fafafa;
            border-left: 6px solid #7f7f84;
            margin-bottom: 10px;
            -webkit-box-shadow: 0 5px 8px -6px rgba(0, 0, 0, .2);
            -moz-box-shadow: 0 5px 8px -6px rgba(0, 0, 0, .2);
            box-shadow: 0 5px 8px -6px rgba(0, 0, 0, .2);
        }

        .notice-sm {
            padding: 10px;
            font-size: 80%;
        }

        .notice-lg {
            padding: 35px;
            font-size: large;
        }

        .notice-success {
            border-color: #80D651;
        }

        .notice-success>strong {
            color: #80D651;
        }

        .notice-info {
            border-color: #45ABCD;
        }

        .notice-info>strong {
            color: #45ABCD;
        }

        .notice-warning {
            border-color: #FEAF20;
        }

        .notice-warning>strong {
            color: #FEAF20;
        }

        .notice-danger {
            border-color: #d73814;
        }

        .notice-danger>strong {
            color: #d73814;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">{{ config('app.name', 'MVC FRAMRWORK') }}</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('register') }}" class="btn btn-primary">Explore</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <div class="container mt-5 py-5">
        @yield('content')
    </div>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
