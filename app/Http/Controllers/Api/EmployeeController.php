<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * List all employees with department info (join)
     */
    public function index()
    {
        $employees = DB::table('employees')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->select(
                'employees.*',
                'departments.name as department_name'
            )
            ->whereNull('employees.deleted_at')
            ->orderBy('employees.employee_number')
            ->get();

        return response()->json($employees);
    }

    /**
     * Get single employee details
     */
    public function show($id)
    {
        $employee = DB::table('employees')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->select(
                'employees.*',
                'departments.name as department_name'
            )
            ->where('employees.id', $id)
            ->whereNull('employees.deleted_at')
            ->first();

        if (!$employee) {
            return response()->json([
                'error' => 'Employee not found'
            ], 404);
        }

        return response()->json($employee);
    }

    /**
     * Create new employee with validation for all fields
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // Required fields
            'employee_number' => 'nullable|string|max:20|unique:employees,employee_number',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:employees,email',
            'position' => 'required|string|max:100',
            'hire_date' => 'required|date',

            // Optional fields
            'phone' => 'nullable|string|max:20',
            'trn' => 'nullable|string|max:20',
            'nis' => 'nullable|string|max:20',

            // Address
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'parish' => 'nullable|string|max:100',

            // Employment details
            'department_id' => 'nullable|exists:departments,id',
            'termination_date' => 'nullable|date',
            'employment_type' => ['nullable', Rule::in(['full-time', 'part-time', 'contract', 'casual'])],
            'employment_status' => ['nullable', Rule::in(['active', 'on-leave', 'terminated', 'suspended'])],

            // Compensation
            'hourly_rate' => 'nullable|numeric|min:0|max:99999999.99',
            'salary_amount' => 'nullable|numeric|min:0|max:99999999.99',
            'is_salaried' => 'nullable|boolean',
            'pay_frequency' => ['nullable', Rule::in(['hourly', 'weekly', 'fortnightly'])],
            'standard_hours_per_day' => 'nullable|numeric|min:0|max:24',
            'overtime_rate_multiplier' => 'nullable|numeric|min:1|max:5',
            'clock_system_enabled' => 'nullable|boolean',

            // Bank details
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'bank_branch' => 'nullable|string|max:100',

            // Emergency contact
            'emergency_contact_name' => 'nullable|string|max:150',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:50',

            // Notes
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Generate employee number if not provided
        if (empty($data['employee_number'])) {
            $data['employee_number'] = $this->generateEmployeeNumber();
        }

        // Set defaults
        $data['employment_type'] = $data['employment_type'] ?? 'full-time';
        $data['employment_status'] = $data['employment_status'] ?? 'active';
        $data['is_salaried'] = $data['is_salaried'] ?? false;
        $data['pay_frequency'] = $data['pay_frequency'] ?? 'hourly';
        $data['standard_hours_per_day'] = $data['standard_hours_per_day'] ?? 8.00;
        $data['overtime_rate_multiplier'] = $data['overtime_rate_multiplier'] ?? 1.5;
        $data['clock_system_enabled'] = $data['clock_system_enabled'] ?? true;
        $data['is_active'] = $data['is_active'] ?? true;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        $id = DB::table('employees')->insertGetId($data);

        $employee = DB::table('employees')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->select(
                'employees.*',
                'departments.name as department_name'
            )
            ->where('employees.id', $id)
            ->first();

        return response()->json($employee, 201);
    }

    /**
     * Update employee
     */
    public function update($id, Request $request)
    {
        // Check if employee exists
        $exists = DB::table('employees')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->exists();

        if (!$exists) {
            return response()->json([
                'error' => 'Employee not found'
            ], 404);
        }

        $data = $request->validate([
            // Required fields
            'employee_number' => 'nullable|string|max:20|unique:employees,employee_number,' . $id,
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:employees,email,' . $id,
            'position' => 'required|string|max:100',
            'hire_date' => 'required|date',

            // Optional fields
            'phone' => 'nullable|string|max:20',
            'trn' => 'nullable|string|max:20',
            'nis' => 'nullable|string|max:20',

            // Address
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'parish' => 'nullable|string|max:100',

            // Employment details
            'department_id' => 'nullable|exists:departments,id',
            'termination_date' => 'nullable|date',
            'employment_type' => ['nullable', Rule::in(['full-time', 'part-time', 'contract', 'casual'])],
            'employment_status' => ['nullable', Rule::in(['active', 'on-leave', 'terminated', 'suspended'])],

            // Compensation
            'hourly_rate' => 'nullable|numeric|min:0|max:99999999.99',
            'salary_amount' => 'nullable|numeric|min:0|max:99999999.99',
            'is_salaried' => 'nullable|boolean',
            'pay_frequency' => ['nullable', Rule::in(['hourly', 'weekly', 'fortnightly'])],
            'standard_hours_per_day' => 'nullable|numeric|min:0|max:24',
            'overtime_rate_multiplier' => 'nullable|numeric|min:1|max:5',
            'clock_system_enabled' => 'nullable|boolean',

            // Bank details
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'bank_branch' => 'nullable|string|max:100',

            // Emergency contact
            'emergency_contact_name' => 'nullable|string|max:150',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:50',

            // Notes
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $data['updated_at'] = now();

        DB::table('employees')
            ->where('id', $id)
            ->update($data);

        $employee = DB::table('employees')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->select(
                'employees.*',
                'departments.name as department_name'
            )
            ->where('employees.id', $id)
            ->first();

        return response()->json($employee);
    }

    /**
     * Soft delete employee
     */
    public function destroy($id)
    {
        $exists = DB::table('employees')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->exists();

        if (!$exists) {
            return response()->json([
                'error' => 'Employee not found'
            ], 404);
        }

        DB::table('employees')
            ->where('id', $id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Helper to generate unique employee numbers like "EMP-001"
     */
    private function generateEmployeeNumber(): string
    {
        // Get the latest employee number
        $latestEmployee = DB::table('employees')
            ->whereNull('deleted_at')
            ->where('employee_number', 'like', 'EMP-%')
            ->orderBy('employee_number', 'desc')
            ->first();

        if (!$latestEmployee) {
            return 'EMP-001';
        }

        // Extract the number part from the latest employee number
        $lastNumber = (int) str_replace('EMP-', '', $latestEmployee->employee_number);
        $nextNumber = $lastNumber + 1;

        // Format with leading zeros (e.g., EMP-001, EMP-002, etc.)
        return 'EMP-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
