<?php

namespace Tests\Unit\Transaction;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Cknow\Money\Money;
use App\Models\Product;
use App\Models\Category;
use App\Models\GiftCard;
use App\Models\Settings;
use App\Models\UserLimit;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\RotationHelper;
use Database\Seeders\RotationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Transactions\TransactionCreateService;

// TODO: testCannotMakeTransactionWithNoCurrentRotation
class TransactionCreateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotCreateOrderForSelfWithoutPermission(): void
    {
        [, $staff_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreateService($this->createFakeRequest($staff_user), $staff_user);

        $this->assertSame(TransactionCreateService::RESULT_NO_SELF_PURCHASE, $transactionService->getResult());
        $this->assertSame('You cannot make purchases for yourself.', $transactionService->getMessage());
    }

    public function testCannotMakeTransactionWithNoProductsSelected(): void
    {
        [$camper_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreateService($this->createFakeRequest($camper_user, with_products: false), $camper_user);

        $this->assertSame(TransactionCreateService::RESULT_NO_ITEMS_SELECTED, $transactionService->getResult());
        $this->assertSame('Please select at least one item.', $transactionService->getMessage());
    }

    public function testCannotMakeTransactionWithLessThanZeroQuantity(): void
    {
        [$camper_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreateService($this->createFakeRequest($camper_user, negative_product: true), $camper_user);

        $this->assertSame(TransactionCreateService::RESULT_NEGATIVE_QUANTITY, $transactionService->getResult());
        $this->assertSame('Quantity must be >= 1 for item Chips', $transactionService->getMessage());
    }

    public function testCannotMakeTransactionWithOutOfStockItem(): void
    {
        [$camper_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreateService($this->createFakeRequest($camper_user, over_stock: true), $camper_user);

        $this->assertSame(TransactionCreateService::RESULT_NO_STOCK, $transactionService->getResult());
        $this->assertSame('Not enough Chips in stock. Only 2 remaining.', $transactionService->getMessage());
    }

    public function testCannotMakeTransactionWithoutEnoughBalance(): void
    {
        [$camper_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreateService($this->createFakeRequest($camper_user, over_balance: true), $camper_user);

        $this->assertSame(TransactionCreateService::RESULT_NOT_ENOUGH_BALANCE, $transactionService->getResult());
        $this->assertStringContainsString('only has $999.99. Tried to spend $6,335.96.', $transactionService->getMessage());
    }

    public function testCannotMakeTransactionWithoutEnoughBalanceInCategory(): void
    {
        [$camper_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreateService($this->createFakeRequest($camper_user, over_category_limit: true), $camper_user);

        $this->assertSame(TransactionCreateService::RESULT_NOT_ENOUGH_CATEGORY_BALANCE, $transactionService->getResult());
        $this->assertSame('Not enough balance in the Food category. (Limit: $1.00, Remaining: $1.00). Tried to spend $6.05', $transactionService->getMessage());
    }

    public function testUserBalanceCorrectAfterTransaction(): void
    {
        [$camper_user] = $this->createFakeRecords();

        $balance_before = $camper_user->balance;
        $transactionService = new TransactionCreateService($this->createFakeRequest($camper_user), $camper_user);

        $this->assertSame(TransactionCreateService::RESULT_SUCCESS, $transactionService->getResult());
        $this->assertStringContainsString('now has $962.44', $transactionService->getMessage());
        $this->assertEquals($balance_before->subtract($transactionService->getTransaction()->total_price), $camper_user->refresh()->balance);
        $this->assertEquals($transactionService->getTransaction()->total_price, $camper_user->findSpent());
    }

    public function testSuccessfulTransactionIsStored(): void
    {
        [$camper_user, $staff_user] = $this->createFakeRecords();

        $transactionService = new TransactionCreateService($this->createFakeRequest($camper_user), $camper_user);

        $this->assertSame(TransactionCreateService::RESULT_SUCCESS, $transactionService->getResult());
        $this->assertStringContainsString('now has $962.44', $transactionService->getMessage());
        $this->assertCount(1, Transaction::all());
        $this->assertCount(1, $camper_user->refresh()->transactions);
        $this->assertEquals($camper_user->id, $transactionService->getTransaction()->purchaser->id);
        $this->assertEquals($staff_user->id, $transactionService->getTransaction()->cashier->id);
        $this->assertEquals(resolve(RotationHelper::class)->getCurrentRotation()->id, $transactionService->getTransaction()->rotation->id);
        $this->assertEquals($transactionService->getTransaction()->total_price, $camper_user->findSpent());
        $this->assertEquals('Chips', $transactionService->getTransaction()->products->first()->product->name);
        $this->assertEquals($transactionService->getTransaction()->products->first()->transaction->id, $transactionService->getTransaction()->id);
        $this->assertEquals(1, Product::firstWhere('name', 'Chips')->stock);
        $this->assertEquals(null, $transactionService->getTransaction()->gift_card_id);
        $this->assertEquals(Money::parse(0), $transactionService->getTransaction()->gift_card_amount);
    }

    public function testInvalidGiftCardCodeError(): void
    {
        [$camper_user] = $this->createFakeRecords();
        $gift_card_code = 'INVALIDCODE';

        $transactionService = new TransactionCreateService($this->createFakeRequest($camper_user, gift_card_code: $gift_card_code), $camper_user);

        $this->assertSame(TransactionCreateService::RESULT_INVALID_GIFT_CARD, $transactionService->getResult());
        $this->assertSame("Gift card with code $gift_card_code does not exist.", $transactionService->getMessage());
    }

    public function testGiftCardZeroBalanceError(): void
    {
        [$camper_user] = $this->createFakeRecords();
        $gift_card_code = GiftCard::factory()->create([
            'code' => 'VALIDCODE',
            'remaining_balance' => Money::parse(0),
        ])->code;

        $transactionService = new TransactionCreateService($this->createFakeRequest($camper_user, gift_card_code: $gift_card_code), $camper_user);

        $this->assertSame(TransactionCreateService::RESULT_INVALID_GIFT_CARD_BALANCE, $transactionService->getResult());
        $this->assertSame("Gift card with code $gift_card_code has a $0.00 balance.", $transactionService->getMessage());
    }

    public function testProperlyCalculatesAmountsWhenGiftCardEqualsTotal(): void
    {
        [$camper_user] = $this->createFakeRecords();
        $gift_card = GiftCard::factory()->create([
            'code' => 'VALIDCODE',
            'remaining_balance' => Money::parse(37_55),
        ]);
        $gift_card_code = $gift_card->code;

        // Creates $37.55 transaction, all of which is paid by the gift card and none of which is paid by the camper
        $transactionService = new TransactionCreateService($this->createFakeRequest($camper_user, gift_card_code: $gift_card_code), $camper_user);

        $this->assertSame(TransactionCreateService::RESULT_SUCCESS, $transactionService->getResult());
        $this->assertEquals(Money::parse(37_55), $transactionService->getTransaction()->total_price);
        $this->assertEquals(Money::parse(37_55), $transactionService->getTransaction()->gift_card_amount);
        $this->assertEquals(Money::parse(0_00), $transactionService->getTransaction()->purchaser_amount);
        $this->assertEquals(Money::parse(0_00), $gift_card->refresh()->remaining_balance);
        $this->assertEquals($gift_card->id, $transactionService->getTransaction()->gift_card_id);
    }

    public function testProperlyCalculatesAmountsWhenGiftCardExceedsTotal(): void
    {
        [$camper_user] = $this->createFakeRecords();
        $gift_card = GiftCard::factory()->create([
            'code' => 'VALIDCODE',
            'remaining_balance' => Money::parse(100_00),
        ]);
        $gift_card_code = $gift_card->code;

        // Creates $37.55 transaction, all of which is paid by the gift card and none of which is paid by the camper
        $transactionService = new TransactionCreateService($this->createFakeRequest($camper_user, gift_card_code: $gift_card_code), $camper_user);

        $this->assertSame(TransactionCreateService::RESULT_SUCCESS, $transactionService->getResult());
        $this->assertEquals(Money::parse(37_55), $transactionService->getTransaction()->total_price);
        $this->assertEquals(Money::parse(37_55), $transactionService->getTransaction()->gift_card_amount);
        $this->assertEquals(Money::parse(0_00), $transactionService->getTransaction()->purchaser_amount);
        $this->assertEquals(Money::parse(62_45), $gift_card->refresh()->remaining_balance);
        $this->assertEquals($gift_card->id, $transactionService->getTransaction()->gift_card_id);
    }

    public function testProperlyCalculatesAmountsWhenGiftCardIsLessThanTotal(): void
    {
        [$camper_user] = $this->createFakeRecords();
        $gift_card = GiftCard::factory()->create([
            'code' => 'VALIDCODE',
            'remaining_balance' => Money::parse(10_00),
        ]);
        $gift_card_code = $gift_card->code;

        // Creates $37.55 transaction, $10.00 of which is paid by the gift card and $27.55 of which is paid by the camper
        $transactionService = new TransactionCreateService($this->createFakeRequest($camper_user, gift_card_code: $gift_card_code), $camper_user);

        $this->assertSame(TransactionCreateService::RESULT_SUCCESS, $transactionService->getResult());
        $this->assertEquals(Money::parse(37_55), $transactionService->getTransaction()->total_price);
        $this->assertEquals(Money::parse(10_00), $transactionService->getTransaction()->gift_card_amount);
        $this->assertEquals(Money::parse(27_55), $transactionService->getTransaction()->purchaser_amount);
        $this->assertEquals(Money::parse(0), $gift_card->refresh()->remaining_balance);
        $this->assertEquals($gift_card->id, $transactionService->getTransaction()->gift_card_id);
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

        /** @var User */
        $staff_user = User::factory()->create([
            'role_id' => $staff_role->id
        ]);

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

        $this->actingAs($staff_user);

        return [$camper_user, $staff_user];
    }

    private function createFakeRequest(
        User $purchaser,
        bool $with_products = true,
        bool $negative_product = false,
        bool $over_stock = false,
        bool $over_balance = false,
        bool $over_category_limit = false,
        string $gift_card_code = '',
    ): Request {
        app(RotationSeeder::class)->run();

        [$food_category, $merch_category] = $this->createFakeCategories();

        if ($over_category_limit) {
            $this->createFakeCategoryLimits($purchaser, $food_category, $merch_category);
        }

        [$chips, $hat, $coffee] = $this->createFakeProducts($over_balance, $food_category, $merch_category);

        if ($with_products) {
            $data = [
                'products' => json_encode([
                    [
                        'id' => $chips->id,
                        'quantity' => $negative_product ? -1 : ($over_stock ? 100 : 1),
                    ],
                    [
                        'id' => $hat->id,
                        'quantity' => 2,
                    ],
                    [
                        'id' => $coffee->id,
                        'quantity' => 1,
                    ],
                ]),
                'purchaser_id' => $purchaser->id,
            ];

            if ($gift_card_code) {
                $data['gift_card_code'] = $gift_card_code;
            }
        } else {
            $data = [
                'products' => '{}',
                'purchaser_id' => $purchaser->id
            ];
        }

        return new Request($data);
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

    private function createFakeCategoryLimits(User $user, Category $food_category, Category $merch_category): void
    {
        UserLimit::factory()->create([
            'user_id' => $user->id,
            'category_id' => $food_category->id,
            'limit' => 1_00,
        ]);

        UserLimit::factory()->create([
            'user_id' => $user->id,
            'category_id' => $merch_category->id,
            'limit' => 1_00,
        ]);
    }

    /** @return Product[] */
    private function createFakeProducts(bool $over_balance, Category $food_category, Category $merch_category): array
    {
        $chips = Product::factory()->create([
            'name' => 'Chips',
            'price' => $over_balance ? 5999_99 : 1_50,
            'pst' => false,
            'category_id' => $food_category->id,
            'stock' => 2,
            'unlimited_stock' => false,
            'stock_override' => false
        ]);

        $hat = Product::factory()->create([
            'name' => 'Hat',
            'price' => 15_00,
            'pst' => false,
            'category_id' => $merch_category->id
        ]);

        $coffee = Product::factory()->create([
            'name' => 'Coffee',
            'price' => 3_99,
            'pst' => true,
            'category_id' => $food_category->id
        ]);

        return [$chips, $hat, $coffee];
    }
}
