<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeedFamily extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function species(): HasMany
    {
        return $this->hasMany(WeedSpecies::class, 'family_id');
    }
}