<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Registrations extends Model
{
    use HasFactory;

    protected $fillable = [
        'championship_id',
        'team_id',
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
