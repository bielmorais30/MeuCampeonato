<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Standing extends Model
{
    use HasFactory;

    protected $fillable = [
        'championship_id',
        'team_id',
        'points',
        'goal_scored',
        'goal_conceded',
    ];

    protected $casts = [
        'points' => 'integer',
        'goal_scored' => 'integer',
        'goal_conceded' => 'integer',
    ];

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class, 'championship_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
}
