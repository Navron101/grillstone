<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = ['product_id','location_id','qty_delta','reason','ref_type','ref_id','unit_cost_cents','meta'];
    protected $casts = ['meta'=>'array'];
}
