<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Combo;
use App\Models\ComboItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ComboController extends Controller
{
    /**
     * List all combos
     */
    public function index(Request $request)
    {
        $query = Combo::with(['items.product', 'category']);

        // Filter by active status
        if ($request->has('active_only')) {
            $query->where('is_active', true);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $combos = $query->orderBy('name')->get();

        return response()->json($combos);
    }

    /**
     * Get a single combo with items
     */
    public function show($id)
    {
        $combo = Combo::with(['items.product', 'category'])->findOrFail($id);

        return response()->json($combo);
    }

    /**
     * Create a new combo
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'image_url' => 'nullable|string',
            'is_active' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Create the combo
            $combo = Combo::create([
                'name' => $request->name,
                'description' => $request->description,
                'price_cents' => (int) ($request->price * 100),
                'category_id' => $request->category_id,
                'image_url' => $request->image_url,
                'is_active' => $request->is_active ?? true,
            ]);

            // Create combo items
            foreach ($request->items as $item) {
                ComboItem::create([
                    'combo_id' => $combo->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();

            return response()->json($combo->load(['items.product', 'category']), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create combo', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing combo
     */
    public function update(Request $request, $id)
    {
        $combo = Combo::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'image_url' => 'nullable|string',
            'is_active' => 'boolean',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Update combo details
            $updateData = [];
            if ($request->has('name')) $updateData['name'] = $request->name;
            if ($request->has('description')) $updateData['description'] = $request->description;
            if ($request->has('price')) $updateData['price_cents'] = (int) ($request->price * 100);
            if ($request->has('category_id')) $updateData['category_id'] = $request->category_id;
            if ($request->has('image_url')) $updateData['image_url'] = $request->image_url;
            if ($request->has('is_active')) $updateData['is_active'] = $request->is_active;

            $combo->update($updateData);

            // Update items if provided
            if ($request->has('items')) {
                // Delete existing items
                $combo->items()->delete();

                // Create new items
                foreach ($request->items as $item) {
                    ComboItem::create([
                        'combo_id' => $combo->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }
            }

            DB::commit();

            return response()->json($combo->fresh(['items.product', 'category']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update combo', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a combo
     */
    public function destroy($id)
    {
        try {
            $combo = Combo::findOrFail($id);
            $combo->delete(); // This will cascade delete combo items

            return response()->json(['message' => 'Combo deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete combo', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Toggle combo active status
     */
    public function toggleActive($id)
    {
        try {
            $combo = Combo::findOrFail($id);
            $combo->is_active = !$combo->is_active;
            $combo->save();

            return response()->json($combo);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to toggle status', 'message' => $e->getMessage()], 500);
        }
    }
}
