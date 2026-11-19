<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatabaseBackup;
use App\Services\ActivityLogger;
use App\Services\DatabaseBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseBackupController extends Controller
{
    public function index(): View
    {
        $backups = DatabaseBackup::with('creator')->latest()->paginate(15);

        return view('admin.backups.index', compact('backups'));
    }

    public function store(
        Request $request,
        DatabaseBackupService $backupService,
        ActivityLogger $activityLogger
    ): RedirectResponse {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $backup = $backupService->create($request->user(), 'manual', $validated['notes'] ?? null);
        } catch (RuntimeException $exception) {
            $activityLogger->record(
                'backup_failed',
                'backups',
                null,
                null,
                null,
                $exception->getMessage()
            );

            return back()->withErrors(['backup' => $exception->getMessage()]);
        }

        $activityLogger->record(
            'backup_created',
            'backups',
            $backup,
            null,
            $backup->only(['filename', 'file_size', 'checksum', 'status'])
        );

        return back()->with('status', 'Database backup created and integrity checksum recorded.');
    }

    public function download(DatabaseBackup $databaseBackup): StreamedResponse
    {
        abort_unless(
            $databaseBackup->status === 'completed'
                && str_starts_with($databaseBackup->file_path, 'backups/')
                && Storage::disk('local')->exists($databaseBackup->file_path),
            404
        );

        return Storage::disk('local')->download($databaseBackup->file_path, $databaseBackup->filename);
    }

    public function restore(
        Request $request,
        DatabaseBackup $databaseBackup,
        DatabaseBackupService $backupService,
        ActivityLogger $activityLogger
    ): RedirectResponse {
        $request->validate([
            'confirm_restore' => ['required', 'accepted'],
            'password' => ['required', 'current_password'],
        ]);

        $operatorId = $request->user()->id;
        $operatorEmail = $request->user()->email;

        try {
            $safetyBackup = $backupService->restore($databaseBackup, $request->user());
        } catch (RuntimeException $exception) {
            $activityLogger->record('restore_failed', 'backups', $databaseBackup, null, null, $exception->getMessage());

            return back()->withErrors(['restore' => $exception->getMessage()]);
        }

        $description = "Backup {$databaseBackup->filename} restored by user {$operatorId} ({$operatorEmail}).";
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $activityLogger->record(
            'restored',
            'backups',
            null,
            null,
            [
                'backup_filename' => $databaseBackup->filename,
                'safety_backup_filename' => $safetyBackup->filename,
            ],
            $description
        );

        return redirect()->route('login')
            ->with('status', 'Database restored. A pre-restore safety backup was saved.');
    }
}
