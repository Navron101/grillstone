<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
}
