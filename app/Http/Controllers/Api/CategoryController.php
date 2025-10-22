<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = DB::table('categories')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
        ]);

        $id = DB::table('categories')->insertGetId([
            'name' => $data['name'],
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['id' => $id], 201);
    }

    public function update($id, Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $id,
        ]);

        DB::table('categories')
            ->where('id', $id)
            ->update([
                'name' => $data['name'],
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true]);
    }

    public function destroy($id)
    {
        // Check if category is in use
        $inUse = DB::table('products')
            ->where('category', DB::table('categories')->where('id', $id)->value('name'))
            ->exists();

        if ($inUse) {
            return response()->json([
                'error' => 'Cannot delete category that is in use by products'
            ], 400);
        }

        DB::table('categories')->where('id', $id)->delete();

        return response()->json(['ok' => true]);
    }
}
