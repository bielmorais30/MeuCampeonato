<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'team_id');
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standing::class, 'team_id');
    }

    public function championships(): BelongsToMany
    {
        return $this->belongsToMany(Championship::class, 'registrations', 'team_id', 'championship_id')->withTimestamps();
    }
}
