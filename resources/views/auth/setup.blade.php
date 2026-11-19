@extends('layouts.app')

@section('title', 'Create administrator')

@section('content')
    <div class="row justify-content-center py-5">
        <div class="col-12 col-md-9 col-lg-6">
            <div class="dh-card p-4 p-md-5">
                <h1 class="dh-page-title mb-2">Set up eDamuhanHub</h1>
                <p class="dh-page-subtitle mb-4">
                    Create the first Administrator account. This setup closes as soon as the account is created.
                </p>

                <form method="POST" action="{{ route('setup.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Full name</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror"
                               autocomplete="name" required autofocus>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Administrator email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror"
                               autocomplete="email" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" name="password" type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               autocomplete="new-password" required>
                        <div class="form-text">Use at least 12 characters with uppercase and lowercase letters, a number, and a symbol.</div>
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                               class="form-control" autocomplete="new-password" required>
                    </div>

                    <button class="btn dh-btn-primary w-100" type="submit">Create Administrator</button>
                </form>
            </div>
        </div>
    </div>
@endsection
