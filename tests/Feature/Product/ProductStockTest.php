<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductStockTest extends TestCase
{
    use RefreshDatabase;

    public function testHasStockOnNormalProduct(): void
    {
        $product = Product::factory()->create([
            'unlimited_stock' => false,
            'stock_override' => false,
            'stock' => 10,
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
            'stock' => 10,
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
