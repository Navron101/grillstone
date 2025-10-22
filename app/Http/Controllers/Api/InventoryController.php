<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Imports\IngredientsImport;
use App\Imports\ProductsImport;
use App\Exports\IngredientsTemplateExport;
use App\Exports\ProductsTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class InventoryController extends Controller
{
    // INGREDIENTS
    public function listIngredients()
    {
        return response()->json(
            DB::table('products')->where('type','ingredient')->orderBy('name')->get()
        );
    }

    public function createIngredient(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'unit_name' => 'nullable|string|max:20',
            'price_cents' => 'nullable|integer|min:0',
        ]);

        $id = DB::table('products')->insertGetId([
            'name' => $data['name'],
            'unit_name' => $data['unit_name'] ?? null,
            'type' => 'ingredient',
            'price_cents' => $data['price_cents'] ?? 0,
            'created_at'=>now(), 'updated_at'=>now(),
        ]);

        return response()->json(['id'=>$id], 201);
    }

    public function updateIngredient($id, Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'unit_name' => 'nullable|string|max:20',
            'price_cents' => 'nullable|integer|min:0',
        ]);

        DB::table('products')->where('id',$id)->where('type','ingredient')->update([
            'name' => $data['name'],
            'unit_name' => $data['unit_name'] ?? null,
            'price_cents' => $data['price_cents'] ?? 0,
            'updated_at'=>now(),
        ]);

        return response()->json(['ok'=>true]);
    }

    public function deleteIngredient($id)
    {
        DB::table('products')->where('id',$id)->where('type','ingredient')->delete();
        return response()->json(['ok'=>true]);
    }

    // DISHES
    public function listDishes()
    {
        return response()->json(
            DB::table('products')->where('type','dish')->orderBy('name')->get()
        );
    }

    public function createDish(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'price_cents' => 'required|integer|min:0',
        ]);
        $id = DB::table('products')->insertGetId([
            'name'=>$data['name'],
            'price_cents'=>$data['price_cents'],
            'type'=>'dish',
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        return response()->json(['id'=>$id], 201);
    }

    public function updateDish($id, Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'price_cents' => 'required|integer|min:0',
        ]);
        DB::table('products')->where('id',$id)->where('type','dish')->update([
            'name'=>$data['name'], 'price_cents'=>$data['price_cents'], 'updated_at'=>now(),
        ]);
        return response()->json(['ok'=>true]);
    }

    public function deleteDish($id)
    {
        DB::table('products')->where('id',$id)->where('type','dish')->delete();
        return response()->json(['ok'=>true]);
    }

    // VARIANTS & COMPONENTS
public function listVariants($dishId)
{
    $rows = DB::table('recipe_variants as v')
        ->join('products as p','p.id','=','v.product_id') // product_id is the dish id
        ->where('v.product_id', $dishId)
        ->orderByDesc('v.is_default')
        ->orderBy('v.id')
        ->get([
            'v.id',
            'v.name',
            'v.is_default',
            DB::raw('COALESCE(v.price_cents, p.price_cents) as price_cents'),
        ]);

    return response()->json($rows);
}

    public function createVariant($dishId, Request $r)
{
    $data = $r->validate([
        'name' => 'required|string|max:60',
        'is_default' => 'boolean',
        'price_cents' => 'nullable|integer|min:0',
    ]);

    return DB::transaction(function () use ($dishId, $data) {
        if (!empty($data['is_default'])) {
            DB::table('recipe_variants')->where('product_id',$dishId)->update(['is_default'=>false]);
        }
        $id = DB::table('recipe_variants')->insertGetId([
            'product_id'  => $dishId,
            'name'        => $data['name'],
            'is_default'  => (bool)($data['is_default'] ?? false),
            'price_cents' => $data['price_cents'] ?? null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        return response()->json(['id'=>$id], 201);
    });
}

    public function updateVariant($dishId, $variantId, Request $r)
    {
        $data = $r->validate([
            'name'=>'required|string|max:60',
            'is_default'=>'boolean',
        ]);
        return DB::transaction(function () use ($dishId, $variantId, $data) {
            if (($data['is_default'] ?? false) === true) {
                DB::table('recipe_variants')->where('product_id',$dishId)->update(['is_default'=>false]);
            }
            DB::table('recipe_variants')->where('id',$variantId)->where('product_id',$dishId)->update([
                'name'=>$data['name'], 'is_default'=>(bool)($data['is_default'] ?? false),
                'updated_at'=>now(),
            ]);
            return response()->json(['ok'=>true]);
        });
    }

    public function deleteVariant($dishId, $variantId)
    {
        DB::table('recipe_variants')->where('id',$variantId)->where('product_id',$dishId)->delete();
        return response()->json(['ok'=>true]);
    }

    public function listComponents($variantId)
    {
        $rows = DB::table('recipe_components as c')
            ->join('products as p','p.id','=','c.ingredient_product_id')
            ->where('c.variant_id',$variantId)
            ->orderBy('p.name')
            ->get(['c.id','c.qty_per_unit','c.unit_name','p.id as ingredient_id','p.name as ingredient_name','p.unit_name as default_unit']);
        return response()->json($rows);
    }

    public function upsertComponent($variantId, Request $r)
    {
        $data = $r->validate([
            'ingredient_product_id'=>'required|integer|exists:products,id',
            'qty_per_unit'=>'required|numeric|min:0.0001',
            'unit_name'=>'nullable|string|max:20',
        ]);

        $existing = DB::table('recipe_components')
            ->where('variant_id',$variantId)
            ->where('ingredient_product_id',$data['ingredient_product_id'])
            ->first();

        if ($existing) {
            DB::table('recipe_components')->where('id',$existing->id)->update([
                'qty_per_unit'=>$data['qty_per_unit'],
                'unit_name'=>$data['unit_name'] ?? null,
                'updated_at'=>now(),
            ]);
            return response()->json(['id'=>$existing->id, 'updated'=>true]);
        } else {
            $id = DB::table('recipe_components')->insertGetId([
                'variant_id'=>$variantId,
                'ingredient_product_id'=>$data['ingredient_product_id'],
                'qty_per_unit'=>$data['qty_per_unit'],
                'unit_name'=>$data['unit_name'] ?? null,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
            return response()->json(['id'=>$id, 'created'=>true], 201);
        }
    }

    public function deleteComponent($variantId, $componentId)
    {
        DB::table('recipe_components')->where('id',$componentId)->where('variant_id',$variantId)->delete();
        return response()->json(['ok'=>true]);
    }

    // PRODUCTS (drinks, snacks, etc.)
    public function listProducts()
    {
        return response()->json(
            DB::table('products')->where('type','product')->orderBy('name')->get()
        );
    }

    public function createProduct(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'category' => 'nullable|string|max:64',
            'price_cents' => 'required|integer|min:0',
            'combo_price_cents' => 'nullable|integer|min:0',
            'unit_name' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'image_url' => 'nullable|string|max:255',
            'low_stock_threshold' => 'nullable|numeric|min:0',
        ]);

        $id = DB::table('products')->insertGetId([
            'name' => $data['name'],
            'category' => $data['category'] ?? null,
            'price_cents' => $data['price_cents'],
            'combo_price_cents' => $data['combo_price_cents'] ?? null,
            'unit_name' => $data['unit_name'] ?? null,
            'description' => $data['description'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'low_stock_threshold' => $data['low_stock_threshold'] ?? 5,
            'type' => 'product',
            'is_active' => true,
            'created_at'=>now(),
            'updated_at'=>now(),
        ]);

        return response()->json(['id'=>$id], 201);
    }

    public function updateProduct($id, Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'category' => 'nullable|string|max:64',
            'price_cents' => 'required|integer|min:0',
            'combo_price_cents' => 'nullable|integer|min:0',
            'unit_name' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'image_url' => 'nullable|string|max:255',
            'low_stock_threshold' => 'nullable|numeric|min:0',
        ]);

        DB::table('products')->where('id',$id)->where('type','product')->update([
            'name' => $data['name'],
            'category' => $data['category'] ?? null,
            'price_cents' => $data['price_cents'],
            'combo_price_cents' => $data['combo_price_cents'] ?? null,
            'unit_name' => $data['unit_name'] ?? null,
            'description' => $data['description'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'low_stock_threshold' => $data['low_stock_threshold'] ?? 5,
            'updated_at'=>now(),
        ]);

        return response()->json(['ok'=>true]);
    }

    public function deleteProduct($id)
    {
        DB::table('products')->where('id',$id)->where('type','product')->delete();
        return response()->json(['ok'=>true]);
    }

    // EXCEL UPLOADS
    public function uploadIngredients(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new IngredientsImport();
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();
            $successCount = $import->getSuccessCount();

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$successCount} ingredients",
                'imported_count' => $successCount,
                'errors' => $errors,
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    public function downloadIngredientsTemplate()
    {
        return Excel::download(new IngredientsTemplateExport, 'ingredients-template.xlsx');
    }

    public function uploadProducts(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new ProductsImport();
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();
            $successCount = $import->getSuccessCount();

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$successCount} products",
                'imported_count' => $successCount,
                'errors' => $errors,
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    public function downloadProductsTemplate()
    {
        return Excel::download(new ProductsTemplateExport, 'products-template.xlsx');
    }

    /**
     * Upload image for a product/dish
     */
    public function uploadImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // 2MB max
        ]);

        $product = DB::table('products')->where('id', $id)->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'product_' . $id . '_' . time() . '.' . $image->getClientOriginalExtension();

            // Store in public/images/products directory
            $path = $image->storeAs('images/products', $filename, 'public');

            // Update product with image URL
            DB::table('products')->where('id', $id)->update([
                'image_url' => '/storage/' . $path,
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'image_url' => '/storage/' . $path,
                'message' => 'Image uploaded successfully'
            ]);
        }

        return response()->json(['error' => 'No image file provided'], 400);
    }

    /**
     * Update image URL for a product (for external URLs)
     */
    public function updateImageUrl(Request $request, $id)
    {
        $request->validate([
            'image_url' => 'required|string|max:500',
        ]);

        $product = DB::table('products')->where('id', $id)->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        DB::table('products')->where('id', $id)->update([
            'image_url' => $request->image_url,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'image_url' => $request->image_url,
            'message' => 'Image URL updated successfully'
        ]);
    }

    /**
     * Delete product image
     */
    public function deleteImage($id)
    {
        $product = DB::table('products')->where('id', $id)->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // If it's a local file, delete it
        if ($product->image_url && str_starts_with($product->image_url, '/storage/')) {
            $filePath = str_replace('/storage/', '', $product->image_url);
            \Storage::disk('public')->delete($filePath);
        }

        DB::table('products')->where('id', $id)->update([
            'image_url' => null,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully'
        ]);
    }
}
