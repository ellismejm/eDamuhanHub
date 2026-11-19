@extends('layouts.app')

@section('title', 'Dataset Imports')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dh-page-title">Dataset Imports</h1>
            <p class="dh-page-subtitle mb-0">Validate and import the packaged 81-species dataset and photographs.</p>
        </div>
    </div>

    @if($errors->has('import'))
        <div class="alert alert-danger">{{ $errors->first('import') }}</div>
    @endif

    <div class="dh-card p-3 p-md-4 mb-4">
        <h2 class="dh-section-title mb-2">Import the bundled weed dataset</h2>
        <p class="text-muted">
            Existing species are skipped unless the import command is run with its explicit update option.
            The importer checks all source images before it writes records.
        </p>
        <form method="POST" action="{{ route('admin.imports.store') }}"
              onsubmit="return confirm('Start importing the bundled weed dataset? Existing species will not be overwritten.');">
            @csrf
            <button type="submit" class="btn dh-btn-primary">
                <i class="bi bi-cloud-upload me-1"></i>Validate and import dataset
            </button>
        </form>
    </div>

    <div class="dh-card overflow-hidden" id="import-history">
        <div class="p-3 p-md-4 border-bottom">
            <h2 class="dh-section-title mb-0">Import history</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Batch</th><th>Source</th><th>Status</th><th>Rows</th><th>Started</th><th>Initiated by</th><th>Errors</th></tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                        <tr>
                            <td>#{{ $batch->id }}</td>
                            <td>{{ $batch->source_filename }}</td>
                            <td><span class="badge {{ in_array($batch->status, ['completed', 'completed_with_errors']) ? 'text-bg-success' : 'text-bg-secondary' }}">{{ str($batch->status)->replace('_', ' ')->title() }}</span></td>
                            <td>{{ $batch->successful_rows }} successful / {{ $batch->failed_rows }} failed / {{ $batch->total_rows }} total</td>
                            <td class="text-nowrap">{{ $batch->started_at?->format('M d, Y h:i A') ?? $batch->created_at?->format('M d, Y h:i A') }}</td>
                            <td>{{ $batch->initiator?->name ?? 'CLI / unknown' }}</td>
                            <td>
                                @if($batch->error_summary)
                                    <details><summary>View errors</summary><pre class="small mt-2 mb-0">{{ json_encode($batch->error_summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></details>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No imports have been run.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $batches->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection
