<?php

namespace App\Console\Commands;

use App\Models\Habitat;
use App\Models\ImportBatch;
use App\Models\LifeCycle;
use App\Models\LocalName;
use App\Models\SpeciesImage;
use App\Models\WeedClassification;
use App\Models\WeedFamily;
use App\Models\WeedSpecies;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImportWeedSpecies extends Command
{
    protected $signature = 'weeds:import
        {--dry-run : Validate the dataset without importing anything}
        {--update-existing : Update species that already exist by EPPO code}
        {--initiated-by= : User ID that initiated the import}';

    protected $description = 'Import the original eDamuhan weed species dataset';

    private string $jsonPath;

    private string $photosDirectory;

    public function handle(): int
    {
        $this->jsonPath = database_path('imports/weed_species_import.json');
        $this->photosDirectory = database_path('imports/photos');

        $this->info('eDamuhanHub Weed Species Importer');
        $this->newLine();

        // 1. Validate the JSON and photographs first.
        $validation = $this->validateDataset();

        if (! $validation['valid']) {
            $this->error('Preflight validation failed.');

            foreach ($validation['errors'] as $error) {
                $this->line(' - '.$error);
            }

            return self::FAILURE;
        }

        $records = $validation['records'];

        $this->info('Preflight validation passed.');
        $this->line('Species records: '.count($records));
        $this->line('Unique EPPO codes: '.count($validation['eppoCodes']));
        $this->line('Referenced photographs: '.count($records));
        $this->newLine();

        // Dry-run stops here. No DB writes and no image copying.
        if ($this->option('dry-run')) {
            $this->info('DRY RUN completed successfully.');
            $this->comment('No database records or files were changed.');

            return self::SUCCESS;
        }

        // 2. Create the import batch.
        $batch = ImportBatch::create([
            'source_filename' => 'weed_species_import.json',
            'source_format' => 'JSON',
            'status' => 'processing',
            'total_rows' => count($records),
            'successful_rows' => 0,
            'failed_rows' => 0,
            'initiated_by' => $this->option('initiated-by'),
            'started_at' => now(),
        ]);

        $successful = 0;
        $failed = 0;
        $skipped = 0;
        $errors = [];

        $this->info("Import batch #{$batch->id} started.");
        $this->newLine();

        // 3. Import each species independently.
        foreach ($records as $index => $record) {
            $rowNumber = $index + 1;
            $eppoCode = $record['eppoCode'] ?? 'UNKNOWN';
            $scientificName = $record['title'] ?? 'Unknown species';

            try {
                $existing = WeedSpecies::withTrashed()
                    ->where('eppo_code', $eppoCode)
                    ->first();

                // Do not overwrite existing species unless explicitly requested.
                if ($existing && ! $this->option('update-existing')) {
                    $skipped++;

                    $this->line(
                        "<comment>SKIPPED</comment> {$scientificName} ({$eppoCode}) - already exists"
                    );

                    continue;
                }

                $this->importRecord($record, $batch->id, $existing);

                $successful++;

                $this->line(
                    "<info>IMPORTED</info> {$scientificName} ({$eppoCode})"
                );
            } catch (Throwable $exception) {
                $failed++;

                $errors[] = [
                    'row' => $rowNumber,
                    'eppo_code' => $eppoCode,
                    'scientific_name' => $scientificName,
                    'error' => $exception->getMessage(),
                ];

                $this->error(
                    "FAILED: {$scientificName} ({$eppoCode}) - {$exception->getMessage()}"
                );
            }
        }

        // 4. Update the import batch summary.
        $batch->update([
            'successful_rows' => $successful,
            'failed_rows' => $failed,
            'error_summary' => ! empty($errors) ? json_encode($errors) : null,
            'status' => $failed === 0
                ? 'completed'
                : ($successful > 0 ? 'completed_with_errors' : 'failed'),
            'completed_at' => now(),
        ]);

        // 5. Display summary.
        $this->newLine();
        $this->info('Import finished.');
        $this->table(
            ['Result', 'Count'],
            [
                ['Imported successfully', $successful],
                ['Skipped (already existed)', $skipped],
                ['Failed', $failed],
                ['Total source records', count($records)],
                ['Import batch ID', $batch->id],
            ]
        );

        if ($failed > 0) {
            $this->warn('Some records failed. Review the errors above and the import batch.');

            return self::FAILURE;
        }

        $this->info('All import operations completed successfully.');

        return self::SUCCESS;
    }

    /**
     * Validate the source JSON, fields, EPPO codes, and image paths.
     */
    private function validateDataset(): array
    {
        $errors = [];
        $records = [];
        $eppoCodes = [];

        if (! file_exists($this->jsonPath)) {
            return [
                'valid' => false,
                'errors' => ['JSON dataset not found: '.$this->jsonPath],
                'records' => [],
                'eppoCodes' => [],
            ];
        }

        $json = file_get_contents($this->jsonPath);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'valid' => false,
                'errors' => ['Invalid JSON: '.json_last_error_msg()],
                'records' => [],
                'eppoCodes' => [],
            ];
        }

        if (! isset($data['groups']) || ! is_array($data['groups'])) {
            return [
                'valid' => false,
                'errors' => ['JSON does not contain a valid groups structure.'],
                'records' => [],
                'eppoCodes' => [],
            ];
        }

        foreach ($data['groups'] as $groupName => $groupRecords) {
            if (! is_array($groupRecords)) {
                $errors[] = "Group {$groupName} is not a valid array.";

                continue;
            }

            foreach ($groupRecords as $record) {
                $records[] = $record;
            }
        }

        if (count($records) !== 81) {
            $errors[] = 'Expected 81 species, found '.count($records).'.';
        }

        foreach ($records as $index => $record) {
            $row = $index + 1;

            $requiredFields = [
                'title',
                'author',
                'photo',
                'family',
                'eppoCode',
                'classification',
                'growsIn',
                'lifeCycle',
                'reproduction',
                'characteristics',
            ];

            foreach ($requiredFields as $field) {
                if (! array_key_exists($field, $record)) {
                    $errors[] = "Record {$row}: missing field '{$field}'.";
                }
            }

            $eppoCode = $record['eppoCode'] ?? null;

            if (! $eppoCode) {
                $errors[] = "Record {$row}: missing EPPO code.";

                continue;
            }

            if (isset($eppoCodes[$eppoCode])) {
                $errors[] = "Duplicate EPPO code: {$eppoCode}.";
            }

            $eppoCodes[$eppoCode] = true;

            // Validate image reference.
            $photo = $record['photo'] ?? '';

            $prefix = '../assets/photos/';

            if (! str_starts_with($photo, $prefix)) {
                $errors[] = "Record {$row}: unexpected photo reference: {$photo}";

                continue;
            }

            $relativePath = substr($photo, strlen($prefix));

            // Prevent path traversal.
            if (
                str_contains($relativePath, '..') ||
                str_starts_with($relativePath, '/') ||
                str_contains($relativePath, '\\')
            ) {
                $errors[] = "Record {$row}: unsafe photo path: {$photo}";

                continue;
            }

            $sourcePath = $this->photosDirectory.'/'.$relativePath;

            if (! file_exists($sourcePath)) {
                $errors[] = "Record {$row}: missing photo: {$relativePath}";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'records' => $records,
            'eppoCodes' => array_keys($eppoCodes),
        ];
    }

    /**
     * Import one species and its related data.
     */
    private function importRecord(
        array $record,
        int $batchId,
        ?WeedSpecies $existing = null
    ): void {
        $sourcePhoto = $record['photo'];
        $relativePhoto = substr(
            $sourcePhoto,
            strlen('../assets/photos/')
        );

        $sourceImage = $this->photosDirectory.'/'.$relativePhoto;

        // Keep the original folder structure in public storage.
        $destinationPath = 'weed-species/'.$relativePhoto;

        // Copy the photo to Laravel's public disk.
        $imageContents = file_get_contents($sourceImage);

        if ($imageContents === false) {
            throw new \RuntimeException("Unable to read image: {$sourceImage}");
        }

        $stored = Storage::disk('public')->put(
            $destinationPath,
            $imageContents
        );

        if (! $stored) {
            throw new \RuntimeException(
                "Unable to save image to public storage: {$destinationPath}"
            );
        }

        try {
            DB::transaction(function () use (
                $record,
                $batchId,
                $existing,
                $destinationPath,
                $relativePhoto
            ) {
                // Create or retrieve reference data.
                $classification = WeedClassification::firstOrCreate([
                    'name' => $record['classification'],
                ]);

                $family = WeedFamily::firstOrCreate([
                    'name' => $record['family'],
                ]);

                $habitat = Habitat::firstOrCreate([
                    'name' => $record['growsIn'],
                ]);

                $lifeCycle = LifeCycle::firstOrCreate([
                    'name' => $record['lifeCycle'],
                ]);

                // Find or restore an existing species if update was requested.
                $species = $existing ?? new WeedSpecies;

                if ($species->trashed()) {
                    $species->restore();
                }

                $species->fill([
                    'scientific_name' => $record['title'],
                    'taxonomic_author' => $record['author'],
                    'eppo_code' => $record['eppoCode'],
                    'classification_id' => $classification->id,
                    'family_id' => $family->id,
                    'habitat_id' => $habitat->id,
                    'life_cycle_id' => $lifeCycle->id,
                    'common_names_source' => $record['description'] ?? null,
                    'reproduction_description' => $record['reproduction'] ?? null,
                    'characteristics' => $record['characteristics'] ?? null,
                    'impacts' => $record['impacts'] ?? null,
                    'weakness' => $record['weakness'] ?? null,
                    'source_payload' => $record,
                    'import_batch_id' => $batchId,
                ]);

                $species->save();

                // Split common/local names, preserving original source text above.
                $localNames = collect(
                    explode(',', $record['description'] ?? '')
                )
                    ->map(fn ($name) => trim($name))
                    ->filter(fn ($name) => $name !== '')
                    ->unique()
                    ->values();

                $localNameIds = [];

                foreach ($localNames as $name) {
                    $localName = LocalName::firstOrCreate([
                        'name' => $name,
                    ]);

                    $localNameIds[] = $localName->id;
                }

                // Sync prevents duplicate pivot records.
                $species->localNames()->sync($localNameIds);

                // Store species image metadata.
                SpeciesImage::updateOrCreate(
                    [
                        'image_path' => $destinationPath,
                    ],
                    [
                        'weed_species_id' => $species->id,
                        'original_filename' => basename($relativePhoto),
                        'caption' => null,
                        'alt_text' => $record['title'],
                        'is_primary' => true,
                        'sort_order' => 0,
                    ]
                );
            });
        } catch (Throwable $exception) {
            // Remove the copied image if the database transaction failed.
            Storage::disk('public')->delete($destinationPath);

            throw $exception;
        }
    }
}
