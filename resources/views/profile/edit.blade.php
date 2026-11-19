@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="mb-4">
        <h1 class="dh-page-title">My Profile</h1>
        <p class="dh-page-subtitle mb-0">Update your account information and password.</p>
    </div>

    <div class="dh-card p-3 p-md-4">
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}"
                       class="form-control @error('name') is-invalid @enderror" required maxlength="255">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label for="email" class="form-label">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                       class="form-control @error('email') is-invalid @enderror" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <h2 class="dh-section-title mb-3">Change password</h2>
            <div class="mb-3">
                <label for="current_password" class="form-label">Current password (required to change password)</label>
                <input id="current_password" name="current_password" type="password"
                       class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password">
                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="password" class="form-label">New password</label>
                    <input id="password" name="password" type="password"
                           class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password"
                           class="form-control" autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn dh-btn-primary">Save profile</button>
        </form>
    </div>
@endsection
