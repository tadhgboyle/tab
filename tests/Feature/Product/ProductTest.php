<?php

namespace Tests\Feature\Product;

use App\Helpers\RotationHelper;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Settings;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use App\Models\User;
use App\Models\UserLimits;
use Database\Seeders\RotationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase{

    use RefreshDatabase;

    public function testFindSoldCount(): void
    {
        $this->createFakeRecords();

        $current_rotation = resolve(RotationHelper::class)->getCurrentRotation()->id;

        foreach ([
            ['Skittles', 2],
            ['Hat', 1],
            ['Sweater', 1],
            ['Coffee', 2]
        ] as $product) {
            [$name, $sold_count] = $product;
            $this->assertEquals($sold_count, Product::firstWhere('name', $name)->findSold($current_rotation));
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

        $transaction1 = Transaction::factory()->create([
            'purchaser_id' => $user->id,
            'cashier_id' => $user->id,
            'rotation_id' => resolve(RotationHelper::class)->getCurrentRotation()->id,
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
            'rotation_id' => resolve(RotationHelper::class)->getCurrentRotation()->id,
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
