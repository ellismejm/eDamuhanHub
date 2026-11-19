@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
    <div class="mb-4">
        <h1 class="dh-page-title">Audit Logs</h1>
        <p class="dh-page-subtitle mb-0">Read-only record of account and database-management activity.</p>
    </div>

    <div class="dh-card p-3 mb-3">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row g-2">
            <div class="col-sm-5">
                <label for="module" class="visually-hidden">Module</label>
                <select id="module" name="module" class="form-select">
                    <option value="">All modules</option>
                    @foreach($modules as $module)
                        <option value="{{ $module }}" @selected(request('module') === $module)>{{ str($module)->replace('_', ' ')->title() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-5">
                <label for="action" class="visually-hidden">Action</label>
                <input id="action" name="action" value="{{ request('action') }}" class="form-control" placeholder="Action">
            </div>
            <div class="col-sm-2 d-grid"><button class="btn btn-outline-success" type="submit">Filter</button></div>
        </form>
    </div>

    <div class="dh-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Date and time</th><th>User</th><th>Activity</th><th>Record</th><th>Details</th><th>IP address</th></tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-nowrap">{{ $log->created_at?->format('M d, Y h:i:s A') }}</td>
                            <td>{{ $log->user?->name ?? 'System or deleted account' }}</td>
                            <td><span class="badge text-bg-light">{{ str($log->module)->replace('_', ' ')->title() }}</span><br>{{ str($log->action)->replace('_', ' ')->title() }}</td>
                            <td>{{ class_basename($log->subject_type ?? '') ?: '—' }} {{ $log->subject_id ? '#'.$log->subject_id : '' }}</td>
                            <td style="min-width:220px">
                                {{ $log->description }}
                                @if($log->old_values || $log->new_values)
                                    <details class="small mt-1">
                                        <summary>View changes</summary>
                                        @if($log->old_values)<div class="mt-1">Before: <code>{{ json_encode($log->old_values, JSON_UNESCAPED_UNICODE) }}</code></div>@endif
                                        @if($log->new_values)<div>After: <code>{{ json_encode($log->new_values, JSON_UNESCAPED_UNICODE) }}</code></div>@endif
                                    </details>
                                @endif
                            </td>
                            <td>{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No matching audit activity.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $logs->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection
