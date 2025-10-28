<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JamaicanRestaurantSeeder extends Seeder
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
        DB::table('vendors')->delete();

        // Ensure default location exists
        if (!DB::table('locations')->where('id', 1)->exists()) {
            DB::table('locations')->insert([
                'id' => 1,
                'name' => 'Main Location',
                'type' => 'store',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // === JAMAICAN CATEGORIES ===
        $categories = [
            ['name' => 'Breakfast', 'is_active' => true],
            ['name' => 'Lunch', 'is_active' => true],
            ['name' => 'Dinner', 'is_active' => true],
            ['name' => 'Combos', 'is_active' => true],
            ['name' => 'Sides', 'is_active' => true],
            ['name' => 'Beverages', 'is_active' => true],
            ['name' => 'Done to Order', 'is_active' => true],
            ['name' => 'Soups', 'is_active' => true],
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

        // === JAMAICAN INGREDIENTS ===
        $ingredients = [
            // Proteins
            ['name' => 'Saltfish (Salted Cod)', 'unit' => 'lb', 'cost_cents' => 80000], // JMD 800/lb
            ['name' => 'Ackee (canned)', 'unit' => 'can', 'cost_cents' => 35000], // JMD 350/can
            ['name' => 'Chicken (whole)', 'unit' => 'lb', 'cost_cents' => 45000],
            ['name' => 'Chicken Thigh', 'unit' => 'lb', 'cost_cents' => 48000],
            ['name' => 'Chicken Breast', 'unit' => 'lb', 'cost_cents' => 52000],
            ['name' => 'Oxtail', 'unit' => 'lb', 'cost_cents' => 150000], // JMD 1,500/lb
            ['name' => 'Beef', 'unit' => 'lb', 'cost_cents' => 65000],
            ['name' => 'Goat Meat', 'unit' => 'lb', 'cost_cents' => 70000],
            ['name' => 'Pork', 'unit' => 'lb', 'cost_cents' => 55000],
            ['name' => 'Red Snapper (whole)', 'unit' => 'lb', 'cost_cents' => 120000],
            ['name' => 'King Fish', 'unit' => 'lb', 'cost_cents' => 100000],
            ['name' => 'Mackerel (canned)', 'unit' => 'can', 'cost_cents' => 18000],
            ['name' => 'Callaloo (canned)', 'unit' => 'can', 'cost_cents' => 20000],

            // Vegetables & Ground Provisions
            ['name' => 'Green Banana', 'unit' => 'lb', 'cost_cents' => 8000],
            ['name' => 'Yellow Yam', 'unit' => 'lb', 'cost_cents' => 12000],
            ['name' => 'Sweet Potato', 'unit' => 'lb', 'cost_cents' => 10000],
            ['name' => 'Dasheen', 'unit' => 'lb', 'cost_cents' => 11000],
            ['name' => 'Breadfruit', 'unit' => 'each', 'cost_cents' => 15000],
            ['name' => 'Plantain', 'unit' => 'each', 'cost_cents' => 8000],
            ['name' => 'Cassava', 'unit' => 'lb', 'cost_cents' => 9000],
            ['name' => 'Irish Potato', 'unit' => 'lb', 'cost_cents' => 10000],
            ['name' => 'Pumpkin', 'unit' => 'lb', 'cost_cents' => 7000],
            ['name' => 'Cho Cho (Chayote)', 'unit' => 'lb', 'cost_cents' => 8000],
            ['name' => 'Cabbage', 'unit' => 'lb', 'cost_cents' => 9000],
            ['name' => 'Carrot', 'unit' => 'lb', 'cost_cents' => 10000],
            ['name' => 'Tomato', 'unit' => 'lb', 'cost_cents' => 12000],
            ['name' => 'Onion', 'unit' => 'lb', 'cost_cents' => 10000],
            ['name' => 'Scallion', 'unit' => 'bunch', 'cost_cents' => 5000],
            ['name' => 'Thyme', 'unit' => 'bunch', 'cost_cents' => 3000],
            ['name' => 'Scotch Bonnet Pepper', 'unit' => 'each', 'cost_cents' => 2000],

            // Rice & Peas Ingredients
            ['name' => 'Rice (long grain)', 'unit' => 'lb', 'cost_cents' => 8000],
            ['name' => 'Red Kidney Beans (dried)', 'unit' => 'lb', 'cost_cents' => 15000],
            ['name' => 'Gungo Peas (dried)', 'unit' => 'lb', 'cost_cents' => 18000],
            ['name' => 'Coconut Milk', 'unit' => 'can', 'cost_cents' => 12000],

            // Seasonings & Spices
            ['name' => 'Jerk Seasoning', 'unit' => 'oz', 'cost_cents' => 120],
            ['name' => 'All-Purpose Seasoning', 'unit' => 'oz', 'cost_cents' => 80],
            ['name' => 'Black Pepper', 'unit' => 'oz', 'cost_cents' => 100],
            ['name' => 'Salt', 'unit' => 'oz', 'cost_cents' => 5],
            ['name' => 'Garlic', 'unit' => 'bulb', 'cost_cents' => 3000],
            ['name' => 'Ginger', 'unit' => 'oz', 'cost_cents' => 150],
            ['name' => 'Curry Powder', 'unit' => 'oz', 'cost_cents' => 100],
            ['name' => 'Pimento (Allspice)', 'unit' => 'oz', 'cost_cents' => 120],

            // Cooking Essentials
            ['name' => 'Vegetable Oil', 'unit' => 'oz', 'cost_cents' => 25],
            ['name' => 'Flour', 'unit' => 'lb', 'cost_cents' => 7000],
            ['name' => 'Cornmeal', 'unit' => 'lb', 'cost_cents' => 8000],
            ['name' => 'Butter', 'unit' => 'lb', 'cost_cents' => 30000],
            ['name' => 'Margarine', 'unit' => 'lb', 'cost_cents' => 20000],

            // Breakfast Specific
            ['name' => 'Liver', 'unit' => 'lb', 'cost_cents' => 35000],
            ['name' => 'Kidney', 'unit' => 'lb', 'cost_cents' => 32000],
            ['name' => 'Tripe', 'unit' => 'lb', 'cost_cents' => 30000],
            ['name' => 'Bammy', 'unit' => 'each', 'cost_cents' => 8000],
            ['name' => 'Festival Dough', 'unit' => 'lb', 'cost_cents' => 10000],
            ['name' => 'Johnny Cake Dough', 'unit' => 'lb', 'cost_cents' => 10000],
            ['name' => 'Dumpling Flour', 'unit' => 'lb', 'cost_cents' => 8000],

            // Soup Ingredients
            ['name' => 'Soup Bones', 'unit' => 'lb', 'cost_cents' => 25000],
            ['name' => 'Beef Feet', 'unit' => 'lb', 'cost_cents' => 28000],
            ['name' => 'Chicken Backs/Necks', 'unit' => 'lb', 'cost_cents' => 20000],
        ];

        $ingredientIds = [];
        foreach ($ingredients as $ing) {
            $id = DB::table('products')->insertGetId([
                'name' => $ing['name'],
                'type' => 'ingredient',
                'category_id' => null,
                'price_cents' => 0,
                'cost_cents' => $ing['cost_cents'],
                'unit' => $ing['unit'],
                'low_stock_threshold' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $ingredientIds[$ing['name']] = $id;

            // Create initial inventory lot with substantial stock
            DB::table('inventory_lots')->insert([
                'product_id' => $id,
                'location_id' => 1,
                'lot_code' => 'INIT-' . $id,
                'qty_on_hand' => 500, // Increased from 100 to ensure well-stocked
                'unit_cost_cents' => $ing['cost_cents'],
                'received_at' => now()->subDays(7), // Received a week ago
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // === JAMAICAN BEVERAGES ===
        $beverages = [
            ['name' => 'Ting', 'price' => 18000, 'cost' => 9000, 'image' => 'https://images.unsplash.com/photo-1625772452859-1c03d5bf1137?w=400'],
            ['name' => 'Ginger Beer', 'price' => 18000, 'cost' => 9000, 'image' => 'https://images.unsplash.com/photo-1594971475674-6a97f8fe8c2e?w=400'],
            ['name' => 'Sorrel Drink', 'price' => 20000, 'cost' => 8000, 'image' => 'https://images.unsplash.com/photo-1546548970-71785318a17b?w=400'],
            ['name' => 'Irish Moss', 'price' => 25000, 'cost' => 10000, 'image' => 'https://images.unsplash.com/photo-1563227812-0ea4c22e6cc8?w=400'],
            ['name' => 'Sky Juice', 'price' => 15000, 'cost' => 5000, 'image' => 'https://images.unsplash.com/photo-1594971475674-6a97f8fe8c2e?w=400'],
            ['name' => 'Coconut Water (fresh)', 'price' => 20000, 'cost' => 8000, 'image' => 'https://images.unsplash.com/photo-1585238341710-8fc8facaa05d?w=400'],
            ['name' => 'Fruit Punch', 'price' => 18000, 'cost' => 7000, 'image' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=400'],
            ['name' => 'Carrot Juice', 'price' => 22000, 'cost' => 9000, 'image' => 'https://images.unsplash.com/photo-1623065422902-30a2d299bbe4?w=400'],
            ['name' => 'Bottled Water', 'price' => 10000, 'cost' => 5000, 'image' => 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?w=400'],
            ['name' => 'Red Stripe Beer', 'price' => 30000, 'cost' => 15000, 'image' => 'https://images.unsplash.com/photo-1608270586620-248524c67de9?w=400'],
        ];

        foreach ($beverages as $bev) {
            $id = DB::table('products')->insertGetId([
                'name' => $bev['name'],
                'type' => 'product',
                'category_id' => $categoryIds['Beverages'],
                'price_cents' => $bev['price'],
                'cost_cents' => $bev['cost'],
                'unit' => 'each',
                'image_url' => $bev['image'],
                'low_stock_threshold' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('inventory_lots')->insert([
                'product_id' => $id,
                'location_id' => 1,
                'lot_code' => 'BEV-' . $id,
                'qty_on_hand' => 200, // Increased from 50 to ensure well-stocked
                'unit_cost_cents' => $bev['cost'],
                'received_at' => now()->subDays(7), // Received a week ago
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // === JAMAICAN DISHES ===
        $dishes = [
            // BREAKFAST
            [
                'name' => 'Ackee & Saltfish',
                'category' => 'Breakfast',
                'description' => 'Jamaica\'s national dish - ackee sautéed with saltfish, onions, tomatoes, and peppers',
                'image' => 'https://images.unsplash.com/photo-1604152135912-04a022e23696?w=400',
                'variants' => [
                    ['name' => 'Regular', 'price' => 95000], // JMD 950
                ],
                'recipe' => [
                    ['ingredient' => 'Ackee (canned)', 'quantity' => 0.5],
                    ['ingredient' => 'Saltfish (Salted Cod)', 'quantity' => 0.3],
                    ['ingredient' => 'Onion', 'quantity' => 0.1],
                    ['ingredient' => 'Tomato', 'quantity' => 0.15],
                    ['ingredient' => 'Scotch Bonnet Pepper', 'quantity' => 0.5],
                    ['ingredient' => 'Scallion', 'quantity' => 0.2],
                    ['ingredient' => 'Thyme', 'quantity' => 0.1],
                    ['ingredient' => 'Vegetable Oil', 'quantity' => 2],
                ]
            ],
            [
                'name' => 'Callaloo & Saltfish',
                'category' => 'Breakfast',
                'description' => 'Steamed callaloo greens with flaked saltfish',
                'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400',
                'variants' => [
                    ['name' => 'Regular', 'price' => 85000],
                ],
                'recipe' => [
                    ['ingredient' => 'Callaloo (canned)', 'quantity' => 1],
                    ['ingredient' => 'Saltfish (Salted Cod)', 'quantity' => 0.25],
                    ['ingredient' => 'Onion', 'quantity' => 0.1],
                    ['ingredient' => 'Tomato', 'quantity' => 0.1],
                    ['ingredient' => 'Scallion', 'quantity' => 0.2],
                    ['ingredient' => 'Scotch Bonnet Pepper', 'quantity' => 0.3],
                ]
            ],
            [
                'name' => 'Mackerel Rundown',
                'category' => 'Breakfast',
                'description' => 'Mackerel cooked in coconut milk with vegetables',
                'image' => 'https://images.unsplash.com/photo-1559847844-5315695dadae?w=400',
                'variants' => [
                    ['name' => 'Regular', 'price' => 75000],
                ],
                'recipe' => [
                    ['ingredient' => 'Mackerel (canned)', 'quantity' => 1],
                    ['ingredient' => 'Coconut Milk', 'quantity' => 0.5],
                    ['ingredient' => 'Onion', 'quantity' => 0.1],
                    ['ingredient' => 'Tomato', 'quantity' => 0.1],
                    ['ingredient' => 'Scallion', 'quantity' => 0.2],
                    ['ingredient' => 'Thyme', 'quantity' => 0.1],
                ]
            ],
            [
                'name' => 'Liver & Banana',
                'category' => 'Breakfast',
                'description' => 'Seasoned liver with boiled green banana',
                'image' => 'https://images.unsplash.com/photo-1604152135912-04a022e23696?w=400',
                'variants' => [
                    ['name' => 'Regular', 'price' => 80000],
                ],
                'recipe' => [
                    ['ingredient' => 'Liver', 'quantity' => 0.4],
                    ['ingredient' => 'Green Banana', 'quantity' => 0.5],
                    ['ingredient' => 'Onion', 'quantity' => 0.1],
                    ['ingredient' => 'All-Purpose Seasoning', 'quantity' => 1],
                    ['ingredient' => 'Vegetable Oil', 'quantity' => 2],
                ]
            ],

            // LUNCH/DINNER MAINS
            [
                'name' => 'Jerk Chicken',
                'category' => 'Lunch',
                'description' => 'Authentic Jamaican jerk chicken - spicy and smoky',
                'image' => 'https://images.unsplash.com/photo-1598103442097-8b74394b95c6?w=400',
                'variants' => [
                    ['name' => 'Quarter', 'price' => 75000],
                    ['name' => 'Half', 'price' => 135000],
                    ['name' => 'Whole', 'price' => 250000],
                ],
                'recipe' => [
                    ['ingredient' => 'Chicken (whole)', 'quantity' => 1],
                    ['ingredient' => 'Jerk Seasoning', 'quantity' => 3],
                    ['ingredient' => 'Scallion', 'quantity' => 0.3],
                    ['ingredient' => 'Thyme', 'quantity' => 0.2],
                    ['ingredient' => 'Scotch Bonnet Pepper', 'quantity' => 1],
                    ['ingredient' => 'Pimento (Allspice)', 'quantity' => 1],
                ]
            ],
            [
                'name' => 'Brown Stew Chicken',
                'category' => 'Lunch',
                'description' => 'Chicken pieces in rich brown gravy',
                'image' => 'https://images.unsplash.com/photo-1598103442097-8b74394b95c6?w=400',
                'variants' => [
                    ['name' => 'Regular', 'price' => 95000],
                ],
                'recipe' => [
                    ['ingredient' => 'Chicken Thigh', 'quantity' => 0.75],
                    ['ingredient' => 'Onion', 'quantity' => 0.15],
                    ['ingredient' => 'Tomato', 'quantity' => 0.15],
                    ['ingredient' => 'Scallion', 'quantity' => 0.2],
                    ['ingredient' => 'Thyme', 'quantity' => 0.15],
                    ['ingredient' => 'All-Purpose Seasoning', 'quantity' => 1.5],
                    ['ingredient' => 'Vegetable Oil', 'quantity' => 2],
                ]
            ],
            [
                'name' => 'Curry Chicken',
                'category' => 'Lunch',
                'description' => 'Tender chicken in Jamaican-style curry',
                'image' => 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?w=400',
                'variants' => [
                    ['name' => 'Regular', 'price' => 95000],
                ],
                'recipe' => [
                    ['ingredient' => 'Chicken Thigh', 'quantity' => 0.75],
                    ['ingredient' => 'Curry Powder', 'quantity' => 2],
                    ['ingredient' => 'Irish Potato', 'quantity' => 0.3],
                    ['ingredient' => 'Onion', 'quantity' => 0.15],
                    ['ingredient' => 'Scallion', 'quantity' => 0.2],
                    ['ingredient' => 'Thyme', 'quantity' => 0.1],
                    ['ingredient' => 'Scotch Bonnet Pepper', 'quantity' => 0.5],
                ]
            ],
            [
                'name' => 'Oxtail',
                'category' => 'Dinner',
                'description' => 'Fall-off-the-bone oxtail in rich gravy with butter beans',
                'image' => 'https://images.unsplash.com/photo-1606850752573-176dc964b0aa?w=400',
                'variants' => [
                    ['name' => 'Regular', 'price' => 185000], // Premium price
                ],
                'recipe' => [
                    ['ingredient' => 'Oxtail', 'quantity' => 0.75],
                    ['ingredient' => 'Red Kidney Beans (dried)', 'quantity' => 0.2],
                    ['ingredient' => 'Onion', 'quantity' => 0.15],
                    ['ingredient' => 'Scallion', 'quantity' => 0.2],
                    ['ingredient' => 'Thyme', 'quantity' => 0.15],
                    ['ingredient' => 'All-Purpose Seasoning', 'quantity' => 2],
                    ['ingredient' => 'Garlic', 'quantity' => 0.3],
                ]
            ],
            [
                'name' => 'Curry Goat',
                'category' => 'Dinner',
                'description' => 'Tender curry goat - a Jamaican favorite',
                'image' => 'https://images.unsplash.com/photo-1585937421612-70e008ce19ed?w=400',
                'variants' => [
                    ['name' => 'Regular', 'price' => 125000],
                ],
                'recipe' => [
                    ['ingredient' => 'Goat Meat', 'quantity' => 0.75],
                    ['ingredient' => 'Curry Powder', 'quantity' => 2.5],
                    ['ingredient' => 'Irish Potato', 'quantity' => 0.25],
                    ['ingredient' => 'Onion', 'quantity' => 0.15],
                    ['ingredient' => 'Scallion', 'quantity' => 0.25],
                    ['ingredient' => 'Thyme', 'quantity' => 0.15],
                    ['ingredient' => 'Scotch Bonnet Pepper', 'quantity' => 1],
                ]
            ],
            [
                'name' => 'Escovitch Fish',
                'category' => 'Done to Order',
                'description' => 'Fried fish topped with spicy pickled vegetables',
                'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=400',
                'variants' => [
                    ['name' => 'Snapper', 'price' => 145000],
                    ['name' => 'King Fish', 'price' => 125000],
                ],
                'recipe' => [
                    ['ingredient' => 'Red Snapper (whole)', 'quantity' => 0.75],
                    ['ingredient' => 'Carrot', 'quantity' => 0.15],
                    ['ingredient' => 'Onion', 'quantity' => 0.15],
                    ['ingredient' => 'Scotch Bonnet Pepper', 'quantity' => 1],
                    ['ingredient' => 'Flour', 'quantity' => 0.15],
                    ['ingredient' => 'Vegetable Oil', 'quantity' => 5],
                ]
            ],
            [
                'name' => 'Fried Chicken',
                'category' => 'Lunch',
                'description' => 'Crispy Jamaican-style fried chicken',
                'image' => 'https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?w=400',
                'variants' => [
                    ['name' => '2 Pieces', 'price' => 75000],
                    ['name' => '4 Pieces', 'price' => 135000],
                ],
                'recipe' => [
                    ['ingredient' => 'Chicken Thigh', 'quantity' => 0.5],
                    ['ingredient' => 'Flour', 'quantity' => 0.2],
                    ['ingredient' => 'All-Purpose Seasoning', 'quantity' => 1.5],
                    ['ingredient' => 'Black Pepper', 'quantity' => 0.5],
                    ['ingredient' => 'Vegetable Oil', 'quantity' => 4],
                ]
            ],

            // SIDES
            [
                'name' => 'Rice & Peas',
                'category' => 'Sides',
                'description' => 'Coconut rice with kidney beans',
                'image' => 'https://images.unsplash.com/photo-1516684732162-798a0062be99?w=400',
                'variants' => [
                    ['name' => 'Regular', 'price' => 35000],
                    ['name' => 'Large', 'price' => 55000],
                ],
                'recipe' => [
                    ['ingredient' => 'Rice (long grain)', 'quantity' => 0.5],
                    ['ingredient' => 'Red Kidney Beans (dried)', 'quantity' => 0.15],
                    ['ingredient' => 'Coconut Milk', 'quantity' => 0.5],
                    ['ingredient' => 'Scallion', 'quantity' => 0.1],
                    ['ingredient' => 'Thyme', 'quantity' => 0.1],
                    ['ingredient' => 'Scotch Bonnet Pepper', 'quantity' => 0.3],
                ]
            ],
            [
                'name' => 'White Rice',
                'category' => 'Sides',
                'description' => 'Steamed white rice',
                'image' => 'https://images.unsplash.com/photo-1516684732162-798a0062be99?w=400',
                'variants' => [
                    ['name' => 'Regular', 'price' => 25000],
                    ['name' => 'Large', 'price' => 40000],
                ],
                'recipe' => [
                    ['ingredient' => 'Rice (long grain)', 'quantity' => 0.5],
                    ['ingredient' => 'Salt', 'quantity' => 0.2],
                ]
            ],
            [
                'name' => 'Festival',
                'category' => 'Sides',
                'description' => 'Sweet fried dumpling',
                'image' => 'https://images.unsplash.com/photo-1541529086526-db283c563270?w=400',
                'variants' => [
                    ['name' => '2 Pieces', 'price' => 20000],
                ],
                'recipe' => [
                    ['ingredient' => 'Festival Dough', 'quantity' => 0.3],
                    ['ingredient' => 'Vegetable Oil', 'quantity' => 2],
                ]
            ],
            [
                'name' => 'Bammy',
                'category' => 'Sides',
                'description' => 'Cassava flatbread',
                'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400',
                'variants' => [
                    ['name' => 'Regular', 'price' => 15000],
                ],
                'recipe' => [
                    ['ingredient' => 'Bammy', 'quantity' => 1],
                    ['ingredient' => 'Vegetable Oil', 'quantity' => 1],
                ]
            ],
            [
                'name' => 'Fried Plantain',
                'category' => 'Sides',
                'description' => 'Sweet fried ripe plantain',
                'image' => 'https://images.unsplash.com/photo-1520012497251-56ab1f75c6ae?w=400',
                'variants' => [
                    ['name' => 'Regular', 'price' => 25000],
                ],
                'recipe' => [
                    ['ingredient' => 'Plantain', 'quantity' => 1],
                    ['ingredient' => 'Vegetable Oil', 'quantity' => 2],
                ]
            ],
            [
                'name' => 'Steamed Vegetables',
                'category' => 'Sides',
                'description' => 'Cabbage, carrots, and cho cho',
                'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400',
                'variants' => [
                    ['name' => 'Regular', 'price' => 30000],
                ],
                'recipe' => [
                    ['ingredient' => 'Cabbage', 'quantity' => 0.3],
                    ['ingredient' => 'Carrot', 'quantity' => 0.15],
                    ['ingredient' => 'Cho Cho (Chayote)', 'quantity' => 0.15],
                    ['ingredient' => 'Onion', 'quantity' => 0.05],
                    ['ingredient' => 'Thyme', 'quantity' => 0.05],
                ]
            ],

            // COMBOS
            [
                'name' => 'Breakfast Combo',
                'category' => 'Combos',
                'description' => 'Ackee & saltfish with boiled provisions and fried dumpling',
                'image' => 'https://images.unsplash.com/photo-1604152135912-04a022e23696?w=400',
                'variants' => [
                    ['name' => 'Regular', 'price' => 125000],
                ],
                'recipe' => [
                    ['ingredient' => 'Ackee (canned)', 'quantity' => 0.5],
                    ['ingredient' => 'Saltfish (Salted Cod)', 'quantity' => 0.3],
                    ['ingredient' => 'Green Banana', 'quantity' => 0.3],
                    ['ingredient' => 'Yellow Yam', 'quantity' => 0.3],
                    ['ingredient' => 'Dumpling Flour', 'quantity' => 0.2],
                    ['ingredient' => 'Vegetable Oil', 'quantity' => 3],
                ]
            ],
            [
                'name' => 'Jerk Chicken Combo',
                'category' => 'Combos',
                'description' => 'Jerk chicken with rice & peas and festival',
                'image' => 'https://images.unsplash.com/photo-1598103442097-8b74394b95c6?w=400',
                'variants' => [
                    ['name' => 'Quarter', 'price' => 125000],
                    ['name' => 'Half', 'price' => 185000],
                ],
                'recipe' => [
                    ['ingredient' => 'Chicken (whole)', 'quantity' => 0.5],
                    ['ingredient' => 'Jerk Seasoning', 'quantity' => 2],
                    ['ingredient' => 'Rice (long grain)', 'quantity' => 0.4],
                    ['ingredient' => 'Red Kidney Beans (dried)', 'quantity' => 0.1],
                    ['ingredient' => 'Coconut Milk', 'quantity' => 0.3],
                    ['ingredient' => 'Festival Dough', 'quantity' => 0.2],
                ]
            ],
            [
                'name' => 'Curry Goat Combo',
                'category' => 'Combos',
                'description' => 'Curry goat with white rice and steamed vegetables',
                'image' => 'https://images.unsplash.com/photo-1585937421612-70e008ce19ed?w=400',
                'variants' => [
                    ['name' => 'Regular', 'price' => 155000],
                ],
                'recipe' => [
                    ['ingredient' => 'Goat Meat', 'quantity' => 0.6],
                    ['ingredient' => 'Curry Powder', 'quantity' => 2],
                    ['ingredient' => 'Rice (long grain)', 'quantity' => 0.4],
                    ['ingredient' => 'Cabbage', 'quantity' => 0.2],
                    ['ingredient' => 'Carrot', 'quantity' => 0.1],
                ]
            ],

            // SOUPS
            [
                'name' => 'Red Peas Soup',
                'category' => 'Soups',
                'description' => 'Hearty kidney bean soup with dumplings',
                'image' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400',
                'variants' => [
                    ['name' => 'Regular', 'price' => 65000],
                ],
                'recipe' => [
                    ['ingredient' => 'Red Kidney Beans (dried)', 'quantity' => 0.3],
                    ['ingredient' => 'Soup Bones', 'quantity' => 0.3],
                    ['ingredient' => 'Yellow Yam', 'quantity' => 0.2],
                    ['ingredient' => 'Pumpkin', 'quantity' => 0.2],
                    ['ingredient' => 'Dumpling Flour', 'quantity' => 0.15],
                    ['ingredient' => 'Scallion', 'quantity' => 0.2],
                    ['ingredient' => 'Thyme', 'quantity' => 0.1],
                ]
            ],
            [
                'name' => 'Chicken Soup',
                'category' => 'Soups',
                'description' => 'Traditional Jamaican chicken soup',
                'image' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400',
                'variants' => [
                    ['name' => 'Regular', 'price' => 70000],
                ],
                'recipe' => [
                    ['ingredient' => 'Chicken Backs/Necks', 'quantity' => 0.5],
                    ['ingredient' => 'Yellow Yam', 'quantity' => 0.2],
                    ['ingredient' => 'Pumpkin', 'quantity' => 0.15],
                    ['ingredient' => 'Carrot', 'quantity' => 0.1],
                    ['ingredient' => 'Cho Cho (Chayote)', 'quantity' => 0.15],
                    ['ingredient' => 'Dumpling Flour', 'quantity' => 0.15],
                    ['ingredient' => 'Scallion', 'quantity' => 0.2],
                ]
            ],
        ];

        foreach ($dishes as $dishData) {
            // Create dish
            $dishId = DB::table('products')->insertGetId([
                'name' => $dishData['name'],
                'type' => 'dish',
                'category_id' => $categoryIds[$dishData['category']],
                'price_cents' => $dishData['variants'][0]['price'],
                'description' => $dishData['description'] ?? null,
                'image_url' => $dishData['image'] ?? null,
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

        // === VENDORS ===
        $vendors = [
            [
                'name' => 'Caribbean Wholesale Ltd',
                'contact_name' => 'Michael Brown',
                'phone' => '876-555-0123',
                'email' => 'orders@caribbeanwholesale.jm',
                'notes' => 'Main supplier for proteins and provisions'
            ],
            [
                'name' => 'Island Fresh Produce',
                'contact_name' => 'Patricia Williams',
                'phone' => '876-555-0456',
                'email' => 'sales@islandfresh.jm',
                'notes' => 'Fresh vegetables and ground provisions'
            ],
            [
                'name' => 'Jamaica Beverage Distributors',
                'contact_name' => 'David Thompson',
                'phone' => '876-555-0789',
                'email' => 'orders@jambev.jm',
                'notes' => 'Beverages and canned goods'
            ],
            [
                'name' => 'Tropical Spice Company',
                'contact_name' => 'Sarah Campbell',
                'phone' => '876-555-0321',
                'email' => 'wholesale@tropicalspice.jm',
                'notes' => 'Seasonings, spices, and condiments'
            ],
            [
                'name' => 'Kingston Meats & Seafood',
                'contact_name' => 'Mark Johnson',
                'phone' => '876-555-0654',
                'email' => 'sales@kingstonmeats.jm',
                'notes' => 'Premium meats, fish, and oxtail'
            ]
        ];

        foreach ($vendors as $vendor) {
            DB::table('vendors')->insert([
                'name' => $vendor['name'],
                'contact_name' => $vendor['contact_name'],
                'phone' => $vendor['phone'],
                'email' => $vendor['email'],
                'notes' => $vendor['notes'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // === EXPENSE CATEGORIES ===
        $expenseCategories = [
            ['name' => 'Accounting & Bookkeeping', 'description' => 'Professional accounting and bookkeeping services'],
            ['name' => 'Bank Charges', 'description' => 'Bank fees and transaction charges'],
            ['name' => 'Subscriptions', 'description' => 'Software subscriptions and memberships'],
            ['name' => 'IT & Software Cost', 'description' => 'Technology and software expenses'],
            ['name' => 'Security Surveillance', 'description' => 'Security system and monitoring costs'],
            ['name' => 'Electricity', 'description' => 'Electrical utility bills'],
            ['name' => 'Water', 'description' => 'Water utility bills'],
            ['name' => 'Internet', 'description' => 'Internet service provider costs'],
            ['name' => 'Gas', 'description' => 'Gas utility bills'],
            ['name' => 'Staff Welfare', 'description' => 'Employee welfare and benefits'],
            ['name' => 'Insurance', 'description' => 'Business insurance premiums'],
            ['name' => 'Director 1 Fees', 'description' => 'Director 1 compensation'],
            ['name' => 'Director 2 Fees', 'description' => 'Director 2 compensation'],
            ['name' => 'Secretary Fees', 'description' => 'Secretary compensation'],
            ['name' => 'Commercial Rent', 'description' => 'Monthly rent for business premises'],
            ['name' => 'Transportation', 'description' => 'Transportation and delivery costs'],
            ['name' => 'Miscellaneous', 'description' => 'Other miscellaneous expenses'],
        ];

        foreach ($expenseCategories as $category) {
            DB::table('expense_categories')->insert([
                'name' => $category['name'],
                'description' => $category['description'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // === DEPARTMENTS ===
        $departments = [
            ['name' => 'Kitchen', 'description' => 'Food preparation and cooking', 'is_active' => true],
            ['name' => 'Front of House', 'description' => 'Customer service and dining area', 'is_active' => true],
            ['name' => 'Management', 'description' => 'Restaurant management', 'is_active' => true],
            ['name' => 'Cleaning', 'description' => 'Cleaning and maintenance', 'is_active' => true],
        ];

        $departmentIds = [];
        foreach ($departments as $dept) {
            $departmentIds[$dept['name']] = DB::table('departments')->insertGetId([
                'name' => $dept['name'],
                'description' => $dept['description'],
                'is_active' => $dept['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // === EMPLOYEES ===
        $employees = [
            [
                'employee_number' => 'EMP001',
                'first_name' => 'Marcus',
                'last_name' => 'Brown',
                'email' => 'marcus.brown@grillstone.jm',
                'phone' => '876-555-0101',
                'address' => '15 Hope Road, Kingston 6',
                'city' => 'Kingston',
                'parish' => 'St. Andrew',
                'department_id' => $departmentIds['Kitchen'],
                'position' => 'Head Chef',
                'hire_date' => '2023-01-15',
                'employment_type' => 'full-time',
                'employment_status' => 'active',
                'is_salaried' => true,
                'salary_amount' => 120000.00,
            ],
            [
                'employee_number' => 'EMP002',
                'first_name' => 'Keisha',
                'last_name' => 'Williams',
                'email' => 'keisha.williams@grillstone.jm',
                'phone' => '876-555-0102',
                'address' => '42 Barbican Road, Kingston 6',
                'city' => 'Kingston',
                'parish' => 'St. Andrew',
                'department_id' => $departmentIds['Front of House'],
                'position' => 'Server',
                'hire_date' => '2023-03-20',
                'employment_type' => 'full-time',
                'employment_status' => 'active',
                'is_salaried' => false,
                'hourly_rate' => 650.00,
            ],
            [
                'employee_number' => 'EMP003',
                'first_name' => 'Andre',
                'last_name' => 'Thompson',
                'email' => 'andre.thompson@grillstone.jm',
                'phone' => '876-555-0103',
                'address' => '28 Constant Spring Road, Kingston 10',
                'city' => 'Kingston',
                'parish' => 'St. Andrew',
                'department_id' => $departmentIds['Kitchen'],
                'position' => 'Line Cook',
                'hire_date' => '2023-06-01',
                'employment_type' => 'full-time',
                'employment_status' => 'active',
                'is_salaried' => true,
                'salary_amount' => 80000.00,
            ],
            [
                'employee_number' => 'EMP004',
                'first_name' => 'Shanique',
                'last_name' => 'Davis',
                'email' => 'shanique.davis@grillstone.jm',
                'phone' => '876-555-0104',
                'address' => '67 Half Way Tree Road, Kingston 5',
                'city' => 'Kingston',
                'parish' => 'St. Andrew',
                'department_id' => $departmentIds['Front of House'],
                'position' => 'Hostess',
                'hire_date' => '2024-01-10',
                'employment_type' => 'part-time',
                'employment_status' => 'active',
                'is_salaried' => false,
                'hourly_rate' => 600.00,
            ],
            [
                'employee_number' => 'EMP005',
                'first_name' => 'Christopher',
                'last_name' => 'Campbell',
                'email' => 'christopher.campbell@grillstone.jm',
                'phone' => '876-555-0105',
                'address' => '10 Trafalgar Road, Kingston 10',
                'city' => 'Kingston',
                'parish' => 'St. Andrew',
                'department_id' => $departmentIds['Management'],
                'position' => 'Restaurant Manager',
                'hire_date' => '2022-11-01',
                'employment_type' => 'full-time',
                'employment_status' => 'active',
                'is_salaried' => true,
                'salary_amount' => 150000.00,
            ],
        ];

        foreach ($employees as $emp) {
            DB::table('employees')->insert(array_merge($emp, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('✅ Jamaican restaurant data seeded successfully!');
        $this->command->info('🇯🇲 Summary:');
        $this->command->info('   - Categories: ' . count($categories));
        $this->command->info('   - Ingredients: ' . count($ingredients));
        $this->command->info('   - Beverages: ' . count($beverages));
        $this->command->info('   - Dishes: ' . count($dishes));
        $this->command->info('   - Vendors: ' . count($vendors));
        $this->command->info('   - Expense Categories: ' . count($expenseCategories));
        $this->command->info('   - Departments: ' . count($departments));
        $this->command->info('   - Employees: ' . count($employees));
    }
}
