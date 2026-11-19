@extends('layouts.app')

@section('title', 'Weed Species')

@section('content')

    {{-- PAGE HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <h1 class="dh-page-title">Weed Species</h1>

            <p class="dh-page-subtitle mb-0">
                Explore and browse rice-associated weed species.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="dh-badge">
                {{ number_format($totalSpecies) }} species in database
            </span>
            @if(auth()->user()->hasRole('Administrator'))
                <a href="{{ route('species.create') }}" class="btn dh-btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Add species
                </a>
            @endif
        </div>

    </div>


    {{-- SEARCH --}}
<div class="dh-card p-3 mb-3">

    <form action="{{ route('species.index') }}" method="GET">

        @if($classificationId)
            <input type="hidden" name="classification" value="{{ $classificationId }}">
        @endif

        <div class="dh-species-search">

            <div class="dh-search-field">

                <i class="bi bi-search dh-search-icon"></i>

                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search scientific name, common name, family, EPPO code..."
                    autocomplete="off"
                    aria-label="Search weed species"
                >

                @if($search)
                    <a href="{{ route('species.index', ['classification' => $classificationId]) }}"
                       class="dh-search-clear"
                       title="Clear search"
                       aria-label="Clear search">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif

            </div>

            <button type="submit" class="btn dh-btn-primary dh-search-button">
                <i class="bi bi-search me-1"></i>
                Search
            </button>

        </div>

    </form>

</div>


    {{-- CLASSIFICATION FILTERS --}}
    <div class="dh-card p-3 mb-4">

        <div class="d-flex flex-wrap align-items-center gap-2">

            <span class="small fw-semibold text-muted me-1">
                Classification:
            </span>

            {{-- ALL --}}
            <a href="{{ route('species.index', ['search' => $search]) }}"
               class="dh-filter-chip {{ !$classificationId ? 'active' : '' }}">

                <i class="bi bi-grid me-1"></i>
                All Species
                <span class="dh-filter-count">{{ $totalSpecies }}</span>
            </a>

            {{-- CLASSIFICATIONS --}}
            @foreach($classifications as $classification)

                <a href="{{ route('species.index', [
                        'search' => $search,
                        'classification' => $classification->id
                    ]) }}"
                   class="dh-filter-chip {{ $classificationId == $classification->id ? 'active' : '' }}">

                    {{ $classification->name }}

                    <span class="dh-filter-count">
                        {{ $classification->species_count }}
                    </span>

                </a>

            @endforeach

        </div>

    </div>


    {{-- RESULTS HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">

        <div class="fw-semibold">

            @if($search)
                Search results for
                <span class="text-success">"{{ $search }}"</span>
            @else
                Browse species
            @endif

        </div>

        <div class="small text-muted">
            Showing {{ $species->firstItem() ?? 0 }}–{{ $species->lastItem() ?? 0 }}
            of {{ $species->total() }} results
        </div>

    </div>


    {{-- SPECIES GRID --}}
    @if($species->count())

        <div class="row g-3">

            @foreach($species as $item)

                <div class="col-12 col-sm-6 col-xl-4">

                    <div class="dh-species-card h-100">

                        {{-- IMAGE --}}
                        <div class="dh-species-card-image">

                            @if($item->primaryImage)

                                <img
                                    src="{{ asset('storage/' . $item->primaryImage->image_path) }}"
                                    alt="{{ $item->primaryImage->alt_text ?? $item->scientific_name }}"
                                    loading="lazy"
                                >

                            @else

                                <div class="dh-species-no-image">
                                    <i class="bi bi-flower2"></i>
                                    <span>No image available</span>
                                </div>

                            @endif

                            {{-- CLASSIFICATION BADGE --}}
                            <div class="dh-species-card-badge">
                                {{ $item->classification?->name ?? 'Unclassified' }}
                            </div>

                        </div>


                        {{-- INFORMATION --}}
                        <div class="dh-species-card-body">

                            <h3 class="dh-species-card-title">
                                {{ $item->scientific_name }}
                            </h3>

                            @if($item->taxonomic_author)
                                <div class="dh-species-author">
                                    {{ $item->taxonomic_author }}
                                </div>
                            @endif


                            {{-- COMMON NAMES --}}
                            <div class="dh-species-common-names">

                                @if($item->localNames->count())

                                    <div class="small text-muted mb-1">
                                        Common names
                                    </div>

                                    <div>
                                        {{ $item->localNames->take(3)->pluck('name')->implode(', ') }}

                                        @if($item->localNames->count() > 3)
                                            <span class="text-muted">
                                                +{{ $item->localNames->count() - 3 }} more
                                            </span>
                                        @endif
                                    </div>

                                @else

                                    <span class="text-muted small">
                                        No common names recorded
                                    </span>

                                @endif

                            </div>


                            {{-- FAMILY --}}
                            <div class="dh-species-card-meta">

                                <i class="bi bi-diagram-3"></i>

                                <span>
                                    {{ $item->family?->name ?? 'Family not specified' }}
                                </span>

                            </div>


                            {{-- EPPO --}}
                            @if($item->eppo_code)

                                <div class="dh-species-card-meta">

                                    <i class="bi bi-tag"></i>

                                    <span>
                                        EPPO: {{ $item->eppo_code }}
                                    </span>

                                </div>

                            @endif


                            {{-- FOOTER --}}
                            <div class="dh-species-card-footer">

                                <span class="small text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Species profile
                                </span>

                                <a href="{{ route('species.show', $item->id) }}"
   class="dh-species-view-link">
    View Details
    <i class="bi bi-arrow-right"></i>
</a>

                            </div>

                        </div>

                    </div>

                </div>

            @endforeach

        </div>


        {{-- PAGINATION --}}
        <div class="mt-4 d-flex justify-content-center">

            {{ $species->links('pagination::bootstrap-5') }}

        </div>


    @else

        {{-- EMPTY SEARCH RESULT --}}
        <div class="dh-card text-center py-5 px-3">

            <div class="dh-empty-icon mb-3">
                <i class="bi bi-search"></i>
            </div>

            <h4 class="fw-semibold">No species found</h4>

            <p class="text-muted mb-3">
                We couldn't find any species matching your search.
                Try a scientific name, common name, family, or EPPO code.
            </p>

            <a href="{{ route('species.index') }}"
               class="btn dh-btn-primary">
                <i class="bi bi-arrow-counterclockwise me-1"></i>
                Clear Search and Filters
            </a>

        </div>

    @endif

@endsection