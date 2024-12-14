@extends('layouts.app')

@section('title', 'Sign Up')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow rounded p-4">
                    <div class="card-header text-center">
                        <h2><i class="bi bi-person-plus"></i> Sign Up</h2>
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
                        <form method="POST" action="{{ url('signup') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" id="name" name="name" class="form-control"
                                    placeholder="Enter your full name" value="{{ old('name') }}">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control"
                                    placeholder="Enter your email" value="{{ old('email') }}">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password" class="form-control"
                                    placeholder="Enter your password">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                                    placeholder="Confirm your password">
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mt-3">Sign Up</button>
                        </form>


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
