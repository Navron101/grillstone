<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TimeLogController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('time_logs as tl')
            ->join('employees as e', 'e.id', '=', 'tl.employee_id')
            ->leftJoin('users as u', 'u.id', '=', 'tl.approved_by')
            ->select([
                'tl.*',
                DB::raw("CONCAT(e.first_name, ' ', e.last_name) as employee_name"),
                'e.employee_number',
                DB::raw("CONCAT(u.name) as approved_by_name"),
            ])
            ->orderByDesc('tl.work_date')
            ->orderByDesc('tl.id');

        // Filter by employee
        if ($request->has('employee_id')) {
            $query->where('tl.employee_id', $request->employee_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('tl.work_date', [$request->start_date, $request->end_date]);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('tl.status', $request->status);
        }

        $logs = $query->get();

        return response()->json($logs);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'work_date' => 'required|date',
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',
            'regular_hours' => 'nullable|numeric|min:0|max:24',
            'overtime_hours' => 'nullable|numeric|min:0|max:24',
            'notes' => 'nullable|string',
        ]);

        // Calculate hours if clock in/out provided
        if (!empty($data['clock_in']) && !empty($data['clock_out'])) {
            $clockIn = \Carbon\Carbon::parse($data['work_date'] . ' ' . $data['clock_in']);
            $clockOut = \Carbon\Carbon::parse($data['work_date'] . ' ' . $data['clock_out']);
            $totalHours = $clockOut->diffInMinutes($clockIn) / 60;

            // Standard work day is 8 hours
            $data['regular_hours'] = min($totalHours, 8);
            $data['overtime_hours'] = max(0, $totalHours - 8);
        }

        $id = DB::table('time_logs')->insertGetId([
            'employee_id' => $data['employee_id'],
            'work_date' => $data['work_date'],
            'clock_in' => $data['clock_in'] ?? null,
            'clock_out' => $data['clock_out'] ?? null,
            'regular_hours' => $data['regular_hours'] ?? 0,
            'overtime_hours' => $data['overtime_hours'] ?? 0,
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['id' => $id], 201);
    }

    public function update($id, Request $request)
    {
        $data = $request->validate([
            'work_date' => 'sometimes|date',
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',
            'regular_hours' => 'nullable|numeric|min:0|max:24',
            'overtime_hours' => 'nullable|numeric|min:0|max:24',
            'notes' => 'nullable|string',
        ]);

        // Recalculate hours if times changed
        if (isset($data['clock_in']) && isset($data['clock_out'])) {
            $log = DB::table('time_logs')->where('id', $id)->first();
            $workDate = $data['work_date'] ?? $log->work_date;

            $clockIn = \Carbon\Carbon::parse($workDate . ' ' . $data['clock_in']);
            $clockOut = \Carbon\Carbon::parse($workDate . ' ' . $data['clock_out']);
            $totalHours = $clockOut->diffInMinutes($clockIn) / 60;

            $data['regular_hours'] = min($totalHours, 8);
            $data['overtime_hours'] = max(0, $totalHours - 8);
        }

        $data['updated_at'] = now();

        DB::table('time_logs')->where('id', $id)->update($data);

        return response()->json(['ok' => true]);
    }

    public function approve($id)
    {
        $userId = Auth::id();

        DB::table('time_logs')->where('id', $id)->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function reject($id)
    {
        DB::table('time_logs')->where('id', $id)->update([
            'status' => 'rejected',
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function destroy($id)
    {
        DB::table('time_logs')->where('id', $id)->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Bulk approve time logs
     */
    public function bulkApprove(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:time_logs,id',
        ]);

        $userId = Auth::id();

        DB::table('time_logs')
            ->whereIn('id', $data['ids'])
            ->update([
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true, 'approved' => count($data['ids'])]);
    }
}
