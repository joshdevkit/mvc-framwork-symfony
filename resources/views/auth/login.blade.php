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
                        {{-- @if (session('errors'))
                            @foreach (session('errors') as $messages)
                                @foreach ($messages as $key => $message)
                                    <div class="notice notice-danger mb-2 p-2 ">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @endforeach
                            @endforeach
                        @endif --}}
                        <form method="POST" action="{{ route('authenticate') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    placeholder="Enter your email">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Enter your password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mt-3">Sign In</button>
                        </form>

                        <div class="mt-3 text-center">
                            <p>Don't have an account? <a href="{{ route('register') }}">Sign Up</a></p>
                        </div>
                        <div class="text-center">
                            <p>Forgot Password ? <a href="{{ route('recover') }}">Click here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
