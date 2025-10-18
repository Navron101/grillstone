<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceipt extends Model
{
    protected $fillable = ['location_id','vendor_id','status','external_ref','meta'];
    protected $casts = ['meta'=>'array'];
    public function lines(): HasMany { return $this->hasMany(GoodsReceiptLine::class); }
}
