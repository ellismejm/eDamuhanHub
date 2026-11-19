@extends('layouts.app')

@section('title', 'Image Management')

@section('content')
    <div class="mb-4">
        <h1 class="dh-page-title">Image Management</h1>
        <p class="dh-page-subtitle mb-0">Review images attached to species profiles. Add or select a main photo from the species editor.</p>
    </div>

    <div class="row g-3">
        @forelse($images as $image)
            <div class="col-6 col-md-4 col-xl-3">
                <div class="dh-card p-2 h-100">
                    <a href="{{ route('species.show', $image->species) }}" class="text-decoration-none">
                        <img src="{{ asset('storage/' . $image->image_path) }}"
                             alt="{{ $image->alt_text ?? $image->species->scientific_name }}"
                             class="w-100 rounded" style="height:170px;object-fit:contain;background:#f5f6f5;">
                        <div class="fw-semibold mt-2">{{ $image->species->scientific_name }}</div>
                    </a>
                    <div class="small text-muted">{{ $image->is_primary ? 'Main photo' : 'Gallery photo' }}</div>
                    <form method="POST" action="{{ route('admin.images.destroy', $image) }}" class="mt-2"
                          onsubmit="return confirm('Delete this photograph from the species profile?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger w-100" type="submit">Delete image</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="dh-card text-center p-5 text-muted">No species images found.</div></div>
        @endforelse
    </div>

    <div class="mt-4">{{ $images->links('pagination::bootstrap-5') }}</div>
@endsection
