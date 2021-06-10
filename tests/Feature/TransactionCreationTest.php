<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Settings;
use App\Models\UserLimits;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\TransactionCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionCreationTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotCreateOrderForSelfWithoutPermission()
    {
        [$camper_user, $staff_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreationService($this->createFakeRequest($staff_user));

        $this->assertSame(TransactionCreationService::RESULT_NO_SELF_PURCHASE, $transactionService->getResult());
    }

    public function testCannotMakeTransactionWithNoProductsSelected()
    {
        [$camper_user, $staff_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreationService($this->createFakeRequest($camper_user, with_products: false));

        $this->assertSame(TransactionCreationService::RESULT_NO_ITEMS_SELECTED, $transactionService->getResult());
    }

    public function testCannotMakeTransactionWithLessThanZeroQuantity()
    {
        [$camper_user, $staff_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreationService($this->createFakeRequest($camper_user, negative_product: true));

        $this->assertSame(TransactionCreationService::RESULT_NEGATIVE_QUANTITY, $transactionService->getResult());
    }

    public function testCannotMakeTransactionWithOutOfStockItem()
    {
        [$camper_user, $staff_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreationService($this->createFakeRequest($camper_user, over_stock: true));

        $this->assertSame(TransactionCreationService::RESULT_NO_STOCK, $transactionService->getResult());
    }

    public function testCannotMakeTransactionWithoutEnoughBalance()
    {
        [$camper_user, $staff_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreationService($this->createFakeRequest($camper_user, over_balance: true));

        $this->assertSame(TransactionCreationService::RESULT_NOT_ENOUGH_BALANCE, $transactionService->getResult());
    }

    public function testCannotMakeTransactionWithoutEnoughBalanceInCategory()
    {
        [$camper_user, $staff_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreationService($this->createFakeRequest($camper_user, over_category_limit: true));

        $this->assertSame(TransactionCreationService::RESULT_NOT_ENOUGH_CATEGORY_BALANCE, $transactionService->getResult());
    }

    public function testUserBalanceCorrectAfterTransaction()
    {
        [$camper_user, $staff_user] = $this->createFakeRecords();

        $camper_user_balance_before = $camper_user->balance;

        $transactionService = new TransactionCreationService($this->createFakeRequest($camper_user));

        $this->assertSame(TransactionCreationService::RESULT_SUCCESS, $transactionService->getResult());
        $this->assertEquals($camper_user_balance_before - $transactionService->getTotalPrice(), $camper_user->refresh()->balance);
    }

    public function testSuccessfulTransactionIsStored()
    {
        [$camper_user, $staff_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreationService($this->createFakeRequest($camper_user));

        $this->assertSame(TransactionCreationService::RESULT_SUCCESS, $transactionService->getResult());
        $this->assertCount(1, Transaction::all());
        // $this->assertCount(1, $camper_user->getTransactions()); TODO: raw DB select works, but this does not.
    }

    /** @return User[] */
    private function createFakeRecords(): array
    {
        $camper_role = Role::factory()->create([
            'name' => 'Camper',
            'superuser' => false,
            'order' => 2,
            'staff' => false
        ]);

        $camper_user = User::factory()->create([
            'role_id' => $camper_role->id,
            'balance' => 999.99
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

    private function createFakeRequest(
        User $purchaser,
        bool $with_products = true,
        bool $negative_product = false,
        bool $over_stock = false,
        bool $over_balance = false,
        bool $over_category_limit = false
    ): Request {
        [$food_category, $merch_category] = $this->createFakeCategories();

        if ($over_category_limit) {
            $this->createFakeCategoryLimits($purchaser, $food_category, $merch_category);
        }

        [$chips, $hat, $coffee] = $this->createFakeProducts($over_balance, $food_category, $merch_category);

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
                'purchaser_id' => $purchaser->id
            ];
        } else {
            $data = [
                'quantity' => [
                    $chips->id => $negative_product ? -1 : ($over_stock ? 100 : 1),
                    $hat->id => 2,
                    $coffee->id => 1
                ],
                'purchaser_id' => $purchaser->id
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

    private function createFakeCategoryLimits(User $user, Category $food_category, Category $merch_category)
    {
        UserLimits::factory()->create([
            'user_id' => $user->id,
            'category_id' => $food_category->id,
            'limit_per' => 1,
        ]);

        UserLimits::factory()->create([
            'user_id' => $user->id,
            'category_id' => $merch_category->id,
            'limit_per' => 1,
        ]);
    }

    /** @return Product[] */
    private function createFakeProducts(bool $over_balance, Category $food_category, Category $merch_category): array
    {
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
