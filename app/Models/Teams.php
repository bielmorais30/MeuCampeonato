<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Teams extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registrations::class, 'team_id');
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standings::class, 'team_id');
    }

    public function championships(): BelongsToMany
    {
        return $this->belongsToMany(Championships::class, 'registrations', 'team_id', 'championship_id')->withTimestamps();
    }
}
