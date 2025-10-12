<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller {
    public function store(Request $request) {
        $data = $request->validate([
            'items'=>'required|array|min:1',
            'items.*.product_id'=>'required|integer|exists:products,id',
            'items.*.qty'=>'required|integer|min:1',
            'discount_percent'=>'nullable|numeric|min:0|max:50',
            'payment'=> 'required|array',
            'payment.method'=>'required|string', // Cash|Card|Digital
            'payment.tendered_cents'=>'nullable|integer|min:0'
        ]);

        return DB::transaction(function () use ($data) {
            $taxRate = 0.15;

            $lines = collect($data['items'])->map(function ($line) {
                $p = Product::findOrFail($line['product_id']);
                $price = $p->price_cents;
                return [
                    'product'=>$p,
                    'qty'=>$line['qty'],
                    'price_cents'=>$price,
                    'line_total_cents'=>$price * $line['qty']
                ];
            });

            $subtotal = $lines->sum('line_total_cents');
            $discount = (int) round($subtotal * (($data['discount_percent'] ?? 0)/100));
            $tax = (int) round(($subtotal - $discount) * $taxRate);
            $total = $subtotal - $discount + $tax;

            $order = Order::create([
                'order_no' => 'GS-'.Str::upper(Str::random(6)),
                'subtotal_cents'=>$subtotal,
                'tax_cents'=>$tax,
                'discount_cents'=>$discount,
                'total_cents'=>$total,
                'status'=>'paid',
                'meta'=>['cashier'=>auth()->user()->name ?? 'Cashier'],
            ]);

            foreach ($lines as $l) {
                OrderItem::create([
                    'order_id'=>$order->id,
                    'product_id'=>$l['product']->id,
                    'qty'=>$l['qty'],
                    'price_cents'=>$l['price_cents'],
                    'line_total_cents'=>$l['line_total_cents'],
                ]);
            }

            $tendered = (int) ($data['payment']['tendered_cents'] ?? $total);
            $change = max(0, $tendered - $total);

            Payment::create([
                'order_id'=>$order->id,
                'method'=>$data['payment']['method'],
                'amount_cents'=>$tendered - $change,
                'change_cents'=>$change,
            ]);

            return response()->json([
                'order_no'=>$order->order_no,
                'total_cents'=>$total,
                'change_cents'=>$change,
            ], 201);
        });
    }

    public function hold(Request $request) {
        $data = $request->validate([
            'items'=>'required|array|min:1',
            'items.*.product_id'=>'required|integer|exists:products,id',
            'items.*.qty'=>'required|integer|min:1',
            'discount_percent'=>'nullable|numeric|min:0|max:50',
            'note'=>'nullable|string|max:255'
        ]);

        $subtotal = collect($data['items'])->sum(function ($l) {
            $p = \App\Models\Product::findOrFail($l['product_id']);
            return $p->price_cents * $l['qty'];
        });
        $discount = (int) round($subtotal * (($data['discount_percent'] ?? 0)/100));
        $tax = (int) round(($subtotal - $discount) * 0.15);
        $total = $subtotal - $discount + $tax;

        $order = Order::create([
            'order_no'=>'GS-'.Str::upper(Str::random(6)),
            'subtotal_cents'=>$subtotal,
            'tax_cents'=>$tax,
            'discount_cents'=>$discount,
            'total_cents'=>$total,
            'status'=>'held',
            'note'=>$data['note'] ?? null,
        ]);

        foreach ($data['items'] as $l) {
            $p = \App\Models\Product::findOrFail($l['product_id']);
            OrderItem::create([
                'order_id'=>$order->id,
                'product_id'=>$p->id,
                'qty'=>$l['qty'],
                'price_cents'=>$p->price_cents,
                'line_total_cents'=>$p->price_cents * $l['qty'],
            ]);
        }

        return response()->json(['order_no'=>$order->order_no], 201);
    }

    public function sendToKitchen(Request $request) {
        // Normally you'd publish to a queue/printer; here we just acknowledge.
        return response()->json(['ok'=>true]);
    }
}
