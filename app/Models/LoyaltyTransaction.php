<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    protected $fillable = [
        'loyalty_company_id',
        'loyalty_employee_id',
        'order_id',
        'order_subtotal',
        'discount_percentage',
        'discount_amount',
        'status',
        'loyalty_settlement_id',
        'settled_at',
        'reversed_at',
        'reversal_reason',
    ];

    protected $casts = [
        'order_subtotal' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'settled_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(LoyaltyCompany::class, 'loyalty_company_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(LoyaltyEmployee::class, 'loyalty_employee_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(LoyaltySettlement::class, 'loyalty_settlement_id');
    }

    /**
     * Mark transaction as settled
     */
    public function markAsSettled(LoyaltySettlement $settlement): void
    {
        $this->update([
            'status' => 'settled',
            'loyalty_settlement_id' => $settlement->id,
            'settled_at' => now(),
        ]);
    }

    /**
     * Reverse the transaction (for refunds)
     */
    public function reverse(string $reason = null): void
    {
        $this->update([
            'status' => 'reversed',
            'reversed_at' => now(),
            'reversal_reason' => $reason,
        ]);

        // If already settled, reduce settlement amount
        if ($this->settlement && $this->settlement->status !== 'paid') {
            $this->settlement->decrement('total_amount', $this->discount_amount);
            $this->settlement->decrement('transaction_count');
        }
    }

    /**
     * Scope to get pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get transactions for a specific period
     */
    public function scopeForPeriod($query, $periodStart, $periodEnd)
    {
        return $query->whereBetween('created_at', [$periodStart, $periodEnd]);
    }
}
