<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LocalName extends Model
{
    protected $fillable = [
        'name',
    ];

    public function species(): BelongsToMany
    {
        return $this->belongsToMany(
            WeedSpecies::class,
            'species_local_names'
        )->withTimestamps();
    }
}