<?php

namespace Tests\Unit\Product;

use Tests\TestCase;
use Cknow\Money\Money;
use App\Models\Product;
use App\Models\Category;
use App\Models\Settings;
use App\Enums\ProductStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Settings::factory()->createMany([
            [
                'setting' => 'gst',
                'value' => '5.00',
            ],
            [
                'setting' => 'pst',
                'value' => '7.00',
            ]
        ]);
    }

    public function testPriceCastedToMoneyObject(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'price' => 10_00,
            'category_id' => $category->id,
        ]);

        $this->assertEquals(Money::parse(10_00), $product->price);
    }

    public function testBelongsToACategory(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    public function testGetPriceAfterTaxWithPst(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'price' => 10_00,
            'pst' => true,
            'category_id' => $category->id,
        ]);

        $this->assertEquals(Money::parse(11_20), $product->getPriceAfterTax());
    }

    public function testGetPriceAfterTaxWithoutPst(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'price' => 10_00,
            'pst' => false,
            'category_id' => $category->id,
        ]);

        $this->assertEquals(Money::parse(10_50), $product->getPriceAfterTax());
    }

    public function testIsActive(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'status' => ProductStatus::Active,
            'category_id' => $category->id,
        ]);

        $this->assertTrue($product->isActive());

        $product->status = ProductStatus::Draft;

        $this->assertFalse($product->isActive());
    }

    public function testHasStockOnNormalProduct(): void
    {
        $product = Product::factory()->create([
            'unlimited_stock' => false,
            'stock_override' => false,
            'stock' => 0,
            'category_id' => Category::factory()->create()->id,
        ]);

        $this->assertTrue($product->hasStock($product->stock));
        $this->assertFalse($product->hasStock($product->stock + 1));
    }

    public function testHasStockOnStockOverrideProduct(): void
    {
        $product = Product::factory()->create([
            'unlimited_stock' => false,
            'stock_override' => true,
            'stock' => 0,
            'category_id' => Category::factory()->create()->id,
        ]);

        $this->assertTrue($product->hasStock($product->stock));
        $this->assertTrue($product->hasStock($product->stock + 1));
    }

    public function testHasStockOnUnlimitedStockProduct(): void
    {
        $product = Product::factory()->create([
            'unlimited_stock' => true,
            'stock_override' => false,
            'stock' => 10,
            'category_id' => Category::factory()->create()->id,
        ]);

        $this->assertTrue($product->hasStock($product->stock));
        $this->assertTrue($product->hasStock($product->stock + 1));
    }

    public function testGetStockOnNormalProduct(): void
    {
        $product = Product::factory()->create([
            'unlimited_stock' => false,
            'stock_override' => false,
            'stock' => 10,
            'category_id' => Category::factory()->create()->id,
        ]);

        $this->assertEquals($product->stock, $product->getStock());
    }

    public function testGetStockOnUnlimitedStockProduct(): void
    {
        $product = Product::factory()->create([
            'unlimited_stock' => true,
            'stock_override' => false,
            'stock' => 10,
            'category_id' => Category::factory()->create()->id,
        ]);

        $this->assertStringContainsString('Unlimited', $product->getStock());
    }

    public function testRemoveStockOnNormalProduct(): void
    {
        $product = Product::factory()->create([
            'unlimited_stock' => false,
            'stock_override' => false,
            'stock' => 10,
            'category_id' => Category::factory()->create()->id,
        ]);

        $this->assertFalse($product->removeStock($product->stock + 1));
        $this->assertTrue($product->removeStock($product->stock));
        $this->assertSame(0, $product->stock);
    }

    public function testRemoveStockOnUnlimitedStockProduct(): void
    {
        $product = Product::factory()->create([
            'unlimited_stock' => true,
            'stock_override' => false,
            'stock' => 10,
            'category_id' => Category::factory()->create()->id,
        ]);

        $this->assertTrue($product->removeStock($product->stock + 1));
        $this->assertSame(10, $product->stock);
    }

    public function testRemoveStockOnStockOverrideProduct(): void
    {
        $product = Product::factory()->create([
            'unlimited_stock' => false,
            'stock_override' => true,
            'stock' => 10,
            'category_id' => Category::factory()->create()->id,
        ]);

        $this->assertTrue($product->removeStock($product->stock + 1));
        $this->assertSame(-1, $product->stock);
    }

    public function testAdjustStock(): void
    {
        $product = Product::factory()->create([
            'stock' => 10,
            'category_id' => Category::factory()->create()->id,
        ]);

        $product->adjustStock(5);
        $this->assertSame(15, $product->stock);

        $product->adjustStock(-10);
        $this->assertSame(5, $product->stock);
    }

    public function testAddBox(): void
    {
        $product = Product::factory()->create([
            'stock' => 10,
            'box_size' => 2,
            'category_id' => Category::factory()->create()->id,
        ]);

        $product->addBox(2);
        $this->assertSame(14, $product->stock);

        $product->addBox(-4);
        $this->assertSame(6, $product->stock);
    }
}
