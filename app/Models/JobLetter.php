<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobLetter extends Model
{
    protected $fillable = [
        'employee_id',
        'employee_name',
        'recipient_name',
        'recipient_organization',
        'recipient_address',
        'letter_purpose',
        'letter_date',
        'letter_html',
        'status',
        'sent_at',
        'sent_to_email',
        'generated_by',
        'notes',
    ];

    protected $casts = [
        'letter_date' => 'date',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the employee that owns the job letter
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who generated the letter
     */
    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
