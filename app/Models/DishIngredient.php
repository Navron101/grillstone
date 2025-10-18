<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DishIngredient extends Model
{
    protected $fillable = ['dish_id','product_id','qty','unit','yield_factor'];
}
