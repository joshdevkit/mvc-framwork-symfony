@extends('layouts.app')

@section('title', 'Sign In')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-sm-8 col-sm-8">
                <div class="card shadow rounded p-4">
                    <div class="card-header text-center">
                        <h2><i class="bi bi-box-arrow-in-right"></i> Sign In</h2>
                    </div>

                    <div class="card-body">
                        @if (session('errors'))
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0">
                                    @foreach (session('errors') as $messages)
                                        @foreach ($messages as $message)
                                            <li>{{ $message }}</li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form method="POST" action="{{ url('signin') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                    class="form-control" placeholder="Enter your email">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password" class="form-control"
                                    placeholder="Enter your password">
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mt-3">Sign In</button>
                        </form>

                        <div class="mt-3 text-center">
                            <p>Don't have an account? <a href="{{ url('signup') }}">Sign Up</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
