
@extends('layouts.app')

@section('title', $weedSpecies->scientific_name)

@section('content')

    {{-- BACK NAVIGATION --}}
    <div class="mb-3">
        <a href="{{ route('species.index') }}" class="dh-profile-back">
            <i class="bi bi-arrow-left me-2"></i>
            Back to Weed Species
        </a>
        @if(auth()->user()->hasRole('Administrator'))
            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('species.edit', $weedSpecies) }}" class="btn btn-outline-success">
                    <i class="bi bi-pencil me-1"></i>Edit species
                </a>
                <form method="POST" action="{{ route('species.destroy', $weedSpecies) }}"
                      onsubmit="return confirm('Archive this species? It will no longer appear in the database.');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger" type="submit">
                        <i class="bi bi-archive me-1"></i>Archive species
                    </button>
                </form>
            </div>
        @endif
    </div>


    {{-- SPECIES HEADER --}}
    <div class="dh-card dh-profile-header p-4 mb-4">

        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">

            <div class="flex-grow-1">

                <div class="d-flex flex-wrap gap-2 mb-3">

                    <span class="dh-badge">
                        {{ $weedSpecies->classification?->name ?? 'Unclassified' }}
                    </span>

                    @if($weedSpecies->eppo_code)
                        <span class="dh-profile-code">
                            EPPO: {{ $weedSpecies->eppo_code }}
                        </span>
                    @endif

                </div>

                <h1 class="dh-profile-scientific-name">
                    {{ $weedSpecies->scientific_name }}
                </h1>

                @if($weedSpecies->taxonomic_author)
                    <p class="dh-profile-author mb-3">
                        {{ $weedSpecies->taxonomic_author }}
                    </p>
                @endif

                <p class="dh-page-subtitle mb-0">
                    Weed species profile and reference information
                </p>

            </div>

            <div class="dh-profile-header-icon">
                <i class="bi bi-flower2"></i>
            </div>

        </div>

    </div>


    {{-- MAIN PROFILE CONTENT --}}
    <div class="row g-4">


        {{-- LEFT COLUMN --}}
        <div class="col-12 col-xl-7">


            {{-- PHOTO --}}
            <div class="dh-card overflow-hidden mb-4">

                <div class="dh-profile-section-heading">
                    <i class="bi bi-images me-2"></i>
                    Species Photos
                </div>

                @if($images->count())

                    {{-- MAIN PHOTO --}}
                    @php
                        $mainImage = $images->first();
                    @endphp

                    <div class="dh-profile-main-image">
                        <button
                            type="button"
                            class="dh-lightbox-trigger dh-lightbox-main-trigger"
                            data-image-src="{{ asset('storage/' . $mainImage->image_path) }}"
                            data-image-alt="{{ $mainImage->alt_text ?? $weedSpecies->scientific_name }}"
                            aria-label="Enlarge species photo"
                        >
                            <img
                                src="{{ asset('storage/' . $mainImage->image_path) }}"
                                alt="{{ $mainImage->alt_text ?? $weedSpecies->scientific_name }}"
                            >
                            <span class="dh-image-zoom-hint">
                                <i class="bi bi-zoom-in"></i>
                                Click to enlarge
                            </span>
                        </button>
                    </div>

                    @if($mainImage->caption)
                        <div class="px-3 pt-2 pb-1 small text-muted">
                            {{ $mainImage->caption }}
                        </div>
                    @endif


                    {{-- ADDITIONAL PHOTOS --}}
                    @if($images->count() > 1)

                        <div class="dh-profile-gallery p-3">

                            @foreach($images->skip(1) as $image)

                                <div class="dh-profile-gallery-item">
                                    <button
                                        type="button"
                                        class="dh-lightbox-trigger"
                                        data-image-src="{{ asset('storage/' . $image->image_path) }}"
                                        data-image-alt="{{ $image->alt_text ?? $weedSpecies->scientific_name }}"
                                        aria-label="Enlarge species photo"
                                    >
                                        <img
                                            src="{{ asset('storage/' . $image->image_path) }}"
                                            alt="{{ $image->alt_text ?? $weedSpecies->scientific_name }}"
                                            loading="lazy"
                                        >
                                        <span class="dh-image-zoom-hint">
                                            <i class="bi bi-zoom-in"></i>
                                        </span>
                                    </button>

                                    @if($image->caption)
                                        <div class="dh-profile-gallery-caption">
                                            {{ $image->caption }}
                                        </div>
                                    @endif

                                </div>

                            @endforeach

                        </div>

                    @endif

                @else

                    <div class="dh-profile-no-photo">
                        <i class="bi bi-image"></i>
                        <p class="mb-0">No photos available for this species.</p>
                    </div>

                @endif

            </div>


            {{-- DESCRIPTION / CHARACTERISTICS --}}
            <div class="dh-card p-4 mb-4">

                <div class="dh-profile-section-heading">
                    <i class="bi bi-card-text me-2"></i>
                    Identification and Characteristics
                </div>

                @if($weedSpecies->characteristics)

                    <div class="dh-profile-content">
                        {!! nl2br(e($weedSpecies->characteristics)) !!}
                    </div>

                @else

                    <p class="text-muted mb-0">
                        No characteristics information has been recorded.
                    </p>

                @endif

            </div>


            {{-- REPRODUCTION --}}
            <div class="dh-card p-4 mb-4">

                <div class="dh-profile-section-heading">
                    <i class="bi bi-arrow-repeat me-2"></i>
                    Reproduction and Spread
                </div>

                @if($weedSpecies->reproduction_description)

                    <div class="dh-profile-content">
                        {!! nl2br(e($weedSpecies->reproduction_description)) !!}
                    </div>

                @else

                    <p class="text-muted mb-0">
                        Reproduction information is not available.
                    </p>

                @endif

            </div>


            {{-- IMPACTS --}}
            <div class="dh-card p-4 mb-4">

                <div class="dh-profile-section-heading">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Impacts
                </div>

                @if($weedSpecies->impacts)

                    <div class="dh-profile-content">
                        {!! nl2br(e($weedSpecies->impacts)) !!}
                    </div>

                @else

                    <p class="text-muted mb-0">
                        No impact information has been recorded.
                    </p>

                @endif

            </div>


            {{-- WEAKNESSES --}}
            <div class="dh-card p-4 mb-4">

                <div class="dh-profile-section-heading">
                    <i class="bi bi-shield-check me-2"></i>
                    Weaknesses and Management Considerations
                </div>

                @if($weedSpecies->weakness)

                    <div class="dh-profile-content">
                        {!! nl2br(e($weedSpecies->weakness)) !!}
                    </div>

                @else

                    <p class="text-muted mb-0">
                        No weakness or management-related information has been recorded.
                    </p>

                @endif

            </div>


        </div>


        {{-- RIGHT COLUMN --}}
        <div class="col-12 col-xl-5">


            {{-- TAXONOMY --}}
            <div class="dh-card p-4 mb-4">

                <div class="dh-profile-section-heading">
                    <i class="bi bi-diagram-3 me-2"></i>
                    Taxonomy and Classification
                </div>

                <div class="dh-profile-detail-list">

                    <div class="dh-profile-detail-row">
                        <div class="dh-profile-detail-label">
                            Classification
                        </div>

                        <div class="dh-profile-detail-value">
                            {{ $weedSpecies->classification?->name ?? 'Not specified' }}
                        </div>
                    </div>

                    <div class="dh-profile-detail-row">
                        <div class="dh-profile-detail-label">
                            Family
                        </div>

                        <div class="dh-profile-detail-value">
                            {{ $weedSpecies->family?->name ?? 'Not specified' }}
                        </div>
                    </div>

                    <div class="dh-profile-detail-row">
                        <div class="dh-profile-detail-label">
                            Scientific Name
                        </div>

                        <div class="dh-profile-detail-value fst-italic">
                            {{ $weedSpecies->scientific_name }}
                        </div>
                    </div>

                    <div class="dh-profile-detail-row">
                        <div class="dh-profile-detail-label">
                            Taxonomic Author
                        </div>

                        <div class="dh-profile-detail-value">
                            {{ $weedSpecies->taxonomic_author ?: 'Not specified' }}
                        </div>
                    </div>

                    <div class="dh-profile-detail-row">
                        <div class="dh-profile-detail-label">
                            EPPO Code
                        </div>

                        <div class="dh-profile-detail-value">
                            {{ $weedSpecies->eppo_code ?: 'Not specified' }}
                        </div>
                    </div>

                </div>

            </div>


            {{-- ECOLOGY --}}
            <div class="dh-card p-4 mb-4">

                <div class="dh-profile-section-heading">
                    <i class="bi bi-globe-asia-australia me-2"></i>
                    Ecology
                </div>

                <div class="dh-profile-detail-list">

                    <div class="dh-profile-detail-row">

                        <div class="dh-profile-detail-label">
                            Habitat
                        </div>

                        <div class="dh-profile-detail-value">
                            {{ $weedSpecies->habitat?->name ?? 'Not specified' }}
                        </div>

                    </div>

                    <div class="dh-profile-detail-row">

                        <div class="dh-profile-detail-label">
                            Life Cycle
                        </div>

                        <div class="dh-profile-detail-value">
                            {{ $weedSpecies->lifeCycle?->name ?? 'Not specified' }}
                        </div>

                    </div>

                </div>

            </div>


            {{-- LOCAL NAMES --}}
            <div class="dh-card p-4 mb-4">

                <div class="dh-profile-section-heading">
                    <i class="bi bi-translate me-2"></i>
                    Common and Local Names
                </div>

                @if($weedSpecies->localNames->count())

                    <div class="dh-profile-name-list">

                        @foreach($weedSpecies->localNames as $localName)

                            <span class="dh-profile-name-tag">
                                {{ $localName->name }}
                            </span>

                        @endforeach

                    </div>

                @else

                    <p class="text-muted mb-0">
                        No common or local names recorded.
                    </p>

                @endif

                @if($weedSpecies->common_names_source)

                    <div class="dh-profile-source mt-3">

                        <div class="small fw-semibold mb-1">
                            Common Names Source
                        </div>

                        <div class="small text-muted">
                            {!! nl2br(e($weedSpecies->common_names_source)) !!}
                        </div>

                    </div>

                @endif

            </div>


            {{-- DATA SOURCES --}}
            <div class="dh-card p-4 mb-4">
                <div class="dh-profile-section-heading">
                    <i class="bi bi-book me-2"></i>
                    Data Sources
                </div>
                <ul class="mb-0 ps-3">
                    <li class="mb-2">
                        <cite>Weeds in Irrigated and Rainfed-Lowland Ricefields in the Philippines</cite> (2nd ed.).
                        Philippine Rice Research Institute, Maligaya, Science City of Muñoz, Nueva Ecija.
                    </li>
                    <li>
                        <cite>Weeds of Vegetables and Other Cash Crops in the Philippines</cite>.
                        Philippine Rice Research Institute, Maligaya, Science City of Muñoz, Nueva Ecija.
                    </li>
                </ul>
            </div>

            {{-- DATABASE RECORD --}}
            <div class="dh-card p-4 mb-4">

                <div class="dh-profile-section-heading">
                    <i class="bi bi-database me-2"></i>
                    Database Record
                </div>

                <div class="dh-profile-detail-list">

                    <div class="dh-profile-detail-row">
                        <div class="dh-profile-detail-label">
                            Record ID
                        </div>

                        <div class="dh-profile-detail-value">
                            #{{ $weedSpecies->id }}
                        </div>
                    </div>

                    <div class="dh-profile-detail-row">
                        <div class="dh-profile-detail-label">
                            Date Added
                        </div>

                        <div class="dh-profile-detail-value">
                            {{ $weedSpecies->created_at?->format('M d, Y') ?? 'Not available' }}
                        </div>
                    </div>

                    <div class="dh-profile-detail-row">
                        <div class="dh-profile-detail-label">
                            Last Updated
                        </div>

                        <div class="dh-profile-detail-value">
                            {{ $weedSpecies->updated_at?->format('M d, Y') ?? 'Not available' }}
                        </div>
                    </div>

                    <div class="dh-profile-detail-row">
                        <div class="dh-profile-detail-label">
                            Photos
                        </div>

                        <div class="dh-profile-detail-value">
                            {{ $images->count() }}
                        </div>
                    </div>

                </div>

            </div>


        </div>

    </div>


    {{-- BACK BUTTON --}}
    <div class="mt-2 mb-4">

        <a href="{{ route('species.index') }}"
           class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left me-1"></i>
            Back to Species Explorer

        </a>

    </div>

    {{-- PHOTO LIGHTBOX --}}
    <dialog id="dhPhotoLightbox" class="dh-photo-lightbox">

        <button
            type="button"
            id="dhLightboxClose"
            class="dh-lightbox-close"
            aria-label="Close enlarged photo"
        >
            <i class="bi bi-x-lg"></i>
        </button>

        <div class="dh-lightbox-content">
            <img
                id="dhLightboxImage"
                src=""
                alt=""
            >

            <div
                id="dhLightboxCaption"
                class="dh-lightbox-caption"
            ></div>
        </div>
    </dialog>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const lightbox = document.getElementById('dhPhotoLightbox');
            const lightboxImage = document.getElementById('dhLightboxImage');
            const lightboxCaption = document.getElementById('dhLightboxCaption');
            const closeButton = document.getElementById('dhLightboxClose');

            const triggers = document.querySelectorAll('.dh-lightbox-trigger');

            // Open enlarged photo
            triggers.forEach(function (trigger) {

                trigger.addEventListener('click', function () {

                    const imageSrc = this.dataset.imageSrc;
                    const imageAlt = this.dataset.imageAlt || 'Species photograph';

                    lightboxImage.src = imageSrc;
                    lightboxImage.alt = imageAlt;
                    lightboxCaption.textContent = imageAlt;

                    lightbox.showModal();

                });

            });

            // Close button
            closeButton.addEventListener('click', function () {
                lightbox.close();
            });

            // Close when clicking outside the image
            lightbox.addEventListener('click', function (event) {
                if (event.target === lightbox) {
                    lightbox.close();
                }
            });

            // Clear image when closed
            lightbox.addEventListener('close', function () {

                lightboxImage.src = '';
                lightboxCaption.textContent = '';

            });

        });
    </script>

@endsection
