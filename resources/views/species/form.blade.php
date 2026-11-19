@extends('layouts.app')

@section('title', $weedSpecies->exists ? 'Edit Weed Species' : 'Add Weed Species')

@section('content')
    @php($isEditing = $weedSpecies->exists)

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dh-page-title">{{ $isEditing ? 'Edit Weed Species' : 'Add Weed Species' }}</h1>
            <p class="dh-page-subtitle mb-0">Manage verified species details and reference information.</p>
        </div>
        <a href="{{ $isEditing ? route('species.show', $weedSpecies) : route('species.index') }}"
           class="btn btn-outline-secondary">Cancel</a>
    </div>

    <form method="POST"
          action="{{ $isEditing ? route('species.update', $weedSpecies) : route('species.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if($isEditing)
            @method('PUT')
        @endif

        <div class="dh-card p-3 p-md-4 mb-4">
            <h2 class="dh-section-title mb-3">Identification</h2>
            <div class="row g-3">
                <div class="col-md-8">
                    <label for="scientific_name" class="form-label">Scientific name <span class="text-danger">*</span></label>
                    <input id="scientific_name" name="scientific_name" value="{{ old('scientific_name', $weedSpecies->scientific_name) }}"
                           class="form-control @error('scientific_name') is-invalid @enderror" required maxlength="255">
                    @error('scientific_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="taxonomic_author" class="form-label">Taxonomic author</label>
                    <input id="taxonomic_author" name="taxonomic_author" value="{{ old('taxonomic_author', $weedSpecies->taxonomic_author) }}"
                           class="form-control @error('taxonomic_author') is-invalid @enderror" maxlength="255">
                    @error('taxonomic_author')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="eppo_code" class="form-label">EPPO code</label>
                    <input id="eppo_code" name="eppo_code" value="{{ old('eppo_code', $weedSpecies->eppo_code) }}"
                           class="form-control @error('eppo_code') is-invalid @enderror" maxlength="30">
                    @error('eppo_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="classification_id" class="form-label">Classification <span class="text-danger">*</span></label>
                    <select id="classification_id" name="classification_id" class="form-select @error('classification_id') is-invalid @enderror" required>
                        <option value="">Choose classification</option>
                        @foreach($classifications as $classification)
                            <option value="{{ $classification->id }}" @selected(old('classification_id', $weedSpecies->classification_id) == $classification->id)>
                                {{ $classification->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('classification_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="family_id" class="form-label">Family <span class="text-danger">*</span></label>
                    <select id="family_id" name="family_id" class="form-select @error('family_id') is-invalid @enderror" required>
                        <option value="">Choose family</option>
                        @foreach($families as $family)
                            <option value="{{ $family->id }}" @selected(old('family_id', $weedSpecies->family_id) == $family->id)>
                                {{ $family->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('family_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="habitat_id" class="form-label">Habitat</label>
                    <select id="habitat_id" name="habitat_id" class="form-select @error('habitat_id') is-invalid @enderror">
                        <option value="">Not specified</option>
                        @foreach($habitats as $habitat)
                            <option value="{{ $habitat->id }}" @selected(old('habitat_id', $weedSpecies->habitat_id) == $habitat->id)>
                                {{ $habitat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('habitat_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="life_cycle_id" class="form-label">Life cycle</label>
                    <select id="life_cycle_id" name="life_cycle_id" class="form-select @error('life_cycle_id') is-invalid @enderror">
                        <option value="">Not specified</option>
                        @foreach($lifeCycles as $lifeCycle)
                            <option value="{{ $lifeCycle->id }}" @selected(old('life_cycle_id', $weedSpecies->life_cycle_id) == $lifeCycle->id)>
                                {{ $lifeCycle->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('life_cycle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="common_names_source" class="form-label">Common/local names</label>
                    <textarea id="common_names_source" name="common_names_source" rows="2"
                              class="form-control @error('common_names_source') is-invalid @enderror"
                              aria-describedby="common-names-help">{{ old('common_names_source', $weedSpecies->common_names_source) }}</textarea>
                    <div id="common-names-help" class="form-text">Separate local names with commas.</div>
                    @error('common_names_source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="reproduction_description" class="form-label">Reproduction and spread</label>
                    <textarea id="reproduction_description" name="reproduction_description" rows="3"
                              class="form-control @error('reproduction_description') is-invalid @enderror">{{ old('reproduction_description', $weedSpecies->reproduction_description) }}</textarea>
                    @error('reproduction_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="dh-card p-3 p-md-4 mb-4">
            <h2 class="dh-section-title mb-3">Description and impacts</h2>
            <div class="row g-3">
                @foreach([
                    'characteristics' => 'Characteristics',
                    'impacts' => 'Impacts',
                    'weakness' => 'Weaknesses and management notes',
                ] as $field => $label)
                    <div class="col-12">
                        <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                        <textarea id="{{ $field }}" name="{{ $field }}" rows="4"
                                  class="form-control @error($field) is-invalid @enderror">{{ old($field, $weedSpecies->{$field}) }}</textarea>
                        @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endforeach
            </div>
        </div>

        <div class="dh-card p-3 p-md-4 mb-4">
            <h2 class="dh-section-title mb-1">Species photographs</h2>
            <p class="small text-muted mb-3">Upload JPG, PNG, or WebP images, up to 8 MB each.</p>

            @if($isEditing && $weedSpecies->images->isNotEmpty())
                <div class="row g-3 mb-3">
                    @foreach($weedSpecies->images->sortBy('sort_order') as $image)
                        <div class="col-6 col-md-4 col-xl-3">
                            <div class="border rounded p-2 h-100">
                                <img src="{{ asset('storage/' . $image->image_path) }}"
                                     alt="{{ $image->alt_text ?? $weedSpecies->scientific_name }}"
                                     class="w-100 rounded mb-2" style="height:140px;object-fit:contain;background:#f5f6f5;">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="primary_image_id"
                                           id="primary_image_{{ $image->id }}" value="{{ $image->id }}"
                                           @checked(old('primary_image_id', $image->is_primary ? $image->id : null) == $image->id)>
                                    <label class="form-check-label small" for="primary_image_{{ $image->id }}">Main photo</label>
                                </div>
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" name="remove_images[]"
                                           id="remove_image_{{ $image->id }}" value="{{ $image->id }}"
                                           @checked(in_array($image->id, old('remove_images', [])))>
                                    <label class="form-check-label small text-danger" for="remove_image_{{ $image->id }}">Remove photo</label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <label for="images" class="form-label">Add photos</label>
            <input id="images" name="images[]" type="file" multiple accept="image/jpeg,image/png,image/webp"
                   class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror">
            @error('images')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @error('images.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex flex-wrap gap-2 justify-content-end mb-4">
            <a href="{{ $isEditing ? route('species.show', $weedSpecies) : route('species.index') }}"
               class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn dh-btn-primary">
                {{ $isEditing ? 'Save changes' : 'Create species' }}
            </button>
        </div>
    </form>
@endsection
