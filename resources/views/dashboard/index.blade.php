@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    {{-- PAGE HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dh-page-title">Dashboard</h1>
            <p class="dh-page-subtitle mb-0">
                Welcome to eDamuhanHub. Here's an overview of your weed database.
            </p>
        </div>

        <div class="text-muted small">
            <i class="bi bi-calendar3 me-1"></i>
            {{ now()->format('F d, Y') }}
        </div>
    </div>

    {{-- STATISTICS --}}
    <div class="row g-3 mb-4">

        <div class="col-6 col-xl-2">
            <div class="dh-card dh-stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="dh-stat-icon"
                         style="background:#eaf1e5;color:#395b32;">
                        <i class="bi bi-flower2"></i>
                    </div>

                    <span class="dh-badge">Database</span>
                </div>

                <div class="dh-stat-number">{{ number_format($speciesCount) }}</div>
                <div class="dh-stat-label">Weed Species</div>
            </div>
        </div>

        <div class="col-6 col-xl-2">
            <div class="dh-card dh-stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="dh-stat-icon"
                         style="background:#e7f0ff;color:#2864b1;">
                        <i class="bi bi-grid"></i>
                    </div>

                    <span class="dh-badge">Groups</span>
                </div>

                <div class="dh-stat-number">{{ number_format($classificationCount) }}</div>
                <div class="dh-stat-label">Classifications</div>
            </div>
        </div>

        <div class="col-6 col-xl-2">
            <div class="dh-card dh-stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="dh-stat-icon"
                         style="background:#fff2df;color:#c47b19;">
                        <i class="bi bi-diagram-3"></i>
                    </div>

                    <span class="dh-badge">Taxonomy</span>
                </div>

                <div class="dh-stat-number">{{ number_format($familyCount) }}</div>
                <div class="dh-stat-label">Weed Families</div>
            </div>
        </div>

        <div class="col-6 col-xl-2">
            <div class="dh-card dh-stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="dh-stat-icon"
                         style="background:#f1e8ff;color:#7950b5;">
                        <i class="bi bi-images"></i>
                    </div>

                    <span class="dh-badge">Media</span>
                </div>

                <div class="dh-stat-number">{{ number_format($imageCount) }}</div>
                <div class="dh-stat-label">Species Images</div>
            </div>
        </div>

        <div class="col-6 col-xl-2">
            <div class="dh-card dh-stat-card">
                <div class="dh-stat-icon" style="background:#e8f4ef;color:#397a5a;">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <div class="dh-stat-number">{{ number_format($lifeCycleCount) }}</div>
                <div class="dh-stat-label">Life Cycles</div>
            </div>
        </div>
        @if(auth()->user()->hasRole('Administrator'))
            <div class="col-6 col-xl-2">
                <div class="dh-card dh-stat-card">
                    <div class="dh-stat-icon" style="background:#e7f0ff;color:#2864b1;">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="dh-stat-number">{{ number_format($userCount) }}</div>
                    <div class="dh-stat-label">Active Users</div>
                </div>
            </div>
        @endif
    </div>

    {{-- MAIN DASHBOARD CONTENT --}}
    <div class="row g-3">

        {{-- RECENT SPECIES --}}
        <div class="col-12 col-xl-8">

            <div class="dh-card p-3 p-md-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="dh-section-title mb-1">Weed Species</h2>
                        <div class="small text-muted">
                            Browse species currently available in the database.
                        </div>
                    </div>

                    <a href="{{ route('species.index') }}" class="btn btn-sm btn-outline-success">
    View All <i class="bi bi-arrow-right ms-1"></i>
</a>
                </div>

                <div class="d-flex flex-column">

                    @forelse($recentSpecies as $species)

                        <div class="d-flex align-items-center gap-3 py-3 border-bottom">

                            {{-- SPECIES IMAGE --}}
                            @if($species->primaryImage)
                                <img
                                    src="{{ asset('storage/' . $species->primaryImage->image_path) }}"
                                    alt="{{ $species->scientific_name }}"
                                    class="dh-species-image"
                                >
                            @else
                                <div class="dh-species-image d-flex align-items-center justify-content-center">
                                    <i class="bi bi-flower2 fs-3 text-success"></i>
                                </div>
                            @endif

                            {{-- SPECIES INFORMATION --}}
                            <div class="flex-grow-1 min-width-0">

                                <a href="{{ route('species.show', $species) }}"
                                   class="dh-species-name d-block mb-1">
                                    {{ $species->scientific_name }}
                                </a>

                                <div class="small text-muted mb-2">
                                    {{ $species->family?->name ?? 'Family not specified' }}
                                </div>

                                <span class="dh-badge">
                                    {{ $species->classification?->name ?? 'Unclassified' }}
                                </span>

                            </div>

                            {{-- EPPO CODE --}}
                            <div class="text-end d-none d-sm-block">
                                <div class="small text-muted">EPPO Code</div>
                                <div class="fw-semibold small">
                                    {{ $species->eppo_code ?? 'N/A' }}
                                </div>
                            </div>

                        </div>

                    @empty

                        <div class="text-center py-5">
                            <i class="bi bi-flower2 display-5 text-muted"></i>
                            <p class="mt-3 text-muted">
                                No weed species found.
                            </p>
                        </div>

                    @endforelse

                </div>

            </div>

        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-12 col-xl-4">

            {{-- CLASSIFICATION BREAKDOWN --}}
            <div class="dh-card p-3 p-md-4 mb-3">

                <h2 class="dh-section-title mb-3">
                    <i class="bi bi-pie-chart me-2 text-success"></i>
                    Classifications
                </h2>

                @foreach($classificationStats as $classification)

                    @php
                        $percentage = $speciesCount > 0
                            ? ($classification->species_count / $speciesCount) * 100
                            : 0;
                    @endphp

                    <div class="mb-3">

                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold">
                                {{ $classification->name }}
                            </span>

                            <span class="text-muted">
                                {{ $classification->species_count }}
                            </span>
                        </div>

                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar"
                                 role="progressbar"
                                 style="width: {{ $percentage }}%; background:var(--dh-green);"
                                 aria-valuenow="{{ $percentage }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                            </div>
                        </div>

                    </div>

                @endforeach

            </div>

            {{-- RECENT IMPORT --}}
            <div class="dh-card p-3 p-md-4 mb-3">

                <h2 class="dh-section-title mb-3">
                    <i class="bi bi-cloud-upload me-2 text-success"></i>
                    Latest Import
                </h2>

                @if($latestImport)

                    <div class="d-flex align-items-center gap-2 mb-3">

                        <div class="dh-stat-icon"
                             style="background:#eaf1e5;color:#395b32;width:40px;height:40px;">
                            <i class="bi bi-check-circle"></i>
                        </div>

                        <div>
                            <div class="fw-semibold">
                                Import #{{ $latestImport->id }}
                            </div>

                            <div class="small text-muted">
                                {{ $latestImport->created_at?->format('M d, Y h:i A') }}
                            </div>
                        </div>

                    </div>

                    <div class="row g-2 text-center">

                        <div class="col-4">
                            <div class="p-2 rounded bg-light">
                                <div class="fw-bold">
                                    {{ $latestImport->total_rows }}
                                </div>
                                <div class="small text-muted">Total</div>
                            </div>
                        </div>

                        <div class="col-4">
                            <div class="p-2 rounded bg-light">
                                <div class="fw-bold text-success">
                                    {{ $latestImport->successful_rows }}
                                </div>
                                <div class="small text-muted">Success</div>
                            </div>
                        </div>

                        <div class="col-4">
                            <div class="p-2 rounded bg-light">
                                <div class="fw-bold text-danger">
                                    {{ $latestImport->failed_rows }}
                                </div>
                                <div class="small text-muted">Failed</div>
                            </div>
                        </div>

                    </div>

                @else

                    <p class="text-muted small mb-0">
                        No import records available.
                    </p>

                @endif

            </div>

            {{-- QUICK ACTIONS --}}
            <div class="dh-card p-3 p-md-4">

                <h2 class="dh-section-title mb-3">
                    <i class="bi bi-lightning me-2 text-warning"></i>
                    Quick Actions
                </h2>

                <div class="d-grid gap-2">

                    @if(auth()->user()->hasRole('Administrator'))
                        <a href="{{ route('species.create') }}" class="btn dh-btn-primary">
                            <i class="bi bi-plus-lg me-2"></i>Add Weed Species
                        </a>
                        <a href="{{ route('admin.imports.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-cloud-upload me-2"></i>Import Dataset
                        </a>
                        <a href="{{ route('admin.imports.index') }}#import-history" class="btn btn-outline-secondary">
                            <i class="bi bi-clock-history me-2"></i>View Import History
                        </a>
                    @else
                        <a href="{{ route('species.index') }}" class="btn dh-btn-primary">
                            <i class="bi bi-search me-2"></i>Browse Weed Species
                        </a>
                    @endif

                </div>

            </div>

        </div>

    </div>

@endsection