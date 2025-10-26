<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'supplier_name',
        'invoice_date',
        'original_filename',
        'file_path',
        'status',
        'ocr_raw_data',
        'extracted_items',
        'total_amount_cents',
        'processed_by',
        'processed_at',
        'goods_receipt_id',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'ocr_raw_data' => 'array',
        'extracted_items' => 'array',
        'processed_at' => 'datetime',
    ];

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }
}
