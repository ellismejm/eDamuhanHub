<?php

namespace App\Http\Controllers;

use App\Models\Habitat;
use App\Models\LifeCycle;
use App\Models\LocalName;
use App\Models\WeedClassification;
use App\Models\WeedFamily;
use App\Models\WeedSpecies;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class WeedSpeciesController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $classificationId = $request->input('classification');

        $speciesQuery = WeedSpecies::with([
            'classification',
            'family',
            'primaryImage',
            'localNames',
        ]);

        if ($search !== '') {
            $speciesQuery->where(function ($query) use ($search) {
                $query->where('scientific_name', 'like', "%{$search}%")
                    ->orWhere('taxonomic_author', 'like', "%{$search}%")
                    ->orWhere('eppo_code', 'like', "%{$search}%")
                    ->orWhere('common_names_source', 'like', "%{$search}%")
                    ->orWhereHas('family', function ($familyQuery) use ($search) {
                        $familyQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('localNames', function ($localNameQuery) use ($search) {
                        $localNameQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($classificationId && is_numeric($classificationId)) {
            $speciesQuery->where('classification_id', $classificationId);
        }

        $species = $speciesQuery->orderBy('scientific_name')
            ->paginate(12)
            ->withQueryString();

        $classifications = WeedClassification::withCount('species')
            ->orderBy('name')
            ->get();

        $totalSpecies = WeedSpecies::query()->count();

        return view('species.index', compact(
            'species',
            'search',
            'classificationId',
            'classifications',
            'totalSpecies'
        ));
    }

    public function show(WeedSpecies $weedSpecies): View
    {
        $weedSpecies->load([
            'classification',
            'family',
            'habitat',
            'lifeCycle',
            'localNames',
            'images',
            'primaryImage',
        ]);

        $images = $weedSpecies->images
            ->sortBy(fn ($image) => [
                $image->is_primary ? 0 : 1,
                $image->sort_order,
                $image->id,
            ])
            ->values();

        return view('species.show', compact('weedSpecies', 'images'));
    }

    public function create(): View
    {
        return view('species.form', [
            'weedSpecies' => new WeedSpecies,
            'classifications' => WeedClassification::orderBy('name')->get(),
            'families' => WeedFamily::orderBy('name')->get(),
            'habitats' => Habitat::orderBy('name')->get(),
            'lifeCycles' => LifeCycle::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $validated = $request->validate($this->speciesRules());
        $uploadedPaths = $this->storeImages($request);

        try {
            $weedSpecies = DB::transaction(function () use ($validated, $uploadedPaths, $activityLogger) {
                $weedSpecies = WeedSpecies::create($this->speciesAttributes($validated, true));

                $this->syncLocalNames($weedSpecies, $validated['common_names_source'] ?? '');
                $this->attachImages($weedSpecies, $uploadedPaths);
                $activityLogger->record(
                    'created',
                    'species',
                    $weedSpecies,
                    null,
                    $this->auditedSpeciesValues($weedSpecies)
                );

                return $weedSpecies;
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($uploadedPaths);

            throw $exception;
        }

        return redirect()->route('species.show', $weedSpecies)
            ->with('status', 'Weed species created.');
    }

    public function edit(WeedSpecies $weedSpecies): View
    {
        $weedSpecies->load('images');

        return view('species.form', [
            'weedSpecies' => $weedSpecies,
            'classifications' => WeedClassification::orderBy('name')->get(),
            'families' => WeedFamily::orderBy('name')->get(),
            'habitats' => Habitat::orderBy('name')->get(),
            'lifeCycles' => LifeCycle::orderBy('name')->get(),
        ]);
    }

    public function update(
        Request $request,
        WeedSpecies $weedSpecies,
        ActivityLogger $activityLogger
    ): RedirectResponse {
        $validated = $request->validate($this->speciesRules($weedSpecies));
        $uploadedPaths = $this->storeImages($request);
        $oldValues = $this->auditedSpeciesValues($weedSpecies);
        $removedImagePaths = [];

        try {
            DB::transaction(function () use (
                $weedSpecies,
                $validated,
                $uploadedPaths,
                $activityLogger,
                $oldValues,
                &$removedImagePaths
            ) {
                $weedSpecies->update($this->speciesAttributes($validated, false));
                $this->syncLocalNames($weedSpecies, $validated['common_names_source'] ?? '');

                $removeIds = $validated['remove_images'] ?? [];
                if ($removeIds !== []) {
                    $images = $weedSpecies->images()->whereIn('id', $removeIds)->get();
                    $removedImagePaths = $images->pluck('image_path')->all();
                    $images->each->delete();
                }

                if ($uploadedPaths !== []) {
                    $this->attachImages($weedSpecies, $uploadedPaths);
                }

                $primaryImageId = $validated['primary_image_id'] ?? null;
                if ($primaryImageId !== null) {
                    $weedSpecies->images()->update(['is_primary' => false]);
                    $weedSpecies->images()->whereKey($primaryImageId)->update(['is_primary' => true]);
                }

                if (! $weedSpecies->images()->where('is_primary', true)->exists()) {
                    $weedSpecies->images()->orderBy('sort_order')->orderBy('id')
                        ->first()?->update(['is_primary' => true]);
                }

                $activityLogger->record(
                    'updated',
                    'species',
                    $weedSpecies,
                    $oldValues,
                    $this->auditedSpeciesValues($weedSpecies)
                );
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($uploadedPaths);

            throw $exception;
        }

        foreach ($removedImagePaths as $path) {
            if (Storage::disk('public')->exists($path) && ! Storage::disk('public')->delete($path)) {
                throw new \RuntimeException('A species image was removed from the database, but its file could not be deleted.');
            }
        }

        return redirect()->route('species.show', $weedSpecies)
            ->with('status', 'Weed species updated.');
    }

    public function destroy(WeedSpecies $weedSpecies, ActivityLogger $activityLogger): RedirectResponse
    {
        $values = $this->auditedSpeciesValues($weedSpecies);

        DB::transaction(function () use ($weedSpecies, $activityLogger, $values) {
            $weedSpecies->delete();
            $activityLogger->record('deleted', 'species', $weedSpecies, $values);
        });

        return redirect()->route('species.index')->with('status', 'Weed species archived.');
    }

    /**
     * @return array<string, array<mixed>>
     */
    private function speciesRules(?WeedSpecies $weedSpecies = null): array
    {
        $rules = [
            'scientific_name' => ['required', 'string', 'max:255'],
            'taxonomic_author' => ['nullable', 'string', 'max:255'],
            'eppo_code' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('weed_species', 'eppo_code')->ignore($weedSpecies?->id),
            ],
            'classification_id' => ['required', 'exists:weed_classifications,id'],
            'family_id' => ['required', 'exists:weed_families,id'],
            'habitat_id' => ['nullable', 'exists:habitats,id'],
            'life_cycle_id' => ['nullable', 'exists:life_cycles,id'],
            'common_names_source' => ['nullable', 'string', 'max:10000'],
            'reproduction_description' => ['nullable', 'string', 'max:10000'],
            'characteristics' => ['nullable', 'string', 'max:50000'],
            'impacts' => ['nullable', 'string', 'max:50000'],
            'weakness' => ['nullable', 'string', 'max:50000'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ];

        if ($weedSpecies !== null) {
            $rules['remove_images'] = ['nullable', 'array'];
            $rules['remove_images.*'] = [
                'integer',
                Rule::exists('species_images', 'id')->where('weed_species_id', $weedSpecies->id),
            ];
            $rules['primary_image_id'] = [
                'nullable',
                'integer',
                Rule::exists('species_images', 'id')->where('weed_species_id', $weedSpecies->id),
            ];
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function speciesAttributes(array $validated, bool $isNew): array
    {
        return [
            'scientific_name' => $validated['scientific_name'],
            'taxonomic_author' => $validated['taxonomic_author'] ?? null,
            'eppo_code' => $validated['eppo_code'] ?? null,
            'classification_id' => $validated['classification_id'],
            'family_id' => $validated['family_id'],
            'habitat_id' => $validated['habitat_id'] ?? null,
            'life_cycle_id' => $validated['life_cycle_id'] ?? null,
            'common_names_source' => $validated['common_names_source'] ?? null,
            'reproduction_description' => $validated['reproduction_description'] ?? null,
            'characteristics' => $validated['characteristics'] ?? null,
            'impacts' => $validated['impacts'] ?? null,
            'weakness' => $validated['weakness'] ?? null,
            'updated_by' => Auth::id(),
            ...($isNew ? ['created_by' => Auth::id()] : []),
        ];
    }

    private function syncLocalNames(WeedSpecies $weedSpecies, string $names): void
    {
        $localNames = collect(explode(',', $names))
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique()
            ->map(fn (string $name) => LocalName::firstOrCreate(['name' => $name])->id)
            ->all();

        $weedSpecies->localNames()->sync($localNames);
    }

    /**
     * @return list<string>
     */
    private function storeImages(Request $request): array
    {
        $paths = [];

        try {
            foreach ($request->file('images', []) as $image) {
                $path = $image->store('weed-species', 'public');
                if ($path === false) {
                    throw new \RuntimeException('Unable to store an uploaded species image.');
                }
                $paths[] = $path;
            }
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($paths);

            throw $exception;
        }

        return $paths;
    }

    /**
     * @param  list<string>  $paths
     */
    private function attachImages(WeedSpecies $weedSpecies, array $paths): void
    {
        $hasPrimaryImage = $weedSpecies->images()->where('is_primary', true)->exists();
        $sortOrder = (int) $weedSpecies->images()->max('sort_order') + 1;

        foreach ($paths as $index => $path) {
            $weedSpecies->images()->create([
                'image_path' => $path,
                'original_filename' => basename($path),
                'alt_text' => $weedSpecies->scientific_name,
                'is_primary' => ! $hasPrimaryImage && $index === 0,
                'sort_order' => $sortOrder + $index,
                'uploaded_by' => Auth::id(),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function auditedSpeciesValues(WeedSpecies $weedSpecies): array
    {
        return $weedSpecies->only([
            'scientific_name',
            'taxonomic_author',
            'eppo_code',
            'classification_id',
            'family_id',
            'habitat_id',
            'life_cycle_id',
            'common_names_source',
            'reproduction_description',
            'characteristics',
            'impacts',
            'weakness',
        ]);
    }
}
