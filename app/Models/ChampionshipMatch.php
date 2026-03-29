<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChampionshipMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'championship_id',
        'phase',
        'order',
        'team_home_id',
        'team_away_id',
        'goals_home',
        'goals_away',
        'winner_id'
    ];

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class, 'championship_id');
    }

    public function teamHome(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_home_id');
    }

    public function teamAway(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_away_id');
    }
}
