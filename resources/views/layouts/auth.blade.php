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
                        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <div class="dropdown">
                                <a class="btn dropdown-toggle" href="#" role="button" data-toggle="dropdown"
                                    aria-expanded="false">
                                    Welcome, {{ auth()->user()->name }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{ route('profile') }}">Account Settings</a>
                                    <a class="dropdown-item" href="#">Change Password</a>
                                    <a class="dropdown-item"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                        href="{{ route('logout') }}">{{ 'Logout' }}</a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endauth
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
