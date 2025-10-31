<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Combo extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price_cents',
        'category_id',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_cents' => 'integer',
    ];

    protected $appends = ['price'];

    /**
     * Get the price in dollars (from cents)
     */
    public function getPriceAttribute(): float
    {
        return $this->price_cents / 100;
    }

    /**
     * Set the price in dollars (stores as cents)
     */
    public function setPriceAttribute($value): void
    {
        $this->attributes['price_cents'] = (int) ($value * 100);
    }

    /**
     * Category this combo belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Items that make up this combo
     */
    public function items(): HasMany
    {
        return $this->hasMany(ComboItem::class);
    }
}
