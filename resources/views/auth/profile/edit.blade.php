@extends('layouts.auth')

@section('content')
    <style>
        .avatar {
            display: inline-block;
            position: relative;
        }

        .avatar-clickable {
            border-radius: 50%;
            background-color: #fff;
        }

        img {
            object-fit: contain;
        }

        #avatarPreview {
            vertical-align: initial;
        }

        .camera-icon {
            bottom: -12px;
            left: 12px;
            position: absolute;
            background-color: #fff;
            border-radius: 50%;
            padding: 4px;
            color: #000;
            font-size: 16px;
            border: 1px solid #ccc;
            transform: translate(-50%, -50%);
        }
    </style>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <form action="{{ route('update-avatar') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="avatar position-relative">
                            <img id="avatarPreview"
                                src="{{ auth()->user()->avatar ? asset(auth()->user()->avatar) : 'https://via.placeholder.com/100' }}"
                                alt="User Avatar" class="avatar-clickable"
                                style="width: 80px; height: 80px; cursor: pointer;">
                            <i class="fa-solid fa-camera position-absolute camera-icon"></i>
                            <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display: none;">
                        </div>

                        <div class="ml-3">
                            <h3 class="mb-0">{{ auth()->user()->name }}</h3>
                            <p class="mb-0 small text-light">{{ auth()->user()->email }}</p>
                        </div>

                        <div class="modal fade" id="confirmUploadModal" tabindex="-1" role="dialog"
                            aria-labelledby="confirmUploadLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <p>Confirm to upload this as your new avatar ?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-dismiss="modal">Cancel</button>
                                        <button type="submit" id="confirmAvatarUpdate"
                                            class="btn btn-primary">Confirm</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="ml-auto">
                        <button class="btn btn-light btn-sm" data-toggle="modal" data-target="#editProfileModal">
                            Edit Profile
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('message'))
                        <div class="alert alert-success" role="alert"> {{ session('message') }} </div>
                    @endif
                    @if (session('errors'))
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                @foreach (session('errors') as $field => $messages)
                                    @foreach ($messages as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                @endforeach
                            </ul>
                        </div>
                    @endif

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

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('update-profile') }}" method="POST" enctype="multipart/form-data">
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
                                value="{{ auth()->user()->name }}">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control"
                                value="{{ auth()->user()->email }}">
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




    <script>
        document.querySelector('.avatar-clickable').addEventListener('click', function() {
            document.getElementById('avatarInput').click();
        });

        document.getElementById('avatarInput').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                    $('#confirmUploadModal').modal('show');
                };
                reader.readAsDataURL(file);
            }
        });

        // document.getElementById('confirmAvatarUpdate').addEventListener('click', function() {
        //     $('#confirmUploadModal').modal('hide');
        // });
    </script>
@endsection
