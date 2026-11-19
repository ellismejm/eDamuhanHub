@extends('layouts.app')

@section('title', 'User Management')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dh-page-title">User Management</h1>
            <p class="dh-page-subtitle mb-0">Manage Administrator and read-only Standard User accounts.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn dh-btn-primary">
            <i class="bi bi-person-plus me-1"></i>Add user
        </a>
    </div>

    @if($errors->has('user'))
        <div class="alert alert-danger">{{ $errors->first('user') }}</div>
    @endif

    <div class="dh-card p-3 mb-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex gap-2">
            <input name="search" value="{{ request('search') }}" class="form-control"
                   placeholder="Search name or email" aria-label="Search users">
            <button type="submit" class="btn btn-outline-success">Search</button>
            @if(request('search'))
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </form>
    </div>

    <div class="dh-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role?->name ?? 'Unassigned' }}</td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $user->created_at?->format('M d, Y') }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                @if(auth()->id() !== $user->id)
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline"
                                          onsubmit="return confirm('Delete this account? Audit records will remain.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No user accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $users->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection
