@extends('layouts.app')

@section('title', 'Database Backups')

@section('content')
    <div class="mb-4">
        <h1 class="dh-page-title">Database Backups</h1>
        <p class="dh-page-subtitle mb-0">Create checksummed MySQL backups or restore a saved backup.</p>
    </div>

    @if($errors->has('backup'))
        <div class="alert alert-danger">{{ $errors->first('backup') }}</div>
    @endif
    @if($errors->has('restore'))
        <div class="alert alert-danger">{{ $errors->first('restore') }}</div>
    @endif

    <div class="alert alert-warning">
        <strong>Restore replaces the current database.</strong>
        Every restore requires your password and creates a safety backup first. Keep copies of important backups outside this server too.
    </div>

    <div class="dh-card p-3 p-md-4 mb-4">
        <h2 class="dh-section-title mb-3">Create a backup</h2>
        <form method="POST" action="{{ route('admin.backups.store') }}">
            @csrf
            <label for="notes" class="form-label">Notes (optional)</label>
            <textarea id="notes" name="notes" class="form-control mb-3" rows="2" maxlength="1000">{{ old('notes') }}</textarea>
            <button type="submit" class="btn dh-btn-primary">
                <i class="bi bi-database-down me-1"></i>Create database backup
            </button>
        </form>
    </div>

    <div class="dh-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Backup</th><th>Status</th><th>Size</th><th>Created</th><th>Created by</th><th>Checksum</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($backups as $backup)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $backup->filename }}</div>
                                <div class="small text-muted">{{ $backup->notes }}</div>
                            </td>
                            <td><span class="badge {{ $backup->status === 'completed' ? 'text-bg-success' : ($backup->status === 'failed' ? 'text-bg-danger' : 'text-bg-secondary') }}">{{ str($backup->status)->title() }}</span></td>
                            <td>{{ number_format($backup->file_size / 1024, 1) }} KB</td>
                            <td class="text-nowrap">{{ $backup->completed_at?->format('M d, Y h:i A') ?? $backup->created_at?->format('M d, Y h:i A') }}</td>
                            <td>{{ $backup->creator?->name ?? 'Unknown' }}</td>
                            <td><code class="small">{{ $backup->checksum ? substr($backup->checksum, 0, 16).'…' : '—' }}</code></td>
                            <td class="text-nowrap">
                                @if($backup->status === 'completed')
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.backups.download', $backup) }}">Download</a>
                                    <details class="mt-2">
                                        <summary class="text-danger small">Restore this backup</summary>
                                        <form method="POST" action="{{ route('admin.backups.restore', $backup) }}" class="border rounded p-2 mt-2"
                                              onsubmit="return confirm('This replaces the current database and logs you out. Continue?');">
                                            @csrf
                                            <div class="form-check">
                                                <input class="form-check-input" id="confirm-restore-{{ $backup->id }}"
                                                       name="confirm_restore" type="checkbox" value="1" required>
                                                <label class="form-check-label small" for="confirm-restore-{{ $backup->id }}">I understand this replaces current data</label>
                                            </div>
                                            <label class="form-label small mt-2" for="restore-password-{{ $backup->id }}">Current password</label>
                                            <input id="restore-password-{{ $backup->id }}" name="password" type="password"
                                                   class="form-control form-control-sm mb-2" autocomplete="current-password" required>
                                            <button class="btn btn-sm btn-danger" type="submit">Restore</button>
                                        </form>
                                    </details>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No database backups created.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $backups->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection
