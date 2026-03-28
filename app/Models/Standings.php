<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Standings extends Model
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
        return $this->belongsTo(Championships::class, 'championship_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Teams::class, 'team_id');
    }
}
