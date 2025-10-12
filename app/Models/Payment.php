<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model {
    protected $fillable = ['order_id','method','amount_cents','change_cents','meta'];
    protected $casts = ['meta'=>'array'];
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
