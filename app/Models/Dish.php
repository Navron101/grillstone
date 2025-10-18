<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dish extends Model
{
    protected $fillable = ['name','price_cents','is_active','meta'];
    protected $casts = ['meta'=>'array','is_active'=>'bool'];
    public function ingredients(): HasMany { return $this->hasMany(DishIngredient::class); }
}
