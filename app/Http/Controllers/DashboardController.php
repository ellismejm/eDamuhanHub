<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
use App\Models\LifeCycle;
use App\Models\SpeciesImage;
use App\Models\User;
use App\Models\WeedClassification;
use App\Models\WeedFamily;
use App\Models\WeedSpecies;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $speciesCount = WeedSpecies::query()->count();
        $classificationCount = WeedClassification::query()->count();
        $familyCount = WeedFamily::query()->count();
        $imageCount = SpeciesImage::query()->whereHas('species')->count();
        $userCount = User::query()->where('is_active', true)->count();
        $lifeCycleCount = LifeCycle::query()->count();

        $recentSpecies = WeedSpecies::with([
            'classification',
            'family',
            'primaryImage',
        ])->latest()->take(5)->get();

        $classificationStats = WeedClassification::withCount('species')
            ->orderBy('name')
            ->get();

        $latestImport = ImportBatch::latest()->first();

        return view('dashboard.index', compact(
            'speciesCount',
            'classificationCount',
            'familyCount',
            'imageCount',
            'userCount',
            'lifeCycleCount',
            'recentSpecies',
            'classificationStats',
            'latestImport'
        ));
    }
}
