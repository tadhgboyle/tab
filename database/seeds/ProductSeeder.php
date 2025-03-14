<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Enums\ProductStatus;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    private static array $foodNames = [
        'Pop', 'Chips', 'Candy Bag', 'Coffee', 'Hot Dog', 'Chocolate Bar', 'M&Ms',
        'Skittles', 'Nerds', 'Gatorade', 'Ice Cream Sandwich', 'Rockets', 'Brownie',
        'Pretzels', 'Trail Mix', 'Smoothie', 'Cupcake', 'Mango Slices', 'Sushi Roll',
        'Granola Bar', 'Cookie Dough', 'Pizza Slice', 'Protein Bar',
    ];

    private static array $merchNames = [
        'Sweater', 'Hat', 'Pants', 'Fanny Pack', 'Sunglasses',
        'T Shirt', 'Skimboard', 'Waterbottle', 'Shorts', 'Long Sleeve Shirt',
        'Beanie', 'Flip Flops', 'Backpack', 'Phone Case', 'Wristband', 'Tank Top',
        'Keychain', 'Sticker Pack', 'Umbrella', 'Bandana', 'Socks', 'Drawstring Bag',
        'Wallet', 'Jacket', 'Gloves', 'Watch', 'Lanyard', 'Tumbler',
    ];

    private static array $generalNames = [
        'Ceramic', 'Tube Ride', 'Sunscreen', 'Tooth Brush', 'Boat Rental',
        'Floss', 'Tye Die Kit', 'Smores Kit', 'Family Photo', 'Computer Pass (1 hour)',
        'Picnic Basket', 'Fishing Rod', 'Canvas Painting Kit', 'Yoga Mat', 'Telescope',
        'Board Game Set', 'Bluetooth Speaker', 'Luggage Tag', 'Plant',
        'Headphones', 'Portable Charger', 'Art Supplies', 'Fitness Tracker',
        'Travel Pillow', 'Cookbook',
    ];

    private static array $variantProducts = [
        'Hoodie' => [
            'category' => 'Merch',
            'variant_options' => [
                'Size' => ['Small', 'Medium', 'Large'],
                'Color' => ['Green', 'Blue']
            ],
            'variants' => [
                [
                    'price' => 30_00,
                    'cost' => 25_00,
                    'sku' => 'HOODIE-S-G',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Small'],
                        ['option' => 'Color', 'value' => 'Green'],
                    ],
                ],
                [
                    'price' => 30_00,
                    'cost' => 25_00,
                    'sku' => 'HOODIE-S-B',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Small'],
                        ['option' => 'Color', 'value' => 'Blue'],
                    ],
                ],
                [
                    'price' => 35_00,
                    'cost' => 30_00,
                    'sku' => 'HOODIE-M-G',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Medium'],
                        ['option' => 'Color', 'value' => 'Green'],
                    ],
                ],
                [
                    'price' => 35_00,
                    'cost' => 30_00,
                    'sku' => 'HOODIE-M-B',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Medium'],
                        ['option' => 'Color', 'value' => 'Blue'],
                    ],
                ],
                [
                    'price' => 40_00,
                    'cost' => 35_00,
                    'sku' => 'HOODIE-L-G',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Large'],
                        ['option' => 'Color', 'value' => 'Green'],
                    ],
                ],
                [
                    'price' => 40_00,
                    'cost' => 35_00,
                    'sku' => 'HOODIE-L-B',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Large'],
                        ['option' => 'Color', 'value' => 'Blue'],
                    ],
                ]
            ]
        ],
        'Slurpee' => [
            'category' => 'Food',
            'variant_options' => [
                'Size' => ['Small', 'Large'],
                'Flavour' => ['Cola', 'Cherry', 'Blue Raspberry']
            ],
            'variants' => [
                [
                    'price' => 2_00,
                    'cost' => 1_00,
                    'sku' => 'SLURPEE-S-C',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Small'],
                        ['option' => 'Flavour', 'value' => 'Cola'],
                    ],
                ],
                [
                    'price' => 2_00,
                    'cost' => 1_00,
                    'sku' => 'SLURPEE-S-CH',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Small'],
                        ['option' => 'Flavour', 'value' => 'Cherry'],
                    ],
                ],
                [
                    'price' => 3_00,
                    'cost' => 1_50,
                    'sku' => 'SLURPEE-L-C',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Large'],
                        ['option' => 'Flavour', 'value' => 'Cola'],
                    ],
                ],
                [
                    'price' => 3_00,
                    'cost' => 1_50,
                    'sku' => 'SLURPEE-L-BR',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Large'],
                        ['option' => 'Flavour', 'value' => 'Blue Raspberry'],
                    ],
                ],
            ],
        ],
        'Ice Cream' => [
            'category' => 'Food',
            'variant_options' => [
                'Size' => ['1 scoop', '2 scoops', '3 scoops'],
                'Flavour' => ['Vanilla', 'Chocolate', 'Strawberry']
            ],
            'variants' => [
                [
                    'price' => 3_00,
                    'cost' => 1_00,
                    'sku' => 'ICECREAM-1-V',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => '1 scoop'],
                        ['option' => 'Flavour', 'value' => 'Vanilla'],
                    ],
                ],
                [
                    'price' => 3_00,
                    'cost' => 1_00,
                    'sku' => 'ICECREAM-1-C',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => '1 scoop'],
                        ['option' => 'Flavour', 'value' => 'Chocolate'],
                    ],
                ],
                [
                    'price' => 3_00,
                    'cost' => 1_00,
                    'sku' => 'ICECREAM-1-S',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => '1 scoop'],
                        ['option' => 'Flavour', 'value' => 'Strawberry'],
                    ],
                ],
                [
                    'price' => 5_00,
                    'cost' => 2_00,
                    'sku' => 'ICECREAM-2-V',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => '2 scoops'],
                        ['option' => 'Flavour', 'value' => 'Vanilla'],
                    ],
                ],
                [
                    'price' => 5_00,
                    'cost' => 2_00,
                    'sku' => 'ICECREAM-2-C',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => '2 scoops'],
                        ['option' => 'Flavour', 'value' => 'Chocolate'],
                    ],
                ],
                [
                    'price' => 5_00,
                    'cost' => 2_00,
                    'sku' => 'ICECREAM-2-S',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => '2 scoops'],
                        ['option' => 'Flavour', 'value' => 'Strawberry'],
                    ],
                ],
                [
                    'price' => 7_00,
                    'cost' => 3_00,
                    'sku' => 'ICECREAM-3-V',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => '3 scoops'],
                        ['option' => 'Flavour', 'value' => 'Vanilla'],
                    ],
                ],
            ],
        ],
        'Wakeboard Rental' => [
            'category' => 'General',
            'variant_options' => [
                'Duration' => ['1 hour', '2 hours', '3 hours'],
            ],
            'variants' => [
                [
                    'price' => 20_00,
                    'cost' => 4_00,
                    'sku' => 'WAKEBOARD-1',
                    'option_value_assignments' => [
                        ['option' => 'Duration', 'value' => '1 hour'],
                    ],
                ],
                [
                    'price' => 35_00,
                    'cost' => 7_00,
                    'sku' => 'WAKEBOARD-2',
                    'option_value_assignments' => [
                        ['option' => 'Duration', 'value' => '2 hours'],
                    ],
                ],
                [
                    'price' => 50_00,
                    'cost' => 10_00,
                    'sku' => 'WAKEBOARD-3',
                    'option_value_assignments' => [
                        ['option' => 'Duration', 'value' => '3 hours'],
                    ],
                ],
            ],
        ],
        'Candle' => [
            'category' => 'General',
            'variant_options' => [
                'Scent' => ['Vanilla', 'Lavender', 'Pine'],
                'Size' => ['Small', 'Medium', 'Large'],
                'Colour' => ['White', 'Blue', 'Red'],
            ],
            'variants' => [
                [
                    'price' => 10_00,
                    'cost' => 5_00,
                    'sku' => 'CANDLE-S-W-V',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Small'],
                        ['option' => 'Colour', 'value' => 'White'],
                        ['option' => 'Scent', 'value' => 'Vanilla'],
                    ],
                ],
                [
                    'price' => 10_00,
                    'cost' => 5_00,
                    'sku' => 'CANDLE-S-W-L',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Small'],
                        ['option' => 'Colour', 'value' => 'White'],
                        ['option' => 'Scent', 'value' => 'Lavender'],
                    ],
                ],
                [
                    'price' => 10_00,
                    'cost' => 5_00,
                    'sku' => 'CANDLE-S-W-P',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Small'],
                        ['option' => 'Colour', 'value' => 'White'],
                        ['option' => 'Scent', 'value' => 'Pine'],
                    ],
                ],
                [
                    'price' => 15_00,
                    'cost' => 7_50,
                    'sku' => 'CANDLE-M-B-V',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Medium'],
                        ['option' => 'Colour', 'value' => 'Blue'],
                        ['option' => 'Scent', 'value' => 'Vanilla'],
                    ],
                ],
                [
                    'price' => 15_00,
                    'cost' => 7_50,
                    'sku' => 'CANDLE-M-B-L',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Medium'],
                        ['option' => 'Colour', 'value' => 'Blue'],
                        ['option' => 'Scent', 'value' => 'Lavender'],
                    ],
                ],
                [
                    'price' => 15_00,
                    'cost' => 7_50,
                    'sku' => 'CANDLE-M-B-P',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Medium'],
                        ['option' => 'Colour', 'value' => 'Blue'],
                        ['option' => 'Scent', 'value' => 'Pine'],
                    ],
                ],
                [
                    'price' => 20_00,
                    'cost' => 10_00,
                    'sku' => 'CANDLE-L-R-V',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Large'],
                        ['option' => 'Colour', 'value' => 'Red'],
                        ['option' => 'Scent', 'value' => 'Vanilla'],
                    ],
                ],
                [
                    'price' => 20_00,
                    'cost' => 10_00,
                    'sku' => 'CANDLE-L-R-L',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Large'],
                        ['option' => 'Colour', 'value' => 'Red'],
                        ['option' => 'Scent', 'value' => 'Lavender'],
                    ],
                ],
                [
                    'price' => 20_00,
                    'cost' => 10_00,
                    'sku' => 'CANDLE-L-R-P',
                    'option_value_assignments' => [
                        ['option' => 'Size', 'value' => 'Large'],
                        ['option' => 'Colour', 'value' => 'Red'],
                        ['option' => 'Scent', 'value' => 'Pine'],
                    ],
                ],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(array $categories): void
    {
        [$food_category, $merch_category, , $general_category] = $categories;

        foreach (self::$foodNames as $name) {
            Product::factory()->state([
                'name' => $name,
                'sku' => $this->formatSku($name),
                'category_id' => $food_category,
                'status' => random_int(0, 3) === 1 ? ProductStatus::Draft : ProductStatus::Active,
            ])->create();
        }

        foreach (self::$merchNames as $name) {
            Product::factory()->state([
                'name' => $name,
                'sku' => $this->formatSku($name),
                'category_id' => $merch_category,
                'status' => random_int(0, 3) === 1 ? ProductStatus::Draft : ProductStatus::Active,
            ])->create();
        }

        foreach (self::$generalNames as $name) {
            Product::factory()->state([
                'name' => $name,
                'sku' => $this->formatSku($name),
                'category_id' => $general_category,
                'status' => random_int(0, 3) === 1 ? ProductStatus::Draft : ProductStatus::Active,
            ])->create();
        }

        foreach (self::$variantProducts as $name => $productData) {
            $product = Product::factory()->state([
                'name' => $name,
                'sku' => null,
                // TODO should these be nullable at the database level?
                'price' => 0,
                'cost' => 0,
                'category_id' => Category::firstWhere('name', $productData['category']),
            ])->create();

            foreach ($productData['variant_options'] as $optionName => $values) {
                $option = $product->variantOptions()->create([
                    'name' => $optionName,
                ]);

                foreach ($values as $value) {
                    $option->values()->create([
                        'value' => $value,
                    ]);
                }
            }

            if (!isset($productData['variants'])) {
                continue;
            }

            foreach ($productData['variants'] as $variantData) {
                $variant = $product->variants()->create([
                    'price' => $variantData['price'],
                    'cost' => random_int(0, 1) ? null : random_int(0, $variantData['price']),
                    'sku' => $variantData['sku'],
                    'stock' => random_int(0, 100),
                    'box_size' => random_int(1, 3) === 1 ? random_int(1, 10) : null,
                ]);

                foreach ($variantData['option_value_assignments'] as $assignmentData) {
                    $option = $product->variantOptions()->where('name', $assignmentData['option'])->first();
                    $value = $option->values()->where('value', $assignmentData['value'])->first();

                    $variant->optionValueAssignments()->create([
                        'product_variant_option_id' => $option->id,
                        'product_variant_option_value_id' => $value->id,
                    ]);
                }
            }
        }
    }

    private function formatSku(string $name): string
    {
        return strtoupper(str_replace(' ', '-', $name));
    }
}
