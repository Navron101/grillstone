<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = DB::table('departments')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json($departments);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $id = DB::table('departments')->insertGetId([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['id' => $id], 201);
    }

    public function update($id, Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        DB::table('departments')
            ->where('id', $id)
            ->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true]);
    }

    public function destroy($id)
    {
        // Check if department has employees
        $hasEmployees = DB::table('employees')
            ->where('department_id', $id)
            ->exists();

        if ($hasEmployees) {
            return response()->json([
                'error' => 'Cannot delete department with active employees'
            ], 400);
        }

        DB::table('departments')->where('id', $id)->delete();

        return response()->json(['ok' => true]);
    }
}
