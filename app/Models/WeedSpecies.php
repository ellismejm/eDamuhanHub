<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WeedSpecies extends Model
{
    use SoftDeletes;

    protected $fillable = [
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
        'source_payload',
        'import_batch_id',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'source_payload' => 'array',
        ];
    }

    public function classification(): BelongsTo
    {
        return $this->belongsTo(WeedClassification::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(WeedFamily::class);
    }

    public function habitat(): BelongsTo
    {
        return $this->belongsTo(Habitat::class);
    }

    public function lifeCycle(): BelongsTo
    {
        return $this->belongsTo(LifeCycle::class);
    }

    public function localNames(): BelongsToMany
    {
        return $this->belongsToMany(
            LocalName::class,
            'species_local_names'
        )->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(SpeciesImage::class);
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(SpeciesImage::class)
            ->where('is_primary', true);
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}