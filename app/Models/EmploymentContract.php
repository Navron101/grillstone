<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploymentContract extends Model
{
    protected $fillable = [
        'employee_id',
        'contract_type',
        'employee_name',
        'employee_address',
        'position',
        'salary_amount_cents',
        'start_date',
        'end_date',
        'contract_html',
        'status',
        'sent_at',
        'sent_to_email',
        'generated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'sent_at' => 'datetime',
        'salary_amount_cents' => 'integer',
    ];

    /**
     * Get the employee that owns the contract
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who generated the contract
     */
    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Get salary in dollars
     */
    public function getSalaryAttribute()
    {
        return $this->salary_amount_cents ? $this->salary_amount_cents / 100 : 0;
    }
}
