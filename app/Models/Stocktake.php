<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stocktake extends Model
{
    protected $fillable = [
        'location_id', 'reference', 'status', 'counted_at', 'counted_by', 'notes'
    ];

    protected $casts = [
        'counted_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(StocktakeLine::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Location::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }
}
