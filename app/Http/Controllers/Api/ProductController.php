<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller {
    public function index(Request $request) {
        $active = \App\Models\Product::with('category')
            ->where('is_active', true)
            ->orderBy('is_popular','desc')
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id'=>$p->id,
                'name'=>$p->name,
                'price'=>$p->price_cents,   // cents
                'category'=>$p->category?->name,
                'img'=>$p->image_url,
                'description'=>$p->description,
                'popular'=>$p->is_popular,
            ]);

        $categories = Category::orderBy('name')->pluck('name');

        return response()->json([
            'products'=>$active,
            'categories'=>$categories,
        ]);
    }
}
