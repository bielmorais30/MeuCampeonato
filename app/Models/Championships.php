<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Championships extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registrations::class, 'championship_id');
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standings::class, 'championship_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(Matches::class, 'championship_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Teams::class, 'registrations', 'championship_id', 'team_id')->withTimestamps();
    }
}
