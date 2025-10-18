<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {

        // PRODUCTS
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products','category_id')) {
                    $table->foreignId('category_id')->nullable()->after('id')
                          ->constrained('categories')->nullOnDelete();
                }
                if (!Schema::hasColumn('products','image_url')) {
                    $table->string('image_url',255)->nullable()->after('price_cents');
                }
                if (!Schema::hasColumn('products','description')) {
                    $table->text('description')->nullable()->after('image_url');
                }
                if (!Schema::hasColumn('products','is_active')) {
                    $table->boolean('is_active')->default(true)->after('description');
                }
                if (!Schema::hasColumn('products','is_popular')) {
                    $table->boolean('is_popular')->default(false)->after('is_active');
                }
            });
        }

        // ORDERS
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders','order_no')) {
                    $table->string('order_no',30)->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('orders','note')) {
                    $table->text('note')->nullable()->after('status');
                }
                if (!Schema::hasColumn('orders','meta')) {
                    $table->json('meta')->nullable()->after('note');
                }
                if (!Schema::hasColumn('orders','channel')) {
                    $table->enum('channel',['counter','delivery','pickup','internal'])
                          ->default('counter')->after('status');
                }
                // monetary columns if missing
                foreach ([
                    'subtotal_cents','tax_cents','discount_cents',
                    'service_charge_cents','total_cents','paid_cents','change_cents'
                ] as $col) {
                    if (!Schema::hasColumn('orders',$col)) {
                        $table->unsignedInteger($col)->default(0);
                    }
                }
            });
        }

        // PAYMENTS
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (!Schema::hasColumn('payments','order_id')) {
                    $table->foreignId('order_id')->after('id')
                          ->constrained('orders')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('payments','method')) {
                    $table->enum('method',['cash','card','transfer','wallet'])
                          ->default('cash')->after('order_id');
                }
                if (!Schema::hasColumn('payments','amount_cents')) {
                    $table->unsignedInteger('amount_cents')->default(0)->after('method');
                }
                if (!Schema::hasColumn('payments','reference')) {
                    $table->string('reference',100)->nullable()->after('amount_cents');
                }
                if (!Schema::hasColumn('payments','status')) {
                    $table->enum('status',['captured','void','refund'])
                          ->default('captured')->after('reference');
                }
                if (!Schema::hasColumn('payments','captured_at')) {
                    $table->dateTime('captured_at')->nullable()->after('status');
                }
                // helpful index
                if (!collect(Schema::getConnection()->getDoctrineSchemaManager()
                        ->listTableIndexes('payments'))->has('payments_order_id_index')) {
                    $table->index('order_id','payments_order_id_index');
                }
            });
        }

        // ORDER ITEMS (add unique guard if desired)
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                // (optional) add composite unique key
                try {
                    $table->unique(['order_id','item_type','item_id','name'],'oi_order_item_unique');
                } catch (\Throwable $e) { /* ignore if exists */ }
            });
        }

        // Add more tables here as needed (stock_movements, lots, etc.)
    }

    public function down(): void {
        // usually no-op: we don’t want to drop columns in a “baseline” aligner
    }
};
