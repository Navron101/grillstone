<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RestaurantDataSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data (in order of foreign key dependencies)
        DB::table('recipe_components')->delete();
        DB::table('recipe_variants')->delete();
        DB::table('stock_movements')->delete();
        DB::table('goods_receipt_lines')->delete();
        DB::table('inventory_lots')->delete();
        DB::table('order_items')->delete();
        DB::table('products')->delete();
        DB::table('categories')->delete();

        // Categories
        $categories = [
            ['name' => 'Appetizers', 'is_active' => true],
            ['name' => 'Burgers & Sandwiches', 'is_active' => true],
            ['name' => 'Main Courses', 'is_active' => true],
            ['name' => 'Grilled Specialties', 'is_active' => true],
            ['name' => 'Sides', 'is_active' => true],
            ['name' => 'Salads', 'is_active' => true],
            ['name' => 'Beverages', 'is_active' => true],
            ['name' => 'Desserts', 'is_active' => true],
            ['name' => 'Kids Menu', 'is_active' => true],
        ];

        $categoryIds = [];
        foreach ($categories as $cat) {
            $categoryIds[$cat['name']] = DB::table('categories')->insertGetId([
                'name' => $cat['name'],
                'is_active' => $cat['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // === INGREDIENTS ===
        $ingredients = [
            // Proteins
            ['name' => 'Beef Patty (6oz)', 'unit' => 'each', 'cost_cents' => 32000], // JMD 320 each
            ['name' => 'Chicken Breast', 'unit' => 'lb', 'cost_cents' => 45000], // JMD 450/lb
            ['name' => 'Ground Beef', 'unit' => 'lb', 'cost_cents' => 42000],
            ['name' => 'Pork Chop', 'unit' => 'lb', 'cost_cents' => 48000],
            ['name' => 'Shrimp', 'unit' => 'lb', 'cost_cents' => 120000],
            ['name' => 'Salmon Fillet', 'unit' => 'lb', 'cost_cents' => 150000],
            ['name' => 'Bacon Strips', 'unit' => 'lb', 'cost_cents' => 55000],
            ['name' => 'Sausage Links', 'unit' => 'each', 'cost_cents' => 8000],

            // Bread & Buns
            ['name' => 'Burger Bun', 'unit' => 'each', 'cost_cents' => 5000],
            ['name' => 'Hot Dog Bun', 'unit' => 'each', 'cost_cents' => 4500],
            ['name' => 'Dinner Roll', 'unit' => 'each', 'cost_cents' => 3500],
            ['name' => 'Garlic Bread', 'unit' => 'each', 'cost_cents' => 6000],

            // Vegetables
            ['name' => 'Lettuce', 'unit' => 'head', 'cost_cents' => 15000],
            ['name' => 'Tomato', 'unit' => 'lb', 'cost_cents' => 12000],
            ['name' => 'Onion', 'unit' => 'lb', 'cost_cents' => 10000],
            ['name' => 'Bell Pepper', 'unit' => 'lb', 'cost_cents' => 18000],
            ['name' => 'Mushrooms', 'unit' => 'lb', 'cost_cents' => 22000],
            ['name' => 'Cucumber', 'unit' => 'each', 'cost_cents' => 8000],
            ['name' => 'Carrot', 'unit' => 'lb', 'cost_cents' => 9000],
            ['name' => 'Potato', 'unit' => 'lb', 'cost_cents' => 8000],
            ['name' => 'Sweet Potato', 'unit' => 'lb', 'cost_cents' => 10000],
            ['name' => 'Corn', 'unit' => 'each', 'cost_cents' => 6000],
            ['name' => 'Broccoli', 'unit' => 'lb', 'cost_cents' => 16000],

            // Cheese & Dairy
            ['name' => 'Cheddar Cheese', 'unit' => 'lb', 'cost_cents' => 38000],
            ['name' => 'Mozzarella Cheese', 'unit' => 'lb', 'cost_cents' => 36000],
            ['name' => 'Butter', 'unit' => 'lb', 'cost_cents' => 28000],
            ['name' => 'Sour Cream', 'unit' => 'lb', 'cost_cents' => 18000],

            // Sauces & Condiments
            ['name' => 'BBQ Sauce', 'unit' => 'oz', 'cost_cents' => 50],
            ['name' => 'Ketchup', 'unit' => 'oz', 'cost_cents' => 30],
            ['name' => 'Mustard', 'unit' => 'oz', 'cost_cents' => 30],
            ['name' => 'Mayonnaise', 'unit' => 'oz', 'cost_cents' => 40],
            ['name' => 'Hot Sauce', 'unit' => 'oz', 'cost_cents' => 60],
            ['name' => 'Ranch Dressing', 'unit' => 'oz', 'cost_cents' => 45],
            ['name' => 'Teriyaki Sauce', 'unit' => 'oz', 'cost_cents' => 50],

            // Oils & Seasonings
            ['name' => 'Vegetable Oil', 'unit' => 'oz', 'cost_cents' => 20],
            ['name' => 'Olive Oil', 'unit' => 'oz', 'cost_cents' => 60],
            ['name' => 'Salt', 'unit' => 'oz', 'cost_cents' => 5],
            ['name' => 'Black Pepper', 'unit' => 'oz', 'cost_cents' => 80],
            ['name' => 'Garlic Powder', 'unit' => 'oz', 'cost_cents' => 70],
            ['name' => 'Cajun Seasoning', 'unit' => 'oz', 'cost_cents' => 100],
            ['name' => 'Jerk Seasoning', 'unit' => 'oz', 'cost_cents' => 120],

            // Other
            ['name' => 'Eggs', 'unit' => 'each', 'cost_cents' => 2500],
            ['name' => 'Pasta', 'unit' => 'lb', 'cost_cents' => 12000],
            ['name' => 'Rice', 'unit' => 'lb', 'cost_cents' => 8000],
            ['name' => 'Flour', 'unit' => 'lb', 'cost_cents' => 6000],
            ['name' => 'Breadcrumbs', 'unit' => 'lb', 'cost_cents' => 10000],
        ];

        $ingredientIds = [];
        foreach ($ingredients as $ing) {
            $id = DB::table('products')->insertGetId([
                'name' => $ing['name'],
                'type' => 'ingredient',
                'category_id' => null,
                'price_cents' => 0, // Ingredients don't have sale price
                'cost_cents' => $ing['cost_cents'],
                'unit' => $ing['unit'],
                'low_stock_threshold' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $ingredientIds[$ing['name']] = $id;

            // Create initial inventory lot
            DB::table('inventory_lots')->insert([
                'product_id' => $id,
                'location_id' => 1,
                'lot_code' => 'INIT-' . $id,
                'qty_on_hand' => 100,
                'unit_cost_cents' => $ing['cost_cents'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // === BEVERAGES (Retail Products) ===
        $beverages = [
            ['name' => 'Coca-Cola', 'price' => 15000, 'cost' => 8000],
            ['name' => 'Sprite', 'price' => 15000, 'cost' => 8000],
            ['name' => 'Fanta Orange', 'price' => 15000, 'cost' => 8000],
            ['name' => 'Ginger Beer', 'price' => 18000, 'cost' => 9000],
            ['name' => 'Ting', 'price' => 18000, 'cost' => 9000],
            ['name' => 'Bottled Water', 'price' => 10000, 'cost' => 5000],
            ['name' => 'Iced Tea', 'price' => 16000, 'cost' => 7000],
            ['name' => 'Lemonade', 'price' => 16000, 'cost' => 6000],
            ['name' => 'Fresh Squeezed Orange Juice', 'price' => 25000, 'cost' => 10000],
            ['name' => 'Coffee', 'price' => 18000, 'cost' => 5000],
        ];

        foreach ($beverages as $bev) {
            $id = DB::table('products')->insertGetId([
                'name' => $bev['name'],
                'type' => 'product',
                'category_id' => $categoryIds['Beverages'],
                'price_cents' => $bev['price'],
                'cost_cents' => $bev['cost'],
                'unit' => 'each',
                'low_stock_threshold' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create inventory lot for beverages
            DB::table('inventory_lots')->insert([
                'product_id' => $id,
                'location_id' => 1,
                'lot_code' => 'BEV-' . $id,
                'qty_on_hand' => 50,
                'unit_cost_cents' => $bev['cost'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // === DISHES (Menu Items) ===
        $dishes = [
            // Burgers
            [
                'name' => 'Classic Grillstone Burger',
                'category' => 'Burgers & Sandwiches',
                'description' => 'Our signature 6oz beef patty with lettuce, tomato, onion, and special sauce',
                'variants' => [
                    ['name' => 'Regular', 'price' => 85000], // JMD 850
                ],
                'recipe' => [
                    ['ingredient' => 'Beef Patty (6oz)', 'quantity' => 1],
                    ['ingredient' => 'Burger Bun', 'quantity' => 1],
                    ['ingredient' => 'Lettuce', 'quantity' => 0.05], // 1/20th of a head
                    ['ingredient' => 'Tomato', 'quantity' => 0.1], // 0.1 lb
                    ['ingredient' => 'Onion', 'quantity' => 0.05],
                    ['ingredient' => 'Cheddar Cheese', 'quantity' => 0.1],
                    ['ingredient' => 'Mayonnaise', 'quantity' => 1],
                    ['ingredient' => 'Ketchup', 'quantity' => 0.5],
                ]
            ],
            [
                'name' => 'BBQ Bacon Burger',
                'category' => 'Burgers & Sandwiches',
                'description' => 'Beef patty topped with crispy bacon, cheddar cheese, and BBQ sauce',
                'variants' => [
                    ['name' => 'Regular', 'price' => 95000],
                ],
                'recipe' => [
                    ['ingredient' => 'Beef Patty (6oz)', 'quantity' => 1],
                    ['ingredient' => 'Burger Bun', 'quantity' => 1],
                    ['ingredient' => 'Bacon Strips', 'quantity' => 0.15], // ~2 strips
                    ['ingredient' => 'Cheddar Cheese', 'quantity' => 0.1],
                    ['ingredient' => 'Onion', 'quantity' => 0.05],
                    ['ingredient' => 'BBQ Sauce', 'quantity' => 2],
                    ['ingredient' => 'Lettuce', 'quantity' => 0.05],
                ]
            ],
            [
                'name' => 'Mushroom Swiss Burger',
                'category' => 'Burgers & Sandwiches',
                'description' => 'Beef patty with sautéed mushrooms and Swiss cheese',
                'variants' => [
                    ['name' => 'Regular', 'price' => 92000],
                ],
                'recipe' => [
                    ['ingredient' => 'Beef Patty (6oz)', 'quantity' => 1],
                    ['ingredient' => 'Burger Bun', 'quantity' => 1],
                    ['ingredient' => 'Mushrooms', 'quantity' => 0.15],
                    ['ingredient' => 'Mozzarella Cheese', 'quantity' => 0.1],
                    ['ingredient' => 'Onion', 'quantity' => 0.05],
                    ['ingredient' => 'Butter', 'quantity' => 0.05],
                ]
            ],

            // Grilled Chicken
            [
                'name' => 'Grilled Jerk Chicken',
                'category' => 'Grilled Specialties',
                'description' => 'Marinated chicken breast with authentic Jamaican jerk seasoning',
                'variants' => [
                    ['name' => 'Half Chicken', 'price' => 110000],
                    ['name' => 'Quarter Chicken', 'price' => 65000],
                ],
                'recipe' => [
                    ['ingredient' => 'Chicken Breast', 'quantity' => 0.75], // 0.75 lb
                    ['ingredient' => 'Jerk Seasoning', 'quantity' => 1],
                    ['ingredient' => 'Vegetable Oil', 'quantity' => 1],
                    ['ingredient' => 'Onion', 'quantity' => 0.1],
                ]
            ],
            [
                'name' => 'BBQ Grilled Chicken',
                'category' => 'Grilled Specialties',
                'description' => 'Tender grilled chicken with our signature BBQ glaze',
                'variants' => [
                    ['name' => 'Half Chicken', 'price' => 105000],
                    ['name' => 'Quarter Chicken', 'price' => 60000],
                ],
                'recipe' => [
                    ['ingredient' => 'Chicken Breast', 'quantity' => 0.75],
                    ['ingredient' => 'BBQ Sauce', 'quantity' => 3],
                    ['ingredient' => 'Garlic Powder', 'quantity' => 0.5],
                    ['ingredient' => 'Black Pepper', 'quantity' => 0.2],
                ]
            ],

            // Steaks & Chops
            [
                'name' => 'Grilled Pork Chop',
                'category' => 'Main Courses',
                'description' => 'Seasoned pork chop grilled to perfection',
                'variants' => [
                    ['name' => '12oz', 'price' => 125000],
                    ['name' => '8oz', 'price' => 90000],
                ],
                'recipe' => [
                    ['ingredient' => 'Pork Chop', 'quantity' => 0.75],
                    ['ingredient' => 'Salt', 'quantity' => 0.5],
                    ['ingredient' => 'Black Pepper', 'quantity' => 0.3],
                    ['ingredient' => 'Garlic Powder', 'quantity' => 0.5],
                    ['ingredient' => 'Olive Oil', 'quantity' => 1],
                ]
            ],
            [
                'name' => 'Grilled Shrimp Skewers',
                'category' => 'Grilled Specialties',
                'description' => 'Succulent shrimp marinated and grilled',
                'variants' => [
                    ['name' => 'Regular (8 pieces)', 'price' => 145000],
                ],
                'recipe' => [
                    ['ingredient' => 'Shrimp', 'quantity' => 0.5],
                    ['ingredient' => 'Garlic Powder', 'quantity' => 0.5],
                    ['ingredient' => 'Olive Oil', 'quantity' => 1],
                    ['ingredient' => 'Cajun Seasoning', 'quantity' => 1],
                    ['ingredient' => 'Bell Pepper', 'quantity' => 0.1],
                ]
            ],

            // Sides
            [
                'name' => 'French Fries',
                'category' => 'Sides',
                'description' => 'Crispy golden fries',
                'variants' => [
                    ['name' => 'Regular', 'price' => 30000],
                    ['name' => 'Large', 'price' => 45000],
                ],
                'recipe' => [
                    ['ingredient' => 'Potato', 'quantity' => 0.5],
                    ['ingredient' => 'Vegetable Oil', 'quantity' => 2],
                    ['ingredient' => 'Salt', 'quantity' => 0.3],
                ]
            ],
            [
                'name' => 'Sweet Potato Fries',
                'category' => 'Sides',
                'description' => 'Sweet and crispy fries',
                'variants' => [
                    ['name' => 'Regular', 'price' => 35000],
                ],
                'recipe' => [
                    ['ingredient' => 'Sweet Potato', 'quantity' => 0.5],
                    ['ingredient' => 'Vegetable Oil', 'quantity' => 2],
                    ['ingredient' => 'Salt', 'quantity' => 0.3],
                ]
            ],
            [
                'name' => 'Onion Rings',
                'category' => 'Sides',
                'description' => 'Crispy battered onion rings',
                'variants' => [
                    ['name' => 'Regular', 'price' => 35000],
                ],
                'recipe' => [
                    ['ingredient' => 'Onion', 'quantity' => 0.3],
                    ['ingredient' => 'Flour', 'quantity' => 0.2],
                    ['ingredient' => 'Breadcrumbs', 'quantity' => 0.1],
                    ['ingredient' => 'Eggs', 'quantity' => 1],
                    ['ingredient' => 'Vegetable Oil', 'quantity' => 3],
                ]
            ],
            [
                'name' => 'Grilled Corn',
                'category' => 'Sides',
                'description' => 'Fresh corn grilled with butter',
                'variants' => [
                    ['name' => 'Regular', 'price' => 25000],
                ],
                'recipe' => [
                    ['ingredient' => 'Corn', 'quantity' => 1],
                    ['ingredient' => 'Butter', 'quantity' => 0.05],
                    ['ingredient' => 'Salt', 'quantity' => 0.2],
                ]
            ],
            [
                'name' => 'Coleslaw',
                'category' => 'Sides',
                'description' => 'Fresh cabbage salad',
                'variants' => [
                    ['name' => 'Regular', 'price' => 25000],
                ],
                'recipe' => [
                    ['ingredient' => 'Lettuce', 'quantity' => 0.15],
                    ['ingredient' => 'Carrot', 'quantity' => 0.1],
                    ['ingredient' => 'Mayonnaise', 'quantity' => 1.5],
                    ['ingredient' => 'Salt', 'quantity' => 0.1],
                ]
            ],

            // Salads
            [
                'name' => 'Garden Salad',
                'category' => 'Salads',
                'description' => 'Fresh mixed greens with vegetables',
                'variants' => [
                    ['name' => 'Regular', 'price' => 45000],
                ],
                'recipe' => [
                    ['ingredient' => 'Lettuce', 'quantity' => 0.2],
                    ['ingredient' => 'Tomato', 'quantity' => 0.15],
                    ['ingredient' => 'Cucumber', 'quantity' => 0.5],
                    ['ingredient' => 'Onion', 'quantity' => 0.05],
                    ['ingredient' => 'Carrot', 'quantity' => 0.1],
                    ['ingredient' => 'Ranch Dressing', 'quantity' => 2],
                ]
            ],
            [
                'name' => 'Grilled Chicken Salad',
                'category' => 'Salads',
                'description' => 'Garden salad topped with grilled chicken',
                'variants' => [
                    ['name' => 'Regular', 'price' => 85000],
                ],
                'recipe' => [
                    ['ingredient' => 'Lettuce', 'quantity' => 0.2],
                    ['ingredient' => 'Chicken Breast', 'quantity' => 0.3],
                    ['ingredient' => 'Tomato', 'quantity' => 0.1],
                    ['ingredient' => 'Cucumber', 'quantity' => 0.5],
                    ['ingredient' => 'Cheddar Cheese', 'quantity' => 0.1],
                    ['ingredient' => 'Ranch Dressing', 'quantity' => 2],
                ]
            ],

            // Kids Menu
            [
                'name' => 'Kids Chicken Tenders',
                'category' => 'Kids Menu',
                'description' => 'Crispy chicken tenders with fries',
                'variants' => [
                    ['name' => 'Regular', 'price' => 55000],
                ],
                'recipe' => [
                    ['ingredient' => 'Chicken Breast', 'quantity' => 0.25],
                    ['ingredient' => 'Breadcrumbs', 'quantity' => 0.1],
                    ['ingredient' => 'Flour', 'quantity' => 0.1],
                    ['ingredient' => 'Eggs', 'quantity' => 1],
                    ['ingredient' => 'Vegetable Oil', 'quantity' => 2],
                ]
            ],
            [
                'name' => 'Kids Mini Burger',
                'category' => 'Kids Menu',
                'description' => 'Small burger with cheese',
                'variants' => [
                    ['name' => 'Regular', 'price' => 50000],
                ],
                'recipe' => [
                    ['ingredient' => 'Ground Beef', 'quantity' => 0.2], // Smaller patty
                    ['ingredient' => 'Burger Bun', 'quantity' => 1],
                    ['ingredient' => 'Cheddar Cheese', 'quantity' => 0.05],
                    ['ingredient' => 'Ketchup', 'quantity' => 0.5],
                ]
            ],
        ];

        foreach ($dishes as $dishData) {
            // Create dish
            $dishId = DB::table('products')->insertGetId([
                'name' => $dishData['name'],
                'type' => 'dish',
                'category_id' => $categoryIds[$dishData['category']],
                'price_cents' => $dishData['variants'][0]['price'], // Use first variant price as base
                'description' => $dishData['description'] ?? null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create variants
            foreach ($dishData['variants'] as $variantData) {
                $variantId = DB::table('recipe_variants')->insertGetId([
                    'product_id' => $dishId,
                    'name' => $variantData['name'],
                    'price_cents' => $variantData['price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create recipe components
                foreach ($dishData['recipe'] as $component) {
                    if (isset($ingredientIds[$component['ingredient']])) {
                        DB::table('recipe_components')->insert([
                            'variant_id' => $variantId,
                            'ingredient_product_id' => $ingredientIds[$component['ingredient']],
                            'qty_per_unit' => $component['quantity'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        $this->command->info('✅ Restaurant data seeded successfully!');
        $this->command->info('📊 Summary:');
        $this->command->info('   - Categories: ' . count($categories));
        $this->command->info('   - Ingredients: ' . count($ingredients));
        $this->command->info('   - Beverages: ' . count($beverages));
        $this->command->info('   - Dishes: ' . count($dishes));
    }
}
