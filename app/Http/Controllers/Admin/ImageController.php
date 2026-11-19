<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpeciesImage;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ImageController extends Controller
{
    public function index(): View
    {
        $images = SpeciesImage::whereHas('species')->with('species')
            ->latest()
            ->paginate(24);

        return view('admin.images.index', compact('images'));
    }

    public function destroy(
        SpeciesImage $image,
        ActivityLogger $activityLogger
    ): RedirectResponse {
        $species = $image->species;
        $oldValues = $image->only(['image_path', 'caption', 'alt_text', 'is_primary']);
        $path = $image->image_path;

        DB::transaction(function () use ($image, $species, $activityLogger, $oldValues) {
            $image->delete();

            if ($image->is_primary) {
                $species->images()->orderBy('sort_order')->orderBy('id')
                    ->first()?->update(['is_primary' => true]);
            }

            $activityLogger->record('deleted', 'images', $image, $oldValues);
        });

        if (Storage::disk('public')->exists($path) && ! Storage::disk('public')->delete($path)) {
            throw new \RuntimeException("Unable to remove species image: {$path}");
        }

        return redirect()->route('admin.images.index')->with('status', 'Species image deleted.');
    }
}
