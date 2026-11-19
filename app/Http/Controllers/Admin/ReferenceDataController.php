<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Habitat;
use App\Models\LifeCycle;
use App\Models\WeedClassification;
use App\Models\WeedFamily;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReferenceDataController extends Controller
{
    private const TYPES = [
        'classifications' => WeedClassification::class,
        'families' => WeedFamily::class,
        'habitats' => Habitat::class,
        'life-cycles' => LifeCycle::class,
    ];

    public function index(Request $request, string $type): View
    {
        $model = $this->modelFor($type);
        $entries = $model::withCount('species')->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.references.index', [
            'type' => $type,
            'title' => str($type)->replace('-', ' ')->plural()->title()->toString(),
            'entries' => $entries,
        ]);
    }

    public function store(Request $request, string $type, ActivityLogger $activityLogger): RedirectResponse
    {
        $model = $this->modelFor($type);
        $validated = $request->validate($this->rules($model));
        $entry = DB::transaction(function () use ($model, $validated, $activityLogger) {
            $entry = $model::create($validated);
            $activityLogger->record('created', 'reference_data', $entry, null, $validated);

            return $entry;
        });

        return redirect()->route('admin.references.index', $type)->with('status', 'Reference record added.');
    }

    public function update(
        Request $request,
        string $type,
        int $reference,
        ActivityLogger $activityLogger
    ): RedirectResponse {
        $model = $this->modelFor($type);
        $entry = $model::query()->findOrFail($reference);
        $validated = $request->validate($this->rules($model, $entry));
        $oldValues = $entry->only(['name', 'description']);
        DB::transaction(function () use ($entry, $validated, $activityLogger, $oldValues) {
            $entry->update($validated);
            $activityLogger->record('updated', 'reference_data', $entry, $oldValues, $validated);
        });

        return redirect()->route('admin.references.index', $type)->with('status', 'Reference record updated.');
    }

    public function destroy(string $type, int $reference, ActivityLogger $activityLogger): RedirectResponse
    {
        $model = $this->modelFor($type);
        $entry = $model::query()->findOrFail($reference);

        if ($entry->species()->withTrashed()->exists()) {
            return back()->withErrors([
                'reference' => 'This record is used by one or more weed species and cannot be deleted.',
            ]);
        }

        $oldValues = $entry->only(['name', 'description']);
        DB::transaction(function () use ($entry, $activityLogger, $oldValues) {
            $entry->delete();
            $activityLogger->record('deleted', 'reference_data', $entry, $oldValues);
        });

        return redirect()->route('admin.references.index', $type)->with('status', 'Reference record deleted.');
    }

    /**
     * @param  class-string<Model>  $model
     * @return array<string, array<mixed>>
     */
    private function rules(string $model, ?Model $entry = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique((new $model)->getTable(), 'name')->ignore($entry?->getKey()),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return class-string<Model>
     */
    private function modelFor(string $type): string
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        return self::TYPES[$type];
    }
}
