@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="mb-4">
        <h1 class="dh-page-title">{{ $title }}</h1>
        <p class="dh-page-subtitle mb-0">Maintain shared reference records used by weed species.</p>
    </div>

    @if($errors->has('reference'))
        <div class="alert alert-danger">{{ $errors->first('reference') }}</div>
    @endif

    <div class="dh-card p-3 p-md-4 mb-4">
        <h2 class="dh-section-title mb-3">Add {{ str($type)->replace('-', ' ')->singular()->title() }}</h2>
        <form method="POST" action="{{ route('admin.references.store', $type) }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-4">
                <label for="new-name" class="form-label">Name</label>
                <input id="new-name" name="name" value="{{ old('name') }}" class="form-control" required maxlength="255">
            </div>
            <div class="col-md-6">
                <label for="new-description" class="form-label">Description</label>
                <input id="new-description" name="description" value="{{ old('description') }}" class="form-control" maxlength="2000">
            </div>
            <div class="col-md-2 d-grid"><button class="btn dh-btn-primary" type="submit">Add</button></div>
            @error('name')<div class="col-12 text-danger small">{{ $message }}</div>@enderror
            @error('description')<div class="col-12 text-danger small">{{ $message }}</div>@enderror
        </form>
    </div>

    <div class="dh-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Name and description</th><th>Species using record</th><th>Update</th><th></th></tr></thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr>
                            <td style="min-width:260px">
                                <form id="reference-{{ $entry->id }}" method="POST"
                                      action="{{ route('admin.references.update', [$type, $entry->id]) }}">
                                    @csrf
                                    @method('PUT')
                                    <label class="visually-hidden" for="name-{{ $entry->id }}">Name</label>
                                    <input id="name-{{ $entry->id }}" name="name" value="{{ $entry->name }}"
                                           class="form-control form-control-sm mb-2" required maxlength="255">
                                    <label class="visually-hidden" for="description-{{ $entry->id }}">Description</label>
                                    <input id="description-{{ $entry->id }}" name="description" value="{{ $entry->description }}"
                                           class="form-control form-control-sm" maxlength="2000">
                                </form>
                            </td>
                            <td>{{ $entry->species_count }}</td>
                            <td><button class="btn btn-sm btn-outline-success" type="submit" form="reference-{{ $entry->id }}">Save</button></td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.references.destroy', [$type, $entry->id]) }}"
                                      onsubmit="return confirm('Delete this reference record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit"
                                            @disabled($entry->species_count > 0) title="{{ $entry->species_count ? 'Used by a species' : 'Delete record' }}">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No reference records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $entries->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection
