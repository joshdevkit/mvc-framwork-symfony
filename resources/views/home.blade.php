@extends('layouts.app')

@section('title', 'HOMEPAGE')

@section('content')
    @if (session('message'))
        <div class="alert alert-success" role="alert"> {{ session('message') }} </div>
    @endif
    <div class="jumbotron text-center bg-light p-5 rounded">
        <h1 class="display-3 mb-3">{{ $message }}</h1>
        <p class="lead">Welcome to the PHP MVC Framework - SYMFONY! Build scalable and efficient apps effortlessly.</p>
        <hr class="my-4">
        <p>Explore the features, create models, and manage your application seamlessly.</p>
        <p>Developed By: {{ $dev }}</p>
        <a class="btn btn-primary  mt-3" href="{{ route('auth.show') }}" role="button">Learn More</a>
    </div>



    <script>
        $(document).ready(function() {
            $.ajax({
                url: '/users/1',
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // if you remove this, you will no longer proced with ajax request
                },
                success: function(response) {
                    console.log(response);
                },
                error: function(xhr) {
                    console.error("Failed to fetch data:", xhr.responseText);
                }
            });
        });
    </script>

@endsection
