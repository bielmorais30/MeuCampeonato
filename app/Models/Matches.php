<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Matches extends Model
{
    use HasFactory;

    protected $fillable = [
        'championship_id',
        'phase',
    ];

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championships::class, 'championship_id');
    }
}
