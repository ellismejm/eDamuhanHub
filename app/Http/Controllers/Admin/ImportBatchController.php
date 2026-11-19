<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportBatch;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class ImportBatchController extends Controller
{
    public function index(): View
    {
        $batches = ImportBatch::with('initiator')->latest()->paginate(15);

        return view('admin.imports.index', compact('batches'));
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $exitCode = Artisan::call('weeds:import', [
            '--initiated-by' => $request->user()->id,
        ]);

        if ($exitCode !== 0) {
            return redirect()->route('admin.imports.index')
                ->withErrors(['import' => 'The dataset import failed. Review the latest batch and application log.']);
        }

        $batch = ImportBatch::latest()->first();
        $activityLogger->record(
            'imported',
            'imports',
            $batch,
            null,
            $batch?->only(['status', 'total_rows', 'successful_rows', 'failed_rows'])
        );

        return redirect()->route('admin.imports.index')
            ->with('status', 'The bundled weed dataset import completed.');
    }
}
