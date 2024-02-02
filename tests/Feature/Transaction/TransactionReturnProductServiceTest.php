<?php

namespace Tests\Feature\Transaction;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Settings;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Database\Seeders\RotationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Transactions\TransactionCreateService;
use App\Services\Transactions\TransactionReturnProductService;

class TransactionReturnProductServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testUserBalanceAndTransactionTotalsUpdated(): void
    {
        [$user, $transaction, $hat] = $this->createFakeRecords();
        $balance_before = $user->balance;

        $hatTransactionProduct = $transaction->products->firstWhere('product_id', $hat->id);
        $transactionService = (new TransactionReturnProductService($hatTransactionProduct))->return();
        $this->assertSame(TransactionReturnProductService::RESULT_SUCCESS, $transactionService->getResult());

        $this->assertSame(Transaction::STATUS_PARTIAL_RETURNED, $transaction->getReturnStatus());

        $this->assertEquals(
            $balance_before->add($hat->getPriceAfterTax()),
            $user->refresh()->balance
        );
        $this->assertEquals($hat->getPriceAfterTax(), $user->findReturned());

        $this->assertEquals($hat->getPriceAfterTax(), $transaction->getReturnedTotal());
    }

    public function testProductReturnedValueUpdated(): void
    {
        [, $transaction, $hat] = $this->createFakeRecords();

        $hatTransactionProduct = $transaction->products->firstWhere('product_id', $hat->id);
        $transactionService = (new TransactionReturnProductService($hatTransactionProduct))->return();
        $this->assertSame(TransactionReturnProductService::RESULT_SUCCESS, $transactionService->getResult());

        $hatTransactionProduct = $transaction->products->firstWhere('product_id', $hat->id);
        $this->assertSame(1, $hatTransactionProduct->refresh()->returned);
    }

    public function testCanReturnPartiallyReturnedItemInTransaction(): void
    {
        [$user, $transaction, $hat] = $this->createFakeRecords();

        $hatTransactionProduct = $transaction->products->firstWhere('product_id', $hat->id);
        (new TransactionReturnProductService($hatTransactionProduct))->return();
        $transactionService = (new TransactionReturnProductService($hatTransactionProduct))->return();
        $this->assertSame(TransactionReturnProductService::RESULT_SUCCESS, $transactionService->getResult());

        $this->assertSame(Transaction::STATUS_FULLY_RETURNED, $transaction->getReturnStatus());
        $this->assertTrue($transaction->isReturned());
        $this->assertEquals($transaction->total_price, $user->findReturned());
    }

    public function testCannotReturnFullyReturnedItemInTransaction(): void
    {
        [$user, , $hat] = $this->createFakeRecords();

        $transaction_2_items = $this->createTwoItemTransaction($user, $hat);
        $hatTransactionProduct = $transaction_2_items->products->firstWhere('product_id', $hat->id);
        $transactionService1 = (new TransactionReturnProductService($hatTransactionProduct))->return();
        $this->assertSame(TransactionReturnProductService::RESULT_SUCCESS, $transactionService1->getResult());

        $transactionService2 = (new TransactionReturnProductService($hatTransactionProduct))->return();
        $this->assertSame(TransactionReturnProductService::RESULT_SUCCESS, $transactionService2->getResult());

        $transactionService3 = (new TransactionReturnProductService($hatTransactionProduct))->return();
        $this->assertSame(TransactionReturnProductService::RESULT_ITEM_RETURNED_MAX_TIMES, $transactionService3->getResult());

        $this->assertSame(Transaction::STATUS_PARTIAL_RETURNED, $transaction_2_items->getReturnStatus());
        $this->assertEquals($hat->getPriceAfterTax()->multiply(2), $user->findReturned());
    }

    public function testProductStockIsNotRestoredIfSettingDisabled(): void
    {
        [, $transaction, $hat] = $this->createFakeRecords(5);

        $hat->update([
            'restore_stock_on_return' => false,
            'stock' => $start_stock = 12,
        ]);

        $hatTransactionProduct = $transaction->products->firstWhere('product_id', $hat->id);
        (new TransactionReturnProductService($hatTransactionProduct))->return();

        $this->assertSame($start_stock, $hat->refresh()->stock);
    }

    public function testProductStockIsRestoredIfSettingEnabled(): void
    {
        [, $transaction, $hat] = $this->createFakeRecords(5);

        $hat->update([
            'restore_stock_on_return' => true,
            'stock' => $start_stock = 12,
        ]);

        $hatTransactionProduct = $transaction->products->firstWhere('product_id', $hat->id);
        (new TransactionReturnProductService($hatTransactionProduct))->return();

        $this->assertEquals($start_stock + 1, $hat->refresh()->stock);
    }

    /**
     * @param int $hat_count
     *
     * @return array<User, Transaction, Product>
     */
    private function createFakeRecords(int $hat_count = 2, ?string $gift_card_code = null): array
    {
        app(RotationSeeder::class)->run();

        $role = Role::factory()->create();

        /** @var User */
        $user = User::factory()->create([
            'role_id' => $role->id,
            'balance' => 300_00
        ]);

        $this->actingAs($user);

        $merch_category = Category::factory()->create([
            'name' => 'Merch'
        ]);

        $hat = Product::factory()->create([
            'name' => 'Hat',
            'price' => 11_99, // $13.43
            'category_id' => $merch_category->id,
            'pst' => true
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

        $transaction = (new TransactionCreateService(new Request([
            'products' => json_encode([
                [
                    'id' => $hat->id,
                    'quantity' => $hat_count,
                ],
            ]),
            'gift_card_code' => $gift_card_code,
            'purchaser_id' => $user->id
        ]), $user))->getTransaction(); // $26.8576 -> $3.1424

        return [$user, $transaction, $hat];
    }

    private function createTwoItemTransaction(User $user, Product $hat): Transaction
    {
        $sweater = Product::factory()->create([
            'name' => 'Sweater',
            'category_id' => $hat->category_id,
            'price' => 39_99
        ]);

        return (new TransactionCreateService(new Request([
            'products' => json_encode([
                [
                    'id' => $hat->id,
                    'quantity' => 2,
                ],
                [
                    'id' => $sweater->id,
                    'quantity' => 1
                ]
            ]),
            'purchaser_id' => $user->id
        ]), $user))->getTransaction();
    }
}
