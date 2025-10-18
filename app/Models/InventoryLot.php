<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLot extends Model
{
    protected $fillable = ['product_id','location_id','qty_on_hand','unit_cost_cents','expires_at','lot_code'];
    protected $casts = ['expires_at'=>'datetime'];
}
