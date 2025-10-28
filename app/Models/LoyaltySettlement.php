<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltySettlement extends Model
{
    protected $fillable = [
        'loyalty_company_id',
        'period',
        'period_start',
        'period_end',
        'transaction_count',
        'total_amount',
        'status',
        'finalized_at',
        'sent_at',
        'paid_at',
        'amount_paid',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'finalized_at' => 'datetime',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(LoyaltyCompany::class, 'loyalty_company_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    /**
     * Finalize the settlement (lock it for processing)
     */
    public function finalize(): void
    {
        if ($this->status !== 'draft') {
            throw new \Exception('Can only finalize draft settlements');
        }

        $this->update([
            'status' => 'finalized',
            'finalized_at' => now(),
        ]);
    }

    /**
     * Mark as sent to company
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Record payment
     */
    public function recordPayment(float $amount): void
    {
        $newAmountPaid = $this->amount_paid + $amount;

        $this->update([
            'amount_paid' => $newAmountPaid,
            'status' => $newAmountPaid >= $this->total_amount ? 'paid' : 'partially_paid',
            'paid_at' => $newAmountPaid >= $this->total_amount ? now() : $this->paid_at,
        ]);
    }

    /**
     * Get remaining balance
     */
    public function getRemainingBalanceAttribute(): float
    {
        return max(0, $this->total_amount - $this->amount_paid);
    }

    /**
     * Check if fully paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid' || $this->amount_paid >= $this->total_amount;
    }

    /**
     * Generate settlement for a company and period
     */
    public static function generateForPeriod(LoyaltyCompany $company, string $period): self
    {
        [$year, $month] = explode('-', $period);
        $periodStart = now()->setYear((int)$year)->setMonth((int)$month)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        // Get pending transactions for this period
        $transactions = LoyaltyTransaction::where('loyalty_company_id', $company->id)
            ->pending()
            ->forPeriod($periodStart, $periodEnd)
            ->get();

        $settlement = self::create([
            'loyalty_company_id' => $company->id,
            'period' => $period,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'transaction_count' => $transactions->count(),
            'total_amount' => $transactions->sum('discount_amount'),
            'status' => 'draft',
        ]);

        // Link transactions to settlement
        foreach ($transactions as $transaction) {
            $transaction->markAsSettled($settlement);
        }

        return $settlement;
    }
}
