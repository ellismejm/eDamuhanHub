@extends('layouts.app')

@section('title', $managedUser->exists ? 'Edit User' : 'Create User')

@section('content')
    @php($isEditing = $managedUser->exists)

    <div class="mb-4">
        <h1 class="dh-page-title">{{ $isEditing ? 'Edit User Account' : 'Create User Account' }}</h1>
        <p class="dh-page-subtitle mb-0">Standard Users can browse and search; only Administrators can make changes.</p>
    </div>

    <div class="dh-card p-3 p-md-4">
        <form method="POST" action="{{ $isEditing ? route('admin.users.update', $managedUser) : route('admin.users.store') }}">
            @csrf
            @if($isEditing) @method('PUT') @endif

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input id="name" name="name" value="{{ old('name', $managedUser->name) }}"
                       class="form-control @error('name') is-invalid @enderror" required maxlength="255">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $managedUser->email) }}"
                       class="form-control @error('email') is-invalid @enderror" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="role_id" class="form-label">Role</label>
                <select id="role_id" name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" @selected(old('role_id', $managedUser->role_id) == $role->id)>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @if($isEditing)
                <div class="form-check mb-3">
                    <input type="hidden" name="is_active" value="0">
                    <input id="is_active" name="is_active" type="checkbox" value="1" class="form-check-input"
                           @checked(old('is_active', $managedUser->is_active))>
                    <label for="is_active" class="form-check-label">Account is active</label>
                </div>
                @error('is_active')<div class="text-danger small mb-3">{{ $message }}</div>@enderror
            @endif

            <div class="mb-3">
                <label for="password" class="form-label">{{ $isEditing ? 'New password (optional)' : 'Password' }}</label>
                <input id="password" name="password" type="password"
                       class="form-control @error('password') is-invalid @enderror"
                       autocomplete="new-password" {{ $isEditing ? '' : 'required' }}>
                @if(!$isEditing)
                    <div class="form-text">At least 12 characters with uppercase and lowercase letters, a number, and a symbol.</div>
                @endif
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Confirm {{ $isEditing ? 'new ' : '' }}password</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                       class="form-control" autocomplete="new-password"
                       {{ $isEditing ? '' : 'required' }}>
            </div>

            <div class="d-flex gap-2">
                <button class="btn dh-btn-primary" type="submit">{{ $isEditing ? 'Save changes' : 'Create account' }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
