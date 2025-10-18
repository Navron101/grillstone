<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiptLine extends Model
{
    protected $fillable = ['goods_receipt_id','product_id','qty','unit_cost_cents','expires_at','lot_code'];
    protected $casts = ['expires_at'=>'datetime'];
}
