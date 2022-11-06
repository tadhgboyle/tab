<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Settings;
use App\Models\UserLimits;
use App\Models\Transaction;
use App\Helpers\RotationHelper;
use App\Models\TransactionProduct;
use Database\Seeders\RotationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
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

    public function testFindSoldCount(): void
    {
        $this->createFakeRecords();

        $current_rotation = resolve(RotationHelper::class)->getRotations()->first()->id;

        foreach ([['Skittles', 2], ['Hat', 1], ['Sweater', 1], ['Coffee', 2]] as $product) {
            [$name, $sold_count] = $product;
            $this->assertEquals($sold_count, Product::firstWhere('name', $name)->findSold($current_rotation));
        }

        foreach ([['Skittles', 4], ['Hat', 2], ['Sweater', 2], ['Coffee', 4]] as $product) {
            [$name, $sold_count] = $product;
            $this->assertEquals($sold_count, Product::firstWhere('name', $name)->findSold('*'));
        }
    }

    /** @return Role[] */
    private function createRoles(): array
    {
        $superadmin_role = Role::factory()->create();

        $camper_role = Role::factory()->create([
            'name' => 'Camper',
            'staff' => false,
            'superuser' => false,
            'order' => 2
        ]);

        return [$superadmin_role, $camper_role];
    }

    private function createSuperadminUser(Role $superadmin_role): User
    {
        return User::factory()->create([
            'role_id' => $superadmin_role->id
        ]);
    }

    private function createFakeRecords(): array
    {
        app(RotationSeeder::class)->run();

        [$superadmin_role] = $this->createRoles();

        $user = $this->createSuperadminUser($superadmin_role);

        Settings::factory()->createMany([
            [
                'setting' => 'gst',
                'value' => '1.05',
            ],
            [
                'setting' => 'pst',
                'value' => '1.07',
            ]
        ]);

        [$food_category, $merch_category] = $this->createFakeCategories();

        UserLimits::factory()->create([
            'user_id' => $user->id,
            'category_id' => $food_category->id,
            'limit_per' => 15,
            'duration' => UserLimits::LIMIT_DAILY
        ]);

        UserLimits::factory()->create([
            'user_id' => $user->id,
            'category_id' => $merch_category->id,
            'limit_per' => -1
        ]);

        [$skittles, $sweater, $coffee, $hat] = $this->createFakeProducts($food_category->id, $merch_category->id);

        $rotation = resolve(RotationHelper::class)->getRotations()->first()->id;

        $transaction1 = Transaction::factory()->create([
            'purchaser_id' => $user->id,
            'cashier_id' => $user->id,
            'rotation_id' => $rotation,
            'total_price' => 3.15 // TODO
        ]);

        $skittles_product = TransactionProduct::from($skittles, 2, 1.05);
        $skittles_product->transaction_id = $transaction1->id;
        $hat_product = TransactionProduct::from($hat, 1, 1.05);
        $hat_product->transaction_id = $transaction1->id;

        $transaction1->products()->saveMany([
            $skittles_product,
            $hat_product,
        ]);

        $transaction2 = Transaction::factory()->create([
            'purchaser_id' => $user->id,
            'cashier_id' => $user->id,
            'rotation_id' => $rotation,
            'total_price' => 44.79 // TODO
        ]);

        $sweater_product = TransactionProduct::from($sweater, 1, 1.05, 1.07);
        $sweater_product->transaction_id = $transaction2->id;
        $coffee_product = TransactionProduct::from($coffee, 2, 1.05, 1.07);
        $coffee_product->transaction_id = $transaction2->id;

        $transaction2->products()->saveMany([
            $sweater_product,
            $coffee_product,
        ]);

        $rotation = resolve(RotationHelper::class)->getRotations()->last()->id;

        $transaction3 = Transaction::factory()->create([
            'purchaser_id' => $user->id,
            'cashier_id' => $user->id,
            'rotation_id' => $rotation,
            'total_price' => 3.15 // TODO
        ]);

        $skittles_product = TransactionProduct::from($skittles, 2, 1.05);
        $skittles_product->transaction_id = $transaction1->id;
        $hat_product = TransactionProduct::from($hat, 1, 1.05);
        $hat_product->transaction_id = $transaction1->id;

        $transaction3->products()->saveMany([
            $skittles_product,
            $hat_product,
        ]);

        $transaction4 = Transaction::factory()->create([
            'purchaser_id' => $user->id,
            'cashier_id' => $user->id,
            'rotation_id' => $rotation,
            'total_price' => 44.79 // TODO
        ]);

        $sweater_product = TransactionProduct::from($sweater, 1, 1.05, 1.07);
        $sweater_product->transaction_id = $transaction2->id;
        $coffee_product = TransactionProduct::from($coffee, 2, 1.05, 1.07);
        $coffee_product->transaction_id = $transaction2->id;

        $transaction4->products()->saveMany([
            $sweater_product,
            $coffee_product,
        ]);
        return [$user, $food_category, $merch_category];
    }

    /** @return Category[] */
    private function createFakeCategories(): array
    {
        $food_category = Category::factory()->create([
            'name' => 'Food'
        ]);

        $merch_category = Category::factory()->create([
            'name' => 'Merch'
        ]);

        return [$food_category, $merch_category];
    }

    /** @return Product[] */
    private function createFakeProducts($food_category_id, $merch_category_id): array
    {
        $skittles = Product::factory()->create([
            'name' => 'Skittles',
            'price' => 1.50,
            'pst' => false,
            'category_id' => $food_category_id
        ]);

        $sweater = Product::factory()->create([
            'name' => 'Sweater',
            'price' => 39.99,
            'pst' => true,
            'category_id' => $merch_category_id
        ]);

        $coffee = Product::factory()->create([
            'name' => 'Coffee',
            'price' => 3.99,
            'pst' => true,
            'category_id' => $food_category_id
        ]);

        $hat = Product::factory()->create([
            'name' => 'Hat',
            'price' => 15.00,
            'pst' => false,
            'category_id' => $merch_category_id
        ]);

        return [$skittles, $sweater, $coffee, $hat];
    }
}
