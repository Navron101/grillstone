<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmployeeTabController extends Controller
{
    /**
     * Get all tab items for an employee
     */
    public function index(Request $request)
    {
        $employeeId = $request->query('employee_id');
        $status = $request->query('status'); // pending, deducted, cancelled

        $query = DB::table('employee_tab_items as eti')
            ->leftJoin('employees as e', 'e.id', '=', 'eti.employee_id')
            ->select([
                'eti.*',
                DB::raw("CONCAT(e.first_name, ' ', e.last_name) as employee_name"),
                'e.employee_number'
            ])
            ->orderBy('eti.tab_date', 'desc');

        if ($employeeId) {
            $query->where('eti.employee_id', $employeeId);
        }

        if ($status) {
            $query->where('eti.status', $status);
        }

        $items = $query->get();

        return response()->json($items);
    }

    /**
     * Get pending tab items for an employee
     */
    public function pending($employeeId)
    {
        $items = DB::table('employee_tab_items')
            ->where('employee_id', $employeeId)
            ->where('status', 'pending')
            ->orderBy('tab_date', 'asc')
            ->get();

        $total = $items->sum('amount_cents');

        return response()->json([
            'items' => $items,
            'total_cents' => $total,
            'total_amount' => $total / 100
        ]);
    }

    /**
     * Create a new tab item
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'tab_date' => 'nullable|date',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'employee_id' => $request->employee_id,
            'description' => $request->description,
            'amount_cents' => round($request->amount * 100),
            'tab_date' => $request->tab_date ?? now()->toDateString(),
            'status' => 'pending',
            'notes' => $request->notes,
            'created_at' => now(),
            'updated_at' => now()
        ];

        $id = DB::table('employee_tab_items')->insertGetId($data);
        $item = DB::table('employee_tab_items')->where('id', $id)->first();

        return response()->json($item, 201);
    }

    /**
     * Update a tab item
     */
    public function update(Request $request, $id)
    {
        $item = DB::table('employee_tab_items')->where('id', $id)->first();
        if (!$item) {
            return response()->json(['error' => 'Tab item not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'tab_date' => 'sometimes|date',
            'status' => 'sometimes|in:pending,deducted,cancelled',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = ['updated_at' => now()];

        if ($request->has('description')) {
            $updateData['description'] = $request->description;
        }
        if ($request->has('amount')) {
            $updateData['amount_cents'] = round($request->amount * 100);
        }
        if ($request->has('tab_date')) {
            $updateData['tab_date'] = $request->tab_date;
        }
        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }
        if ($request->has('notes')) {
            $updateData['notes'] = $request->notes;
        }

        DB::table('employee_tab_items')->where('id', $id)->update($updateData);
        $updated = DB::table('employee_tab_items')->where('id', $id)->first();

        return response()->json($updated);
    }

    /**
     * Delete a tab item (only if pending)
     */
    public function destroy($id)
    {
        $item = DB::table('employee_tab_items')->where('id', $id)->first();
        if (!$item) {
            return response()->json(['error' => 'Tab item not found'], 404);
        }

        if ($item->status !== 'pending') {
            return response()->json(['error' => 'Can only delete pending items'], 400);
        }

        DB::table('employee_tab_items')->where('id', $id)->delete();

        return response()->json(['message' => 'Tab item deleted successfully']);
    }

    /**
     * Get total pending tab balance for an employee
     */
    public function balance($employeeId)
    {
        $total = DB::table('employee_tab_items')
            ->where('employee_id', $employeeId)
            ->where('status', 'pending')
            ->sum('amount_cents');

        return response()->json([
            'employee_id' => $employeeId,
            'balance_cents' => $total,
            'balance_amount' => $total / 100
        ]);
    }
}
