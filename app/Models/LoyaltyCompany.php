<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoyaltyCompany extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'contact_name',
        'contact_email',
        'contact_phone',
        'address',
        'discount_percentage',
        'per_order_cap',
        'per_employee_monthly_cap',
        'company_monthly_cap',
        'status',
        'contract_start_date',
        'contract_end_date',
        'notes',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'per_order_cap' => 'decimal:2',
        'per_employee_monthly_cap' => 'decimal:2',
        'company_monthly_cap' => 'decimal:2',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(LoyaltyEmployee::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(LoyaltySettlement::class);
    }

    /**
     * Get active employees for this company
     */
    public function activeEmployees(): HasMany
    {
        return $this->employees()->where('status', 'active');
    }

    /**
     * Check if company is active and eligible for discounts
     */
    public function isEligible(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->contract_end_date && $this->contract_end_date->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Get total pending discount amount for the current month
     */
    public function getCurrentMonthTotal(): float
    {
        return $this->transactions()
            ->where('status', 'pending')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('discount_amount');
    }
}
