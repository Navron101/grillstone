<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClockController extends Controller
{
    /**
     * Clock in an employee
     * Creates a new time_log record with clock_in time
     *
     * Business rules:
     * - Only one active clock-in at a time per employee
     * - Employee must be active and clock system enabled
     *
     * @param int $employeeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function clockIn($employeeId)
    {
        // Verify employee exists and is active
        $employee = DB::table('employees')
            ->where('id', $employeeId)
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->first();

        if (!$employee) {
            return response()->json([
                'error' => 'Employee not found or inactive'
            ], 404);
        }

        // Check if clock system is enabled for this employee
        if (!$employee->clock_system_enabled) {
            return response()->json([
                'error' => 'Clock system is not enabled for this employee'
            ], 403);
        }

        // Check for existing active clock-in (no clock_out yet)
        $activeClock = DB::table('time_logs')
            ->where('employee_id', $employeeId)
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->first();

        if ($activeClock) {
            return response()->json([
                'error' => 'Employee already clocked in',
                'active_clock' => $activeClock
            ], 409);
        }

        $now = Carbon::now();
        $workDate = $now->toDateString();
        $clockInTime = $now->format('H:i:s');

        // Create new time log entry
        $timeLogId = DB::table('time_logs')->insertGetId([
            'employee_id' => $employeeId,
            'work_date' => $workDate,
            'clock_in' => $clockInTime,
            'clock_out' => null,
            'regular_hours' => 0,
            'overtime_hours' => 0,
            'status' => 'pending',
            'notes' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $timeLog = DB::table('time_logs')->where('id', $timeLogId)->first();

        return response()->json([
            'message' => 'Clocked in successfully',
            'time_log' => $timeLog,
            'employee_name' => $employee->first_name . ' ' . $employee->last_name,
        ], 201);
    }

    /**
     * Clock out an employee
     * Updates time_log with clock_out time and calculates hours
     *
     * Business rules:
     * - Must have an active clock-in
     * - Auto-calculates regular_hours and overtime_hours
     * - Handles overnight shifts (clock_out < clock_in means next day)
     * - Uses employee->standard_hours_per_day and overtime_rate_multiplier
     *
     * @param int $employeeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function clockOut($employeeId)
    {
        // Verify employee exists
        $employee = DB::table('employees')
            ->where('id', $employeeId)
            ->whereNull('deleted_at')
            ->first();

        if (!$employee) {
            return response()->json([
                'error' => 'Employee not found'
            ], 404);
        }

        // Find active clock-in
        $activeClock = DB::table('time_logs')
            ->where('employee_id', $employeeId)
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->orderByDesc('id')
            ->first();

        if (!$activeClock) {
            return response()->json([
                'error' => 'No active clock-in found for this employee'
            ], 404);
        }

        $now = Carbon::now();
        $clockOutTime = $now->format('H:i:s');

        // Calculate hours worked
        $clockIn = Carbon::parse($activeClock->work_date . ' ' . $activeClock->clock_in);
        $clockOut = Carbon::parse($activeClock->work_date . ' ' . $clockOutTime);

        // Handle overnight shifts: if clock_out is before clock_in, add a day
        if ($clockOut->lt($clockIn)) {
            $clockOut->addDay();
        }

        // Calculate total hours worked
        $totalHours = $clockOut->diffInMinutes($clockIn) / 60;
        $totalHours = round($totalHours, 2);

        // Get employee's standard hours per day (default 8.00)
        $standardHours = $employee->standard_hours_per_day ?? 8.00;

        // Calculate regular and overtime hours
        $regularHours = min($totalHours, $standardHours);
        $overtimeHours = max(0, $totalHours - $standardHours);

        // Round to 2 decimal places
        $regularHours = round($regularHours, 2);
        $overtimeHours = round($overtimeHours, 2);

        // Update the time log
        DB::table('time_logs')
            ->where('id', $activeClock->id)
            ->update([
                'clock_out' => $clockOutTime,
                'regular_hours' => $regularHours,
                'overtime_hours' => $overtimeHours,
                'updated_at' => $now,
            ]);

        $updatedLog = DB::table('time_logs')->where('id', $activeClock->id)->first();

        return response()->json([
            'message' => 'Clocked out successfully',
            'time_log' => $updatedLog,
            'total_hours' => $totalHours,
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'overtime_rate_multiplier' => $employee->overtime_rate_multiplier ?? 1.5,
            'employee_name' => $employee->first_name . ' ' . $employee->last_name,
        ], 200);
    }

    /**
     * Check current clock status of an employee
     * Returns whether employee is currently clocked in and their active log if any
     *
     * @param int $employeeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function currentStatus($employeeId)
    {
        // Verify employee exists
        $employee = DB::table('employees')
            ->where('id', $employeeId)
            ->whereNull('deleted_at')
            ->select('id', 'employee_number', 'first_name', 'last_name', 'clock_system_enabled')
            ->first();

        if (!$employee) {
            return response()->json([
                'error' => 'Employee not found'
            ], 404);
        }

        // Check for active clock-in
        $activeClock = DB::table('time_logs')
            ->where('employee_id', $employeeId)
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->orderByDesc('id')
            ->first();

        $isClockedIn = $activeClock !== null;

        $response = [
            'employee_id' => $employee->id,
            'employee_number' => $employee->employee_number,
            'employee_name' => $employee->first_name . ' ' . $employee->last_name,
            'clock_system_enabled' => (bool) $employee->clock_system_enabled,
            'is_clocked_in' => $isClockedIn,
        ];

        if ($isClockedIn) {
            $clockIn = Carbon::parse($activeClock->work_date . ' ' . $activeClock->clock_in);
            $now = Carbon::now();
            $minutesWorked = $now->diffInMinutes($clockIn);
            $hoursWorked = round($minutesWorked / 60, 2);

            $response['active_clock'] = $activeClock;
            $response['clocked_in_at'] = $clockIn->toDateTimeString();
            $response['hours_worked_so_far'] = $hoursWorked;
        }

        return response()->json($response);
    }

    /**
     * Get today's time logs for an employee
     * Returns all clock entries for the current date
     *
     * @param int $employeeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function todayLogs($employeeId)
    {
        // Verify employee exists
        $employee = DB::table('employees')
            ->where('id', $employeeId)
            ->whereNull('deleted_at')
            ->select('id', 'employee_number', 'first_name', 'last_name')
            ->first();

        if (!$employee) {
            return response()->json([
                'error' => 'Employee not found'
            ], 404);
        }

        $today = Carbon::today()->toDateString();

        $logs = DB::table('time_logs')
            ->where('employee_id', $employeeId)
            ->where('work_date', $today)
            ->orderBy('clock_in', 'desc')
            ->get();

        // Calculate total hours for today
        $totalRegularHours = $logs->sum('regular_hours');
        $totalOvertimeHours = $logs->sum('overtime_hours');
        $totalHours = $totalRegularHours + $totalOvertimeHours;

        return response()->json([
            'employee_id' => $employee->id,
            'employee_number' => $employee->employee_number,
            'employee_name' => $employee->first_name . ' ' . $employee->last_name,
            'date' => $today,
            'logs' => $logs,
            'summary' => [
                'total_hours' => round($totalHours, 2),
                'regular_hours' => round($totalRegularHours, 2),
                'overtime_hours' => round($totalOvertimeHours, 2),
                'number_of_shifts' => $logs->count(),
            ]
        ]);
    }

    /**
     * Quick clock in/out based on employee_number (for kiosk mode)
     * Automatically determines whether to clock in or out based on current status
     *
     * Expected request body:
     * {
     *   "employee_number": "EMP-001",
     *   "notes": "Optional notes"
     * }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickClock(Request $request)
    {
        $data = $request->validate([
            'employee_number' => 'required|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        // Find employee by employee_number
        $employee = DB::table('employees')
            ->where('employee_number', $data['employee_number'])
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->first();

        if (!$employee) {
            return response()->json([
                'error' => 'Employee not found or inactive',
                'employee_number' => $data['employee_number']
            ], 404);
        }

        // Check if clock system is enabled
        if (!$employee->clock_system_enabled) {
            return response()->json([
                'error' => 'Clock system is not enabled for this employee',
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
            ], 403);
        }

        // Check current status
        $activeClock = DB::table('time_logs')
            ->where('employee_id', $employee->id)
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->orderByDesc('id')
            ->first();

        // If no active clock, clock in
        if (!$activeClock) {
            $now = Carbon::now();
            $workDate = $now->toDateString();
            $clockInTime = $now->format('H:i:s');

            $timeLogId = DB::table('time_logs')->insertGetId([
                'employee_id' => $employee->id,
                'work_date' => $workDate,
                'clock_in' => $clockInTime,
                'clock_out' => null,
                'regular_hours' => 0,
                'overtime_hours' => 0,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $timeLog = DB::table('time_logs')->where('id', $timeLogId)->first();

            return response()->json([
                'action' => 'clock_in',
                'message' => 'Clocked in successfully',
                'employee_id' => $employee->id,
                'employee_number' => $employee->employee_number,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'time_log' => $timeLog,
            ], 201);
        }

        // If active clock exists, clock out
        $now = Carbon::now();
        $clockOutTime = $now->format('H:i:s');

        // Calculate hours
        $clockIn = Carbon::parse($activeClock->work_date . ' ' . $activeClock->clock_in);
        $clockOut = Carbon::parse($activeClock->work_date . ' ' . $clockOutTime);

        // Handle overnight shifts
        if ($clockOut->lt($clockIn)) {
            $clockOut->addDay();
        }

        $totalHours = $clockOut->diffInMinutes($clockIn) / 60;
        $totalHours = round($totalHours, 2);

        $standardHours = $employee->standard_hours_per_day ?? 8.00;
        $regularHours = round(min($totalHours, $standardHours), 2);
        $overtimeHours = round(max(0, $totalHours - $standardHours), 2);

        // Add notes if provided
        $updateData = [
            'clock_out' => $clockOutTime,
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'updated_at' => $now,
        ];

        if (!empty($data['notes'])) {
            $existingNotes = $activeClock->notes ? $activeClock->notes . "\n" : '';
            $updateData['notes'] = $existingNotes . $data['notes'];
        }

        DB::table('time_logs')
            ->where('id', $activeClock->id)
            ->update($updateData);

        $updatedLog = DB::table('time_logs')->where('id', $activeClock->id)->first();

        return response()->json([
            'action' => 'clock_out',
            'message' => 'Clocked out successfully',
            'employee_id' => $employee->id,
            'employee_number' => $employee->employee_number,
            'employee_name' => $employee->first_name . ' ' . $employee->last_name,
            'time_log' => $updatedLog,
            'total_hours' => $totalHours,
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'overtime_rate_multiplier' => $employee->overtime_rate_multiplier ?? 1.5,
        ], 200);
    }
}
