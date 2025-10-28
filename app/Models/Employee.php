<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_number',
        'first_name',
        'last_name',
        'gender',
        'email',
        'phone',
        'trn',
        'nis',
        'address',
        'city',
        'parish',
        'department_id',
        'position',
        'hire_date',
        'termination_date',
        'employment_type',
        'employment_status',
        'hourly_rate',
        'salary_per_period',
        'is_salaried',
        'bank_name',
        'bank_account',
        'bank_branch',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'termination_date' => 'date',
        'hourly_rate' => 'decimal:2',
        'salary_per_period' => 'decimal:2',
        'is_salaried' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the department that the employee belongs to
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the employment contracts for the employee
     */
    public function contracts()
    {
        return $this->hasMany(EmploymentContract::class);
    }

    /**
     * Get the job letters for the employee
     */
    public function jobLetters()
    {
        return $this->hasMany(JobLetter::class);
    }
}
