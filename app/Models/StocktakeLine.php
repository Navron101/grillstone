<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StocktakeLine extends Model
{
    protected $fillable = [
        'stocktake_id', 'product_id', 'system_qty', 'actual_qty', 'variance', 'unit_cost_cents', 'notes'
    ];

    protected $casts = [
        'system_qty' => 'float',
        'actual_qty' => 'float',
        'variance' => 'float',
        'unit_cost_cents' => 'integer',
    ];

    public function stocktake(): BelongsTo
    {
        return $this->belongsTo(Stocktake::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Automatically calculate variance when actual_qty is set
    protected static function booted()
    {
        static::saving(function ($line) {
            if ($line->actual_qty !== null && $line->system_qty !== null) {
                $line->variance = $line->actual_qty - $line->system_qty;
            }
        });
    }
}
