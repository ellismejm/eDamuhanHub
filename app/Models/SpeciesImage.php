<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpeciesImage extends Model
{
    protected $fillable = [
        'weed_species_id',
        'image_path',
        'original_filename',
        'caption',
        'alt_text',
        'is_primary',
        'sort_order',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(WeedSpecies::class, 'weed_species_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}