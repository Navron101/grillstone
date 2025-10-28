<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * List all vendors
     */
    public function index()
    {
        $vendors = Vendor::orderBy('name')->get();

        return response()->json(['vendors' => $vendors]);
    }

    /**
     * Get single vendor
     */
    public function show($id)
    {
        $vendor = Vendor::with('purchaseOrders')->findOrFail($id);

        return response()->json(['vendor' => $vendor]);
    }

    /**
     * Create vendor
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
        ]);

        $vendor = Vendor::create($data);

        return response()->json([
            'message' => 'Vendor created successfully',
            'vendor' => $vendor
        ], 201);
    }

    /**
     * Update vendor
     */
    public function update(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
        ]);

        $vendor->update($data);

        return response()->json([
            'message' => 'Vendor updated successfully',
            'vendor' => $vendor
        ]);
    }

    /**
     * Delete vendor
     */
    public function destroy($id)
    {
        $vendor = Vendor::findOrFail($id);

        if ($vendor->purchaseOrders()->count() > 0) {
            return response()->json([
                'error' => 'Cannot delete vendor with existing purchase orders'
            ], 400);
        }

        $vendor->delete();

        return response()->json([
            'message' => 'Vendor deleted successfully'
        ]);
    }
}
