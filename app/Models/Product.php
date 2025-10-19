<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model {
    protected $fillable = [
        'category_id','name','price_cents','image_url','description','is_active','is_popular','type','unit_name','low_stock_threshold'
    ];
    protected $casts = ['is_active'=>'bool','is_popular'=>'bool'];
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
}
