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
use App\Services\Transactions\TransactionReturnService;
use App\Services\Transactions\TransactionCreateService;

class TransactionReturnServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testCanReturnTransaction(): void
    {
        [, $transaction] = $this->createFakeRecords();

        $transactionService = (new TransactionReturnService($transaction))->return();
        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService->getResult());

        $this->assertSame(Transaction::STATUS_FULLY_RETURNED, $transaction->getReturnStatus());
        $this->assertTrue($transaction->isReturned());
    }

    public function testUserBalanceUpdated(): void
    {
        [$user, $transaction] = $this->createFakeRecords();
        $balance_before = $user->balance;

        $transactionService = (new TransactionReturnService($transaction))->return();
        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService->getResult());

        $this->assertSame(Transaction::STATUS_FULLY_RETURNED, $transaction->getReturnStatus());
        $this->assertTrue($transaction->isReturned());
        $this->assertEquals(
            $balance_before->add($transaction->total_price),
            $user->refresh()->balance
        );
        $this->assertEquals($transaction->total_price, $user->findReturned());
    }

    public function testProductReturnedValueUpdated(): void
    {
        [, $transaction, $hat] = $this->createFakeRecords();

        $transactionService = (new TransactionReturnService($transaction))->return();
        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService->getResult());

        $hatTransactionProduct = $transaction->products->firstWhere('product_id', $hat->id);
        $this->assertSame(2, $hatTransactionProduct->refresh()->returned);
    }

    public function testCannotReturnFullyReturnedTransaction(): void
    {
        [$user, $transaction] = $this->createFakeRecords();

        $transactionService1 = (new TransactionReturnService($transaction))->return();
        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService1->getResult());

        $transactionService2 = (new TransactionReturnService($transaction))->return();
        $this->assertSame(TransactionReturnService::RESULT_ALREADY_RETURNED, $transactionService2->getResult());

        $this->assertSame(Transaction::STATUS_FULLY_RETURNED, $transaction->getReturnStatus());
        $this->assertEquals($transaction->total_price, $user->findReturned());
    }

    public function testProductStockIsNotRestoredIfSettingDisabled(): void
    {
        [, $transaction, $hat] = $this->createFakeRecords(5);

        $hat->update([
            'restore_stock_on_return' => false,
            'stock' => $start_stock = 12,
        ]);

        (new TransactionReturnService($transaction))->return();

        $this->assertSame($start_stock, $hat->refresh()->stock);
    }

    public function testProductStockIsRestoredIfSettingEnabled(): void
    {
        [, $transaction, $hat] = $this->createFakeRecords($hat_count = 5);

        $hat->update([
            'restore_stock_on_return' => true,
            'stock' => $start_stock = 12,
        ]);

        (new TransactionReturnService($transaction))->return();

        $this->assertSame($start_stock + $hat_count, $hat->refresh()->stock);
    }

    /**
     * @param int $hat_count
     * @param string|null $gift_card_code
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
