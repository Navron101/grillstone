<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoyaltyEmployee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'loyalty_company_id',
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'status',
        'start_date',
        'end_date',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $appends = ['full_name'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(LoyaltyCompany::class, 'loyalty_company_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if employee is eligible for discount
     */
    public function isEligible(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        // Check company eligibility
        return $this->company->isEligible();
    }

    /**
     * Get total discount amount for current month
     */
    public function getCurrentMonthTotal(): float
    {
        return $this->transactions()
            ->where('status', 'pending')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('discount_amount');
    }

    /**
     * Check if employee has reached their monthly cap
     */
    public function hasReachedMonthlyCap(): bool
    {
        $cap = $this->company->per_employee_monthly_cap;

        if (!$cap) {
            return false;
        }

        return $this->getCurrentMonthTotal() >= $cap;
    }
}
