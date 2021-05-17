<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Settings;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Services\TransactionCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

    public function testCannotMakeTransactionWithOutOfStockItem()
    {
        [$camper_user, $staff_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreationService($this->createFakeRequest($camper_user->id, true, false, true));

        $this->assertSame(3, $transactionService->getResult());
    }

    public function testCannotMakeTransactionWithoutEnoughBalance()
    {
        [$camper_user, $staff_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreationService($this->createFakeRequest($camper_user->id, true, false, false, true));

        $this->assertSame(4, $transactionService->getResult());
    }

    public function testCannotMakeTransactionWithoutEnoughBalanceInCategory()
    {
        $this->assertTrue(true); // TODO
    }

    public function testUserBalanceCorrectAfterTransaction()
    {
        [$camper_user, $staff_user] = $this->createFakeRecords();

        $camper_user_balance = $camper_user->balance;

        $transactionService = new TransactionCreationService($this->createFakeRequest($camper_user->id));

        $this->assertEquals($camper_user_balance - $transactionService->getTotalPrice(), $camper_user->balance); // TODO, user balance seems to not be updated
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

    private function createFakeRequest(int $purchaser_id, bool $with_products = true, bool $negative_product = false, bool $over_stock = false, bool $over_balance = false): Request
    {
        [$chips, $hat, $coffee] = $this->createFakeProducts($over_balance);

        if ($with_products) {
            $data = [
                'product' => [
                    $chips->id,
                    $hat->id,
                    $coffee->id
                ],
                'quantity' => [
                    $chips->id => $negative_product ? -1 : ($over_stock ? 100 : 1),
                    $hat->id => 2,
                    $coffee->id => 1
                ],
                'purchaser_id' => $purchaser_id
            ];
        } else {
            $data = [
                'quantity' => [
                    $chips->id => $negative_product ? -1 : ($over_stock ? 100 : 1),
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
    private function createFakeProducts(bool $over_balance): array
    {
        [$food_category, $merch_category] = $this->createFakeCategories();

        $chips = Product::factory()->create([
            'name' => 'Chips',
            'price' => $over_balance ? 5999.99 : 1.50,
            'pst' => false,
            'category_id' => $food_category->id,
            'stock' => 2,
            'unlimited_stock' => false
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
