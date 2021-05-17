<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionCreationService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Models\Settings;
use App\Models\Product;
use App\Models\Category;

// TODO after rewriting transaction handling
class TransactionCreationTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotCreateOrderForSelfWithoutPermission()
    {
        [$camper_user, $staff_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreationService($this->createFakeRequest($staff_user->id));

        $this->assertSame(0, $transactionService->getResult());
    }

    public function testCannotMakeTransactionWithNoProductsSelected()
    {
        [$camper_user, $staff_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreationService($this->createFakeRequest($camper_user->id, false));

        $this->assertSame(1, $transactionService->getResult());
    }

    public function testCannotMakeTransactionWithLessThanZeroQuantity()
    {
        [$camper_user, $staff_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreationService($this->createFakeRequest($camper_user->id, true, true));

        $this->assertSame(2, $transactionService->getResult());
    }

    public function testCannotMakeTransactionWithoutEnoughBalance()
    {
        $this->assertTrue(true);
    }

    public function testCannotMakeTransactionWithOutOfStockItem()
    {
        $this->assertTrue(true);
    }

    public function testUserBalanceCorrectAfterTransaction()
    {
        $this->assertTrue(true);
    }

    public function testSuccessfulTransaction()
    {
        [$camper_user, $staff_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreationService($this->createFakeRequest($camper_user->id));

        $this->assertSame(6, $transactionService->getResult());
        $this->assertCount(1, Transaction::all());
    }

    private function createFakeRecords(): array
    {
        $camper_role = Role::factory()->create([
            'name' => 'Camper',
            'superuser' => false,
            'order' => 2,
            'staff' => false
        ]);

        $camper_user = User::factory()->create([
            'role_id' => $camper_role->id
        ]);

        $staff_role = Role::factory()->create([
            'name' => 'Staff',
            'superuser' => false,
            'order' => 1,
            'staff' => true
        ]);

        $staff_user = User::factory()->create([
            'role_id' => $staff_role->id
        ]);

        Settings::factory()->createMany([
            [
                'setting' => 'gst',
                'value' => '1.05',
                'editor_id' => $staff_user->id
            ],
            [
                'setting' => 'pst',
                'value' => '1.07',
                'editor_id' => $staff_user->id
            ]
        ]);

        $this->actingAs($staff_user);

        return [$camper_user, $staff_user];
    }

    private function createFakeRequest(int $purchaser_id, bool $with_products = true, bool $negative_product = false): Request
    {

        [$chips, $hat, $coffee] = $this->createFakeProducts();

        if ($with_products) {
            $data = [
                'product' => [
                    $chips->id,
                    $hat->id,
                    $coffee->id
                ],
                'quantity' => [
                    $chips->id => $negative_product ? -1 : 1,
                    $hat->id => 2,
                    $coffee->id => 1
                ],
                'purchaser_id' => $purchaser_id
            ];
        } else {
            $data = [
                'quantity' => [
                    $chips->id => $negative_product ? -1 : 1,
                    $hat->id => 2,
                    $coffee->id => 1
                ],
                'purchaser_id' => $purchaser_id
            ];
        }

        return new Request($data);
    }

    /** @return Category[] */
    private function createFakeCategories()
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
    private function createFakeProducts(): array
    {

        [$food_category, $merch_category] = $this->createFakeCategories();

        $chips = Product::factory()->create([
            'name' => 'Chips',
            'price' => 1.50,
            'pst' => false,
            'category_id' => $food_category->id
        ]);

        $hat = Product::factory()->create([
            'name' => 'Hat',
            'price' => 15.00,
            'pst' => false,
            'category_id' => $merch_category->id
        ]);

        $coffee = Product::factory()->create([
            'name' => 'Coffee',
            'price' => 3.99,
            'pst' => true,
            'category_id' => $food_category->id
        ]);

        return [$chips, $hat, $coffee];
    }
}
