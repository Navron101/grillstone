<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'items'                      => 'required|array|min:1',
            'items.*.product_id'         => 'required|integer|exists:products,id',
            'items.*.variant_id'         => 'nullable|integer|exists:recipe_variants,id',
            'items.*.qty'                => 'required|integer|min:1',
            'discount_percent'           => 'nullable|numeric|min:0|max:50',
            'loyalty_employee_id'        => 'nullable|integer|exists:loyalty_employees,id',
            'payment'                    => 'required|array',
            'payment.method'             => 'required|string', // Cash|Card|Digital
            'payment.tendered_cents'     => 'nullable|integer|min:0',
            'location_id'                => 'nullable|integer|min:1',
        ]);

        return DB::transaction(function () use ($data) {
            $taxEnabled = Setting::get('tax_enabled', true);
            $taxRate    = $taxEnabled ? (Setting::get('tax_rate', 15) / 100) : 0;
            $locationId = (int)($data['location_id'] ?? 1);

            // Resolve price for each line (variant price overrides product price)
            $lines = collect($data['items'])->map(function ($line) {
                /** @var Product $p */
                $p = Product::findOrFail($line['product_id']);

                $variantId    = $line['variant_id'] ?? null;
                $variantPrice = null;

                if ($variantId) {
                    $variantPrice = DB::table('recipe_variants')
                        ->where('id', $variantId)
                        ->value('price_cents');
                }

                $price = (int) ($variantPrice ?? $p->price_cents);

                return [
                    'product'           => $p,
                    'variant_id'        => $variantId,
                    'qty'               => (int)$line['qty'],
                    'price_cents'       => $price,
                    'line_total_cents'  => $price * (int)$line['qty'],
                ];
            });

            $subtotal = (int) $lines->sum('line_total_cents');
            $discount = (int) round($subtotal * (($data['discount_percent'] ?? 0) / 100));
            $tax      = (int) round(($subtotal - $discount) * $taxRate);
            $total    = (int) ($subtotal - $discount + $tax);

            /** @var Order $order */
            $order = Order::create([
                'order_no'        => 'GS-' . Str::upper(Str::random(6)),
                'subtotal_cents'  => $subtotal,
                'tax_cents'       => $tax,
                'discount_cents'  => $discount,
                'total_cents'     => $total,
                'status'          => 'paid',
                'meta'            => ['cashier' => auth()->user()->name ?? 'Cashier'],
            ]);

            foreach ($lines as $l) {
                $payload = [
                    'order_id'         => $order->id,
                    'item_type'        => $l['product']->type ?? 'product',
                    'item_id'          => $l['product']->id,
                    'name'             => $l['product']->name,
                    'qty'              => $l['qty'],
                    'unit_price_cents' => $l['price_cents'],
                    'line_total_cents' => $l['line_total_cents'],
                    'tax_rate'         => $taxRate,
                ];

                // Add variant info to meta if exists
                if (!empty($l['variant_id'])) {
                    $payload['meta'] = json_encode(['variant_id' => (int)$l['variant_id']]);
                }

                OrderItem::create($payload);
            }

            $tendered = (int) ($data['payment']['tendered_cents'] ?? $total);
            $change   = max(0, $tendered - $total);

            Payment::create([
                'order_id'     => $order->id,
                'method'       => $data['payment']['method'],
                'amount_cents' => $tendered - $change,
                'change_cents' => $change,
            ]);

            // --- COGS / inventory deduction ---
            $cogsCents = 0;

            // Prefer the RecipeConsumption service if available (variant-aware),
            // otherwise fall back to FinalizeSale.
            try {
                if (class_exists(\App\Services\Sales\RecipeConsumption::class)) {
                    $cogsCents = app(\App\Services\Sales\RecipeConsumption::class)
                        ->apply($order->load('items'), $locationId);
                } elseif (class_exists(\App\Services\Sales\FinalizeSale::class)) {
                    $cogsCents = app(\App\Services\Sales\FinalizeSale::class)
                        ->applyInventory($order->load('items'), $locationId);
                }
            } catch (\Throwable $e) {
                // Don't break checkout if COGS fails — log it and continue
                logger()->error('COGS failed: '.$e->getMessage(), ['order_id' => $order->id]);
            }

            // Save COGS into order meta
            $meta = $order->meta ?? [];
            $meta['cogs_cents'] = (int) $cogsCents;
            $order->meta = $meta;
            $order->save();

            // Create loyalty transaction if employee was provided
            if (!empty($data['loyalty_employee_id'])) {
                try {
                    $loyaltyEmployee = \App\Models\LoyaltyEmployee::find($data['loyalty_employee_id']);
                    if ($loyaltyEmployee && class_exists(\App\Services\LoyaltyService::class)) {
                        $loyaltyService = app(\App\Services\LoyaltyService::class);
                        $loyaltyService->applyDiscount($loyaltyEmployee, $order);
                    }
                } catch (\Throwable $e) {
                    // Don't break checkout if loyalty fails - log it
                    logger()->error('Loyalty transaction failed: '.$e->getMessage(), ['order_id' => $order->id]);
                }
            }

            return response()->json([
                'order_no'     => $order->order_no,
                'total_cents'  => $total,
                'change_cents' => $change,
                'cogs_cents'   => (int) $cogsCents,
            ], 201);
        });
    }

    public function hold(Request $request)
    {
        $data = $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|integer|exists:products,id',
            'items.*.qty'         => 'required|integer|min:1',
            'discount_percent'    => 'nullable|numeric|min:0|max:50',
            'note'                => 'nullable|string|max:255',
        ]);

        $subtotal = collect($data['items'])->sum(function ($l) {
            $p = Product::findOrFail($l['product_id']);
            return (int)$p->price_cents * (int)$l['qty'];
        });

        $taxEnabled = Setting::get('tax_enabled', true);
        $taxRate    = $taxEnabled ? (Setting::get('tax_rate', 15) / 100) : 0;
        $discount = (int) round($subtotal * (($data['discount_percent'] ?? 0) / 100));
        $tax      = (int) round(($subtotal - $discount) * $taxRate);
        $total    = (int) ($subtotal - $discount + $tax);

        $order = Order::create([
            'order_no'        => 'GS-' . Str::upper(Str::random(6)),
            'subtotal_cents'  => $subtotal,
            'tax_cents'       => $tax,
            'discount_cents'  => $discount,
            'total_cents'     => $total,
            'status'          => 'held',
            'note'            => $data['note'] ?? null,
        ]);

        foreach ($data['items'] as $l) {
            $p = Product::findOrFail($l['product_id']);
            OrderItem::create([
                'order_id'         => $order->id,
                'item_type'        => $p->type ?? 'product',
                'item_id'          => $p->id,
                'name'             => $p->name,
                'qty'              => (int)$l['qty'],
                'unit_price_cents' => (int)$p->price_cents,
                'line_total_cents' => (int)$p->price_cents * (int)$l['qty'],
                'tax_rate'         => $taxRate,
            ]);
        }

        return response()->json(['order_no' => $order->order_no], 201);
    }

    public function sendToKitchen(Request $request)
    {
        // Normally you'd publish to a queue/printer; here we just acknowledge.
        return response()->json(['ok' => true]);
    }
}
