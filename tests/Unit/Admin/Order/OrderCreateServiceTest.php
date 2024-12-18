<?php

namespace Tests\Unit\Admin\Order;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use Cknow\Money\Money;
use App\Models\Product;
use App\Models\Category;
use App\Models\GiftCard;
use App\Models\Settings;
use App\Models\UserLimit;
use App\Enums\ProductStatus;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Helpers\RotationHelper;
use Database\Seeders\RotationSeeder;
use App\Services\Orders\OrderCreateService;
use Illuminate\Foundation\Testing\RefreshDatabase;

// TODO: testCannotMakeOrderWithNoCurrentRotation
class OrderCreateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotCreateOrderForSelfWithoutPermission(): void
    {
        [, $staff_user] = $this->createFakeRecords();

        $orderService = new OrderCreateService($this->createFakeRequest($staff_user), $staff_user);

        $this->assertSame(OrderCreateService::RESULT_NO_SELF_PURCHASE, $orderService->getResult());
        $this->assertSame('You cannot make purchases for yourself.', $orderService->getMessage());
    }

    public function testCannotMakeOrderWithNoProductsSelected(): void
    {
        [$camper_user] = $this->createFakeRecords();

        $orderService = new OrderCreateService($this->createFakeRequest($camper_user, with_products: false), $camper_user);

        $this->assertSame(OrderCreateService::RESULT_NO_ITEMS_SELECTED, $orderService->getResult());
        $this->assertSame('Please select at least one item.', $orderService->getMessage());
    }

    public function testCannotMakeOrderWithNoVariantSelectedForProductWithVariants(): void
    {
        [$camper_user] = $this->createFakeRecords();

        $orderService = new OrderCreateService($this->createFakeRequest($camper_user, with_no_variant_selected: true), $camper_user);

        $this->assertSame(OrderCreateService::RESULT_MUST_SELECT_VARIANT, $orderService->getResult());
        $this->assertSame('You must select a variant for item Sweater', $orderService->getMessage());
    }

    public function testCannotMakeOrderWithLessThanZeroQuantity(): void
    {
        [$camper_user] = $this->createFakeRecords();

        $orderService = new OrderCreateService($this->createFakeRequest($camper_user, negative_product: true), $camper_user);

        $this->assertSame(OrderCreateService::RESULT_NEGATIVE_QUANTITY, $orderService->getResult());
        $this->assertSame('Quantity must be >= 1 for item Chips', $orderService->getMessage());
    }

    public function testCannotMakeOrderNonActiveItem(): void
    {
        [$camper_user] = $this->createFakeRecords();

        $orderService = new OrderCreateService($this->createFakeRequest($camper_user, draft_product: true), $camper_user);

        $this->assertSame(OrderCreateService::RESULT_PRODUCT_NOT_ACTIVE, $orderService->getResult());
        $this->assertSame('Product Chips is not active.', $orderService->getMessage());
    }

    public function testCannotMakeOrderWithOutOfStockItem(): void
    {
        [$camper_user] = $this->createFakeRecords();

        $orderService = new OrderCreateService($this->createFakeRequest($camper_user, over_stock: true), $camper_user);

        $this->assertSame(OrderCreateService::RESULT_NO_STOCK, $orderService->getResult());
        $this->assertSame('Not enough Chips in stock. Only 2 remaining.', $orderService->getMessage());
    }

    public function testCannotMakeOrderWithOutOfStockProductVariant(): void
    {
        [$camper_user] = $this->createFakeRecords();

        $orderService = new OrderCreateService($this->createFakeRequest($camper_user, with_variant_selected: true, with_no_variant_stock: true), $camper_user);

        $this->assertSame(OrderCreateService::RESULT_NO_STOCK, $orderService->getResult());
        $this->assertSame('Not enough Sweater: Size: Small in stock. Only 0 remaining.', $orderService->getMessage());
    }

    public function testCannotMakeOrderWithoutEnoughBalance(): void
    {
        [$camper_user] = $this->createFakeRecords();

        $orderService = new OrderCreateService($this->createFakeRequest($camper_user, over_balance: true), $camper_user);

        $this->assertSame(OrderCreateService::RESULT_NOT_ENOUGH_BALANCE, $orderService->getResult());
        $this->assertStringContainsString('only has $999.99. Tried to spend $6,335.96.', $orderService->getMessage());
    }

    public function testCannotMakeOrderWithoutEnoughBalanceInCategory(): void
    {
        [$camper_user] = $this->createFakeRecords();

        $orderService = new OrderCreateService($this->createFakeRequest($camper_user, over_category_limit: true), $camper_user);

        $this->assertSame(OrderCreateService::RESULT_NOT_ENOUGH_CATEGORY_BALANCE, $orderService->getResult());
        $this->assertSame('Not enough balance in the Food category. (Limit: $1.00, Remaining: $1.00). Tried to spend $6.05', $orderService->getMessage());
    }

    public function testUserBalanceCorrectAfterOrder(): void
    {
        [$camper_user] = $this->createFakeRecords();

        $balance_before = $camper_user->balance;
        $orderService = new OrderCreateService($this->createFakeRequest($camper_user), $camper_user);

        $this->assertSame(OrderCreateService::RESULT_SUCCESS, $orderService->getResult());
        $this->assertStringContainsString('now has $962.44', $orderService->getMessage());
        $this->assertEquals($balance_before->subtract($orderService->getOrder()->total_price), $camper_user->refresh()->balance);
        $this->assertEquals($orderService->getOrder()->total_price, $camper_user->findSpent());
    }

    public function testSuccessfulOrderIsStored(): void
    {
        [$camper_user, $staff_user] = $this->createFakeRecords();

        $orderService = new OrderCreateService($this->createFakeRequest($camper_user, with_variant_selected: true), $camper_user);

        $this->assertSame(OrderCreateService::RESULT_SUCCESS, $orderService->getResult());
        $this->assertEquals('#1', $orderService->getOrder()->identifier);
        $this->assertStringContainsString('now has $936.19', $orderService->getMessage());
        $this->assertCount(1, Order::all());
        $this->assertCount(1, $camper_user->refresh()->orders);
        $this->assertEquals($camper_user->id, $orderService->getOrder()->purchaser->id);
        $this->assertEquals($staff_user->id, $orderService->getOrder()->cashier->id);
        $this->assertEquals(resolve(RotationHelper::class)->getCurrentRotation()->id, $orderService->getOrder()->rotation->id);
        $this->assertEquals($orderService->getOrder()->total_price, $camper_user->findSpent());
        $this->assertEquals($orderService->getOrder()->total_price, Money::parse(63_80));
        $this->assertEquals($orderService->getOrder()->subtotal, Money::parse(60_49));
        $this->assertEquals($orderService->getOrder()->total_tax, Money::parse(3_31));

        $this->assertEquals('Chips', $orderService->getOrder()->products->first()->product->name);
        $this->assertEquals($orderService->getOrder()->products->first()->order->id, $orderService->getOrder()->id);
        $this->assertEquals(1, Product::firstWhere('name', 'Chips')->stock);
        $this->assertEquals($orderService->getOrder()->products->first()->cost, Product::firstWhere('name', 'Chips')->cost);
        $this->assertEquals(1, $orderService->getOrder()->products->first()->quantity);
        $this->assertEquals(Money::parse(1_50), $orderService->getOrder()->products->first()->price);
        $this->assertEquals(Money::parse(8), $orderService->getOrder()->products->first()->total_tax);
        $this->assertEquals(Money::parse(1_58), $orderService->getOrder()->products->first()->total_price);
        $this->assertEquals(Money::parse(1_50), $orderService->getOrder()->products->first()->subtotal);

        $this->assertEquals('Sweater', $orderService->getOrder()->products->last()->product->name);
        $this->assertEquals(ProductVariant::first()->id, $orderService->getOrder()->products->last()->product_variant_id);
        $this->assertEquals(4, ProductVariant::first()->stock);
        $this->assertEquals($orderService->getOrder()->products->last()->cost, ProductVariant::first()->cost);
        $this->assertEquals(1, $orderService->getOrder()->products->last()->quantity);
        $this->assertEquals(Money::parse(25_00), $orderService->getOrder()->products->last()->price);
        $this->assertEquals(Money::parse(1_25), $orderService->getOrder()->products->last()->total_tax);
        $this->assertEquals(Money::parse(26_25), $orderService->getOrder()->products->last()->total_price);
        $this->assertEquals(Money::parse(25_00), $orderService->getOrder()->products->last()->subtotal);

        $this->assertEquals($orderService->getOrder()->subtotal, Money::parse(0)->add(...$orderService->getOrder()->products->map->subtotal));
        $this->assertEquals($orderService->getOrder()->total_tax, Money::parse(0)->add(...$orderService->getOrder()->products->map->total_tax));
        $this->assertEquals($orderService->getOrder()->total_price, Money::parse(0)->add(...$orderService->getOrder()->products->map->total_price));

        $this->assertEquals(null, $orderService->getOrder()->gift_card_id);
        $this->assertEquals(Money::parse(0), $orderService->getOrder()->gift_card_amount);
    }

    public function testInvalidGiftCardCodeError(): void
    {
        [$camper_user] = $this->createFakeRecords();
        $gift_card_code = 'INVALIDCODE';

        $orderService = new OrderCreateService($this->createFakeRequest($camper_user, gift_card_code: $gift_card_code), $camper_user);

        $this->assertSame(OrderCreateService::RESULT_INVALID_GIFT_CARD, $orderService->getResult());
        $this->assertSame("Gift card with code $gift_card_code does not exist.", $orderService->getMessage());
    }

    public function testGiftCardZeroBalanceError(): void
    {
        [$camper_user] = $this->createFakeRecords();
        $gift_card_code = GiftCard::factory()->create([
            'code' => 'VALIDCODE',
            'remaining_balance' => Money::parse(0),
        ])->code;

        $orderService = new OrderCreateService($this->createFakeRequest($camper_user, gift_card_code: $gift_card_code), $camper_user);

        $this->assertSame(OrderCreateService::RESULT_INVALID_GIFT_CARD_BALANCE, $orderService->getResult());
        $this->assertSame("Gift card with code $gift_card_code has a $0.00 balance.", $orderService->getMessage());
    }

    public function testProperlyCalculatesAmountsWhenGiftCardEqualsTotal(): void
    {
        [$camper_user] = $this->createFakeRecords();
        $gift_card = GiftCard::factory()->create([
            'code' => 'VALIDCODE',
            'remaining_balance' => Money::parse(37_55),
        ]);
        $gift_card_code = $gift_card->code;

        // Creates $37.55 order, all of which is paid by the gift card and none of which is paid by the camper
        $orderService = new OrderCreateService($this->createFakeRequest($camper_user, gift_card_code: $gift_card_code), $camper_user);

        $this->assertSame(OrderCreateService::RESULT_SUCCESS, $orderService->getResult());
        $this->assertEquals(Money::parse(37_55), $orderService->getOrder()->total_price);
        $this->assertEquals(Money::parse(37_55), $orderService->getOrder()->gift_card_amount);
        $this->assertEquals(Money::parse(0_00), $orderService->getOrder()->purchaser_amount);
        $this->assertEquals(Money::parse(0_00), $gift_card->refresh()->remaining_balance);
        $this->assertEquals($gift_card->id, $orderService->getOrder()->gift_card_id);
    }

    public function testProperlyCalculatesAmountsWhenGiftCardExceedsTotal(): void
    {
        [$camper_user] = $this->createFakeRecords();
        $gift_card = GiftCard::factory()->create([
            'code' => 'VALIDCODE',
            'remaining_balance' => Money::parse(100_00),
        ]);
        $gift_card_code = $gift_card->code;

        // Creates $37.55 order, all of which is paid by the gift card and none of which is paid by the camper
        $orderService = new OrderCreateService($this->createFakeRequest($camper_user, gift_card_code: $gift_card_code), $camper_user);

        $this->assertSame(OrderCreateService::RESULT_SUCCESS, $orderService->getResult());
        $this->assertEquals(Money::parse(37_55), $orderService->getOrder()->total_price);
        $this->assertEquals(Money::parse(37_55), $orderService->getOrder()->gift_card_amount);
        $this->assertEquals(Money::parse(0_00), $orderService->getOrder()->purchaser_amount);
        $this->assertEquals(Money::parse(62_45), $gift_card->refresh()->remaining_balance);
        $this->assertEquals($gift_card->id, $orderService->getOrder()->gift_card_id);
    }

    public function testProperlyCalculatesAmountsWhenGiftCardIsLessThanTotal(): void
    {
        [$camper_user] = $this->createFakeRecords();
        $gift_card = GiftCard::factory()->create([
            'code' => 'VALIDCODE',
            'remaining_balance' => Money::parse(10_00),
        ]);
        $gift_card_code = $gift_card->code;

        // Creates $37.55 order, $10.00 of which is paid by the gift card and $27.55 of which is paid by the camper
        $orderService = new OrderCreateService($this->createFakeRequest($camper_user, gift_card_code: $gift_card_code), $camper_user);

        $this->assertSame(OrderCreateService::RESULT_SUCCESS, $orderService->getResult());
        $this->assertEquals(Money::parse(37_55), $orderService->getOrder()->total_price);
        $this->assertEquals(Money::parse(10_00), $orderService->getOrder()->gift_card_amount);
        $this->assertEquals(Money::parse(27_55), $orderService->getOrder()->purchaser_amount);
        $this->assertEquals(Money::parse(0), $gift_card->refresh()->remaining_balance);
        $this->assertEquals($gift_card->id, $orderService->getOrder()->gift_card_id);
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
        bool $draft_product = false,
        bool $over_stock = false,
        bool $over_balance = false,
        bool $over_category_limit = false,
        string $gift_card_code = '',
        bool $with_no_variant_selected = false,
        bool $with_variant_selected = false,
        bool $with_no_variant_stock = false
    ): Request {
        app(RotationSeeder::class)->run();

        [$food_category, $merch_category] = $this->createFakeCategories();

        if ($over_category_limit) {
            $this->createFakeCategoryLimits($purchaser, $food_category, $merch_category);
        }

        [$chips, $hat, $coffee, $sweater] = $this->createFakeProducts($draft_product, $over_balance, $food_category, $merch_category, $with_no_variant_stock);

        $data = [
            'purchaser_id' => $purchaser->id,
        ];
        if ($with_products) {
            $products = [
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
            ];

            if ($with_no_variant_selected) {
                $products[] = [
                    'id' => $sweater->id,
                    'quantity' => 1,
                ];
            } else if ($with_variant_selected) {
                $products[] = [
                    'id' => $sweater->id,
                    'quantity' => 1,
                    'variantId' => $sweater->variants->first()->id,
                ];
            }

            $data['products'] = json_encode($products);

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
    private function createFakeProducts(bool $draft_product, bool $over_balance, Category $food_category, Category $merch_category, bool $with_no_variant_stock): array
    {
        $chips = Product::factory()->create([
            'name' => 'Chips',
            'cost'  => 1_10,
            'price' => $over_balance ? 5999_99 : 1_50,
            'pst' => false,
            'category_id' => $food_category->id,
            'stock' => 2,
            'unlimited_stock' => false,
            'stock_override' => false,
            'status' => $draft_product ? ProductStatus::Draft : ProductStatus::Active,
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

        $sweater = Product::factory()->create([
            'name' => 'Sweater',
            'price' => 25_00,
            'pst' => false,
            'stock_override' => false,
            'unlimited_stock' => false,
            'category_id' => $merch_category->id
        ]);

        $option = $sweater->variantOptions()->create([
            'name' => 'Size',
        ]);

        $value = $option->values()->create([
            'value' => 'Small',
        ]);

        $variant = $sweater->variants()->create([
            'sku' => 'SWEATER_SMALL',
            'price' => 25_00,
            'cost' => 10_00,
            'stock' => $with_no_variant_stock ? 0 : 5,
        ]);

        $variant->optionValueAssignments()->create([
            'product_variant_option_id' => $option->id,
            'product_variant_option_value_id' => $value->id,
        ]);

        return [$chips, $hat, $coffee, $sweater];
    }
}
