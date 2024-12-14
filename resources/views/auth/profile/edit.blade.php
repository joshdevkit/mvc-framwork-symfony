@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Profile Card -->
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white d-flex align-items-center">
                        <div class="avatar">
                            <img src="https://via.placeholder.com/100" alt="User Avatar"
                                class="rounded-circle border border-white" style="width: 80px; height: 80px;">
                        </div>
                        <div class="ml-3">
                            <h3 class="mb-0">{{ auth()->user()->name }}</h3>
                            <p class="mb-0 small text-light">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="ml-auto">
                            <button class="btn btn-light btn-sm" data-toggle="modal" data-target="#editProfileModal">
                                Edit Profile
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Profile Details</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>Full Name:</strong> {{ auth()->user()->name }}
                            </li>
                            <li class="list-group-item">
                                <strong>Email:</strong> {{ auth()->user()->email }}
                            </li>
                        </ul>
                    </div>
                    {{-- <div class="card-footer text-right">
                        <button class="btn btn-outline-danger btn-sm"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Logout
                        </button>
                        <form id="logout-form" action="{{ url('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ url('/profile/update') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" name="name" id="name" class="form-control"
                                value="{{ auth()->user()->name }}" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control"
                                value="{{ auth()->user()->email }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection