<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    private static array $foodNames = [
        'Pop', 'Chips', 'Candy Bag', 'Slurpee (Small)', 'Slurpee (Large)', 'Coffee',
        'Hot Dog', 'Ice Cream (1 scoop)', 'Ice Cream (2 scoops)', 'Ice Cream (3 scoops)',
        'Chocolate Bar', 'M&Ms', 'Skittles', 'Nerds', 'Gatorade', 'Ice Cream Sandwich', 'Rockets',
        'Brownie', 'Pretzels', 'Trail Mix', 'Smoothie', 'Cupcake', 'Mango Slices',
        'Sushi Roll', 'Granola Bar', 'Cookie Dough', 'Pizza Slice', 'Protein Bar'
    ];

    private static array $merchNames = [
        'Sweater', 'Hat', 'Pants', 'Fanny Pack', 'Sunglasses',
        'T Shirt', 'Skimboard', 'Waterbottle', 'Shorts', 'Long Sleeve Shirt',
        'Beanie', 'Flip Flops', 'Backpack', 'Phone Case', 'Wristband', 'Tank Top',
        'Keychain', 'Sticker Pack', 'Umbrella', 'Bandana', 'Socks', 'Drawstring Bag',
        'Wallet', 'Jacket', 'Gloves', 'Watch', 'Lanyard', 'Tumbler'
    ];

    private static array $generalNames = [
        'Ceramic', 'Tube Ride', 'Sunscreen', 'Tooth Brush', 'Boat Rental',
        'Wakeboard (1 hour)', 'Floss', 'Tye Die Kit', 'Smores Kit',
        'Family Photo', 'Computer Pass (1 hour)',
        'Picnic Basket', 'Fishing Rod', 'Canvas Painting Kit', 'Yoga Mat', 'Telescope',
        'Board Game Set', 'Bluetooth Speaker', 'Candle Set', 'Luggage Tag', 'Plant',
        'Headphones', 'Portable Charger', 'Art Supplies', 'Fitness Tracker',
        'Travel Pillow', 'Cookbook'
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
                'category_id' => $food_category
            ])->create();
        }

        foreach (self::$merchNames as $name) {
            Product::factory()->state([
                'name' => $name,
                'category_id' => $merch_category
            ])->create();
        }

        foreach (self::$generalNames as $name) {
            Product::factory()->state([
                'name' => $name,
                'category_id' => $general_category
            ])->create();
        }

        // Create example variant product
        $product = Product::factory()->state([
            'name' => 'Hoodie',
            'category_id' => $merch_category
        ])->create();
        $sizeVariantOption = $product->variantOptions()->create([
            'name' => 'Size',
        ]);
        $colorVariantOption = $product->variantOptions()->create([
            'name' => 'Color',
        ]);
        $sizeVariantOption->values()->createMany([
            ['value' => 'Small'],
            ['value' => 'Medium'],
            ['value' => 'Large'],
        ]);
        $colorVariantOption->values()->createMany([
            ['value' => 'Green'],
            ['value' => 'Blue'],
        ]);
        $variant = $product->variants()->create([
            'price' => 3000,
            'sku' => 'Hoodie-Small-Green',
        ]);
        $variant->optionValueAssignments()->create([
            'product_variant_option_id' => $sizeVariantOption->id,
            'product_variant_option_value_id' => $sizeVariantOption->values()->where('value', 'Small')->first()->id,
        ]);
        $variant->optionValueAssignments()->create([
            'product_variant_option_id' => $colorVariantOption->id,
            'product_variant_option_value_id' => $colorVariantOption->values()->where('value', 'Green')->first()->id,
        ]);
    }
}
