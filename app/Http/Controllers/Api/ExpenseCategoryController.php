<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json(['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $category = ExpenseCategory::create($data);

        return response()->json([
            'message' => 'Expense category created successfully',
            'category' => $category
        ], 201);
    }

    public function show($id)
    {
        $category = ExpenseCategory::findOrFail($id);
        return response()->json(['category' => $category]);
    }

    public function update(Request $request, $id)
    {
        $category = ExpenseCategory::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update($data);

        return response()->json([
            'message' => 'Expense category updated successfully',
            'category' => $category
        ]);
    }

    public function destroy($id)
    {
        $category = ExpenseCategory::findOrFail($id);

        // Check if category has expenses
        if ($category->expenses()->count() > 0) {
            return response()->json([
                'error' => 'Cannot delete category with existing expenses'
            ], 400);
        }

        $category->delete();

        return response()->json([
            'message' => 'Expense category deleted successfully'
        ]);
    }
}
