<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteRecord extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'quantity',
        'unit',
        'cost',
        'reason',
        'notes',
        'wasted_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'cost' => 'decimal:2',
        'wasted_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
