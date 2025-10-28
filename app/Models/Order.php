<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model {
    protected $fillable = [
        'order_no','subtotal_cents','tax_cents','discount_cents','total_cents','status','note','meta'
    ];
    protected $casts = ['meta'=>'array'];

    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function loyaltyTransaction(): HasOne { return $this->hasOne(LoyaltyTransaction::class); }

    // Accessor for subtotal in dollars (for loyalty service)
    public function getSubtotalAttribute(): float
    {
        return $this->subtotal_cents / 100;
    }
}
