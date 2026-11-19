@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
    <div class="row justify-content-center py-5">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="dh-card p-4 p-md-5">
                <h1 class="dh-page-title mb-2">Welcome back</h1>
                <p class="dh-page-subtitle mb-4">Sign in to explore the Philippine rice weed database.</p>

                @if(session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror"
                               autocomplete="username" required autofocus>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" name="password" type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               autocomplete="current-password" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-check mb-4">
                        <input id="remember" name="remember" type="checkbox" value="1" class="form-check-input">
                        <label for="remember" class="form-check-label">Remember me</label>
                    </div>

                    <button class="btn dh-btn-primary w-100" type="submit">Sign in</button>
                </form>
            </div>
        </div>
    </div>
@endsection
