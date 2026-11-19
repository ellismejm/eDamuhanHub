<?php

namespace App\Services;

use App\Models\DatabaseBackup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class DatabaseBackupService
{
    public function create(?User $user, string $type = 'manual', ?string $notes = null): DatabaseBackup
    {
        $this->assertMySqlConnection();

        $filename = now()->format('Ymd_His').'_'.Str::random(8).'.sql';
        $path = 'backups/'.$filename;
        $backup = DatabaseBackup::create([
            'filename' => $filename,
            'file_path' => $path,
            'backup_type' => $type,
            'status' => 'processing',
            'created_by' => $user?->id,
            'notes' => $notes,
        ]);

        try {
            $optionsFile = $this->createClientOptionsFile();
            try {
                $process = new Process([
                    'mysqldump',
                    '--defaults-extra-file='.$optionsFile,
                    '--single-transaction',
                    '--quick',
                    '--skip-lock-tables',
                    '--add-drop-table',
                    '--default-character-set=utf8mb4',
                    config('database.connections.mysql.database'),
                ]);
                $process->setTimeout(600);
                $process->run();
            } finally {
                if (isset($optionsFile) && file_exists($optionsFile)) {
                    unlink($optionsFile);
                }
            }

            if (! $process->isSuccessful() || trim($process->getOutput()) === '') {
                throw new RuntimeException('MySQL database backup failed.');
            }

            $contents = $process->getOutput();
            if (! Storage::disk('local')->put($path, $contents)) {
                throw new RuntimeException('The database backup file could not be saved.');
            }

            $backup->update([
                'file_size' => strlen($contents),
                'checksum' => hash('sha256', $contents),
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return $backup;
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            $backup->update([
                'status' => 'failed',
                'notes' => 'Backup could not be completed.',
            ]);

            throw $exception;
        }
    }

    public function restore(DatabaseBackup $backup, ?User $user): DatabaseBackup
    {
        $this->assertMySqlConnection();
        $this->assertValidBackup($backup);
        $safetyBackup = $this->create($user, 'pre_restore', 'Automatic backup before restoring '.$backup->filename);

        try {
            $this->restoreFile($backup);
            $this->persistBackupRecord($backup);
            $safetyBackup = $this->persistBackupRecord($safetyBackup);
        } catch (Throwable $exception) {
            try {
                $this->restoreFile($safetyBackup);
                $this->persistBackupRecord($backup);
                $safetyBackup = $this->persistBackupRecord($safetyBackup);
            } catch (Throwable $rollbackException) {
                throw new RuntimeException(
                    'Restore failed and the automatic rollback could not complete. Database recovery is required.',
                    previous: $rollbackException
                );
            }

            throw new RuntimeException(
                'Restore failed. The database was returned to its pre-restore state.',
                previous: $exception
            );
        }

        return $safetyBackup;
    }

    private function restoreFile(DatabaseBackup $backup): void
    {
        $this->assertValidBackup($backup);

        $optionsFile = $this->createClientOptionsFile();
        try {
            $process = new Process([
                'mysql',
                '--defaults-extra-file='.$optionsFile,
                '--default-character-set=utf8mb4',
                config('database.connections.mysql.database'),
            ]);
            $process->setTimeout(600);
            $process->setInput(Storage::disk('local')->get($backup->file_path));
            $process->run();
        } finally {
            if (file_exists($optionsFile)) {
                unlink($optionsFile);
            }
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException('MySQL database restore failed.');
        }
    }

    private function assertMySqlConnection(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            throw new RuntimeException('Database backup and restore are currently supported only for MySQL.');
        }
    }

    private function assertValidBackup(DatabaseBackup $backup): void
    {
        if (
            $backup->status !== 'completed'
            || ! str_starts_with($backup->file_path, 'backups/')
            || ! Storage::disk('local')->exists($backup->file_path)
        ) {
            throw new RuntimeException('The selected database backup is unavailable.');
        }

        $contents = Storage::disk('local')->get($backup->file_path);
        if (! hash_equals((string) $backup->checksum, hash('sha256', $contents))) {
            throw new RuntimeException('The selected database backup failed its integrity check.');
        }
    }

    private function persistBackupRecord(DatabaseBackup $backup): DatabaseBackup
    {
        $attributes = $backup->only([
            'filename',
            'file_size',
            'checksum',
            'backup_type',
            'status',
            'created_by',
            'notes',
            'completed_at',
        ]);

        if (
            $attributes['created_by'] !== null
            && ! User::query()->whereKey($attributes['created_by'])->exists()
        ) {
            $attributes['created_by'] = null;
        }

        return DatabaseBackup::query()->updateOrCreate(
            ['file_path' => $backup->file_path],
            $attributes
        );
    }

    private function createClientOptionsFile(): string
    {
        $connection = config('database.connections.mysql');
        $lines = ['[client]'];

        $keys = filled($connection['unix_socket'] ?? null)
            ? ['unix_socket', 'username', 'password']
            : ['host', 'port', 'username', 'password'];

        foreach ($keys as $key) {
            $value = (string) ($connection[$key] ?? '');
            if (str_contains($value, "\n") || str_contains($value, "\r")) {
                throw new RuntimeException('Database connection settings cannot be used by the backup utility.');
            }

            $lines[] = match ($key) {
                'unix_socket' => 'socket='.$this->escapeMySqlOption($value),
                'username' => 'user='.$this->escapeMySqlOption($value),
                'password' => 'password='.$this->escapeMySqlOption($value),
                default => $key.'='.$this->escapeMySqlOption($value),
            };
        }

        $path = tempnam(sys_get_temp_dir(), 'edamuhan-db-');
        if ($path === false || file_put_contents($path, implode("\n", $lines)."\n") === false) {
            if ($path !== false && file_exists($path)) {
                unlink($path);
            }

            throw new RuntimeException('Unable to prepare private MySQL client settings.');
        }

        chmod($path, 0600);

        return $path;
    }

    private function escapeMySqlOption(string $value): string
    {
        return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
    }
}
