<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LifeCycle extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function species(): HasMany
    {
        return $this->hasMany(WeedSpecies::class, 'life_cycle_id');
    }
}