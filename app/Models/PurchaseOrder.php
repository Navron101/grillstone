<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'vendor_id',
        'status',
        'ordered_at',
        'due_at',
        'reference',
        'notes',
        'subtotal_cents',
        'tax_cents',
        'total_cents',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'due_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseOrderLine::class);
    }
}
