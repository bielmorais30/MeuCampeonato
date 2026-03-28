<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Championship extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'championship_id');
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standing::class, 'championship_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(ChampionshipMatch::class, 'championship_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'registrations', 'championship_id', 'team_id')->withTimestamps();
    }
}
