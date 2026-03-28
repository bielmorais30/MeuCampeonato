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
    ];

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class, 'championship_id');
    }
}
