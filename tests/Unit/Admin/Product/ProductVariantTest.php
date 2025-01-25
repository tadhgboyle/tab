<?php

namespace Tests\Unit\Admin\Product;

use Tests\TestCase;
use Cknow\Money\Money;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductVariantTest extends TestCase
{
    use RefreshDatabase;

    private Category $_category;

    public function setUp(): void
    {
        parent::setUp();

        $this->_category = Category::factory()->create();
    }

    public function testCasts(): void
    {
        $product = Product::factory()->create([
            'price' => 0,
            'category_id' => $this->_category->id,
        ]);
        $variant = $product->variants()->create([
            'sku' => 'SKU-1',
            'stock' => 10,
            'price' => 10_00,
        ]);

        $this->assertEquals(Money::parse(10_00), $variant->price);
        $this->assertEquals(10, $variant->stock);
    }

    public function testDescription(): void
    {
        $product = Product::factory()->create([
            'name' => 'Product 1',
            'category_id' => $this->_category->id,
        ]);
        $colourOption = $product->variantOptions()->create(['name' => 'Color']);
        $sizeOption = $product->variantOptions()->create(['name' => 'Size']);

        $redValue = $colourOption->values()->create(['value' => 'Red']);
        $largeValue = $sizeOption->values()->create(['value' => 'Large']);

        $variant = $product->variants()->create([
            'sku' => 'SKU-1',
            'stock' => 10,
            'price' => 10_00,
        ]);

        $variant->optionValueAssignments()->createMany([
            [
                'product_variant_option_id' => $colourOption->id,
                'product_variant_option_value_id' => $redValue->id,
            ],
            [
                'product_variant_option_id' => $sizeOption->id,
                'product_variant_option_value_id' => $largeValue->id,
            ],
        ]);

        $this->assertEquals('Product 1: Color: Red, Size: Large', $variant->description());
    }

    public function testDescriptionWhenTrashed(): void
    {
        $product = Product::factory()->create([
            'name' => 'Product 1',
            'category_id' => $this->_category->id,
        ]);
        $colourOption = $product->variantOptions()->create(['name' => 'Color']);
        $sizeOption = $product->variantOptions()->create(['name' => 'Size']);

        $redValue = $colourOption->values()->create(['value' => 'Red']);
        $largeValue = $sizeOption->values()->create(['value' => 'Large']);

        $variant = $product->variants()->create([
            'sku' => 'SKU-1',
            'stock' => 10,
            'price' => 10_00,
        ]);

        $variant->optionValueAssignments()->createMany([
            [
                'product_variant_option_id' => $colourOption->id,
                'product_variant_option_value_id' => $redValue->id,
            ],
            [
                'product_variant_option_id' => $sizeOption->id,
                'product_variant_option_value_id' => $largeValue->id,
            ],
        ]);

        $colourOption->delete();

        $this->assertEquals('Product 1: Size: Large', $variant->description());
    }

    public function testDescriptionsWithTrashed(): void
    {
        $product = Product::factory()->create([
            'name' => 'Product 1',
            'category_id' => $this->_category->id,
        ]);
        $colourOption = $product->variantOptions()->create(['name' => 'Color']);
        $sizeOption = $product->variantOptions()->create(['name' => 'Size']);

        $redValue = $colourOption->values()->create(['value' => 'Red']);
        $largeValue = $sizeOption->values()->create(['value' => 'Large']);

        $variant = $product->variants()->create([
            'sku' => 'SKU-1',
            'stock' => 10,
            'price' => 10_00,
        ]);

        $variant->optionValueAssignments()->createMany([
            [
                'product_variant_option_id' => $colourOption->id,
                'product_variant_option_value_id' => $redValue->id,
            ],
            [
                'product_variant_option_id' => $sizeOption->id,
                'product_variant_option_value_id' => $largeValue->id,
            ],
        ]);

        $colourOption->delete();

        $this->assertEquals(['Color: Red', 'Size: Large'], $variant->descriptions(false)->toArray());
        $this->assertEquals(['Size: Large'], $variant->descriptions(true)->toArray());
    }

    public function testOptionValueForNullWhenNoAssignment(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->_category->id,
        ]);
        $colourOption = $product->variantOptions()->create(['name' => 'Color']);
        $sizeOption = $product->variantOptions()->create(['name' => 'Size']);

        $variant = $product->variants()->create([
            'sku' => 'SKU-1',
            'stock' => 10,
            'price' => 10_00,
        ]);

        $this->assertNull($variant->optionValueFor($colourOption));
        $this->assertNull($variant->optionValueFor($sizeOption));
    }

    public function testOptionValueForNullWhenOptionTrashed(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->_category->id,
        ]);
        $colourOption = $product->variantOptions()->create(['name' => 'Color']);
        $sizeOption = $product->variantOptions()->create(['name' => 'Size']);

        $redValue = $colourOption->values()->create(['value' => 'Red']);
        $largeValue = $sizeOption->values()->create(['value' => 'Large']);

        $variant = $product->variants()->create([
            'sku' => 'SKU-1',
            'stock' => 10,
            'price' => 10_00,
        ]);

        $variant->optionValueAssignments()->createMany([
            [
                'product_variant_option_id' => $colourOption->id,
                'product_variant_option_value_id' => $redValue->id,
            ],
            [
                'product_variant_option_id' => $sizeOption->id,
                'product_variant_option_value_id' => $largeValue->id,
            ],
        ]);

        $colourOption->delete();

        $this->assertNull($variant->optionValueFor($colourOption));
    }

    public function testOptionValueForNullWhenValueTrashed(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->_category->id,
        ]);
        $colourOption = $product->variantOptions()->create(['name' => 'Color']);
        $sizeOption = $product->variantOptions()->create(['name' => 'Size']);

        $redValue = $colourOption->values()->create(['value' => 'Red']);
        $largeValue = $sizeOption->values()->create(['value' => 'Large']);

        $variant = $product->variants()->create([
            'sku' => 'SKU-1',
            'stock' => 10,
            'price' => 10_00,
        ]);

        $variant->optionValueAssignments()->createMany([
            [
                'product_variant_option_id' => $colourOption->id,
                'product_variant_option_value_id' => $redValue->id,
            ],
            [
                'product_variant_option_id' => $sizeOption->id,
                'product_variant_option_value_id' => $largeValue->id,
            ],
        ]);

        $redValue->delete();

        $this->assertNull($variant->optionValueFor($colourOption));
    }

    public function testOptionValueFor(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->_category->id,
        ]);
        $colourOption = $product->variantOptions()->create(['name' => 'Color']);
        $sizeOption = $product->variantOptions()->create(['name' => 'Size']);

        $redValue = $colourOption->values()->create(['value' => 'Red']);
        $largeValue = $sizeOption->values()->create(['value' => 'Large']);

        $variant = $product->variants()->create([
            'sku' => 'SKU-1',
            'stock' => 10,
            'price' => 10_00,
        ]);

        $variant->optionValueAssignments()->createMany([
            [
                'product_variant_option_id' => $colourOption->id,
                'product_variant_option_value_id' => $redValue->id,
            ],
            [
                'product_variant_option_id' => $sizeOption->id,
                'product_variant_option_value_id' => $largeValue->id,
            ],
        ]);

        $this->assertEquals($redValue->id, $variant->optionValueFor($colourOption)->id);
        $this->assertEquals($largeValue->id, $variant->optionValueFor($sizeOption)->id);
    }

    public function testStockOverrideAttributeReadFromProduct(): void
    {
        $product = Product::factory()->create([
            'stock_override' => true,
            'category_id' => $this->_category->id,
        ]);
        $variant = $product->variants()->create([
            'sku' => 'SKU-1',
            'stock' => 10,
            'price' => 10_00,
        ]);

        $this->assertTrue($variant->stock_override);
    }

    public function testUnlimitedStockAttributeReadFromProduct(): void
    {
        $product = Product::factory()->create([
            'unlimited_stock' => true,
            'category_id' => $this->_category->id,
        ]);
        $variant = $product->variants()->create([
            'sku' => 'SKU-1',
            'stock' => 10,
            'price' => 10_00,
        ]);

        $this->assertTrue($variant->unlimited_stock);
    }
}
