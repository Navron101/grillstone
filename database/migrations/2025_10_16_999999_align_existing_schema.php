<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {

        // ===== PRODUCTS deltas =====
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products','sku')) $table->string('sku',64)->nullable()->unique()->after('name');
                if (!Schema::hasColumn('products','barcode')) $table->string('barcode',64)->nullable()->unique()->after('sku');
                if (!Schema::hasColumn('products','category_id')) $table->foreignId('category_id')->nullable()->after('barcode')->constrained('categories')->nullOnDelete();
                if (!Schema::hasColumn('products','unit')) $table->string('unit',16)->default('ea')->after('category_id');
                if (!Schema::hasColumn('products','is_stock_item')) $table->boolean('is_stock_item')->default(true)->after('unit');
                if (!Schema::hasColumn('products','is_active')) $table->boolean('is_active')->default(true)->after('is_stock_item');
                if (!Schema::hasColumn('products','tax_rate')) $table->decimal('tax_rate',5,2)->default(0)->after('price_cents');
                if (!Schema::hasColumn('products','cost_cents')) $table->unsignedInteger('cost_cents')->nullable()->after('tax_rate');
                if (!Schema::hasColumn('products','image_url')) $table->string('image_url',255)->nullable()->after('cost_cents');
                if (!Schema::hasColumn('products','description')) $table->text('description')->nullable()->after('image_url');
            });
        }

        // ===== ORDERS deltas =====
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders','order_no')) $table->string('order_no',30)->nullable()->unique()->after('id');
                if (!Schema::hasColumn('orders','location_id')) $table->foreignId('location_id')->nullable()->after('order_no')->constrained('locations')->nullOnDelete();
                if (!Schema::hasColumn('orders','status')) $table->enum('status',['open','void','paid','refunded'])->default('open')->after('location_id');
                if (!Schema::hasColumn('orders','channel')) $table->enum('channel',['counter','delivery','pickup','internal'])->default('counter')->after('status');

                foreach (['subtotal_cents','tax_cents','discount_cents','service_charge_cents','total_cents','paid_cents','change_cents'] as $col) {
                    if (!Schema::hasColumn('orders',$col)) $table->unsignedInteger($col)->default(0);
                }
                if (!Schema::hasColumn('orders','note')) $table->text('note')->nullable();
                if (!Schema::hasColumn('orders','meta')) $table->json('meta')->nullable();
            });
        }

        // ===== ORDER ITEMS deltas =====
if (Schema::hasTable('order_items')) {

    // Add unit_price_cents if missing (position after qty if available)
    if (!Schema::hasColumn('order_items','unit_price_cents')) {
        Schema::table('order_items', function (Blueprint $table) {
            // Try to position after qty if it exists; otherwise append
            if (Schema::hasColumn('order_items','qty')) {
                $table->unsignedInteger('unit_price_cents')->default(0)->after('qty');
            } else {
                $table->unsignedInteger('unit_price_cents')->default(0);
            }
        });
    }

    // Add tax_rate; place after unit_price_cents if present, else after qty, else append
    if (!Schema::hasColumn('order_items','tax_rate')) {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items','unit_price_cents')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('unit_price_cents');
            } elseif (Schema::hasColumn('order_items','qty')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('qty');
            } else {
                $table->decimal('tax_rate', 5, 2)->default(0);
            }
        });
    }

    // Add line_total_cents if missing; try to place after tax_rate/unit_price_cents
    if (!Schema::hasColumn('order_items','line_total_cents')) {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items','tax_rate')) {
                $table->unsignedInteger('line_total_cents')->after('tax_rate');
            } elseif (Schema::hasColumn('order_items','unit_price_cents')) {
                $table->unsignedInteger('line_total_cents')->after('unit_price_cents');
            } else {
                $table->unsignedInteger('line_total_cents');
            }
        });
    }

    // Add meta JSON if missing; try to place after line_total_cents
    if (!Schema::hasColumn('order_items','meta')) {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items','line_total_cents')) {
                $table->json('meta')->nullable()->after('line_total_cents');
            } else {
                $table->json('meta')->nullable();
            }
        });
    }

    // Optional composite unique (ignore if already exists)
    try {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unique(['order_id','item_type','item_id','name'],'oi_order_item_unique');
        });
    } catch (\Throwable $e) { /* index already exists or unsupported */ }
}


        // ===== PAYMENTS deltas =====
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (!Schema::hasColumn('payments','order_id')) $table->foreignId('order_id')->after('id')->constrained('orders')->cascadeOnDelete();
                if (!Schema::hasColumn('payments','method')) $table->enum('method',['cash','card','transfer','wallet'])->default('cash')->after('order_id');
                if (!Schema::hasColumn('payments','amount_cents')) $table->unsignedInteger('amount_cents')->default(0)->after('method');
                if (!Schema::hasColumn('payments','reference')) $table->string('reference',100)->nullable()->after('amount_cents');
                if (!Schema::hasColumn('payments','status')) $table->enum('status',['captured','void','refund'])->default('captured')->after('reference');
                if (!Schema::hasColumn('payments','captured_at')) $table->dateTime('captured_at')->nullable()->after('status');
                // index: attempt and ignore if already present (no DBAL)
                try { $table->index('order_id','payments_order_id_index'); } catch (\Throwable $e) {}
            });
        }

        // ===== LOCATIONS (create if missing) =====
        if (!Schema::hasTable('locations')) {
            Schema::create('locations', function (Blueprint $table) {
                $table->id();
                $table->string('name',100);
                $table->string('type',50)->default('store');
                $table->timestamps();
            });
        }

        // ===== STOCK tables (create if missing) =====
        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products');
                $table->foreignId('location_id')->constrained('locations');
                $table->decimal('qty_delta',14,4);
                $table->enum('reason',['receive','sale','variance','transfer','waste','production']);
                $table->string('ref_type',50)->nullable();
                $table->unsignedBigInteger('ref_id')->nullable();
                $table->unsignedInteger('unit_cost_cents')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['product_id','location_id','created_at']);
            });
        }

        if (!Schema::hasTable('inventory_lots')) {
            Schema::create('inventory_lots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products');
                $table->foreignId('location_id')->constrained('locations');
                $table->decimal('qty_on_hand',14,4)->default(0);
                $table->unsignedInteger('unit_cost_cents')->nullable();
                $table->date('expires_at')->nullable();
                $table->string('lot_code',64)->nullable();
                $table->timestamps();
                $table->index(['product_id','location_id']);
            });
        }
    }

    public function down(): void { /* no destructive down for baseline */ }
};
