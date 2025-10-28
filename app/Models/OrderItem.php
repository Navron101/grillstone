<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model {
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'item_type',
        'item_id',
        'name',
        'qty',
        'unit_price_cents',
        'tax_rate',
        'line_total_cents',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array',
        'tax_rate' => 'decimal:2',
    ];

    public function order(): BelongsTo {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class, 'item_id');
    }
}
