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
use App\Services\Transactions\TransactionCreationService;

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

    public function testUserBalanceAndTransactionTotalsUpdatedAfterItemReturn(): void
    {
        [$user, $transaction, $hat] = $this->createFakeRecords();

        $transactionService = (new TransactionReturnService($transaction))->returnItem($hat);
        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService->getResult());

        $this->assertSame(Transaction::STATUS_PARTIAL_RETURNED, $transaction->getReturnStatus());
        // TODO: not need to use number format to round (3 and 4 decimal places are off)
        $this->assertEquals(
            number_format($user->balance + $hat->getPrice(), 2),
            number_format($user->refresh()->balance, 2)
        );
        $this->assertEquals($hat->getPrice(), number_format($user->findReturned(), 2));

        $this->assertEquals($hat->getPrice(), number_format($transaction->getReturnedTotal(), 2));
    }

    public function testUserBalanceUpdatedAfterTransactionReturn(): void
    {
        [$user, $transaction] = $this->createFakeRecords();

        $transactionService = (new TransactionReturnService($transaction))->return();
        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService->getResult());

        $this->assertSame(Transaction::STATUS_FULLY_RETURNED, $transaction->getReturnStatus());
        $this->assertTrue($transaction->isReturned());
        $this->assertEquals(
            $user->balance + $transaction->total_price,
            $user->refresh()->balance
        );
        $this->assertEquals($transaction->total_price, $user->findReturned());
    }

    public function testCanReturnPartiallyReturnedItemInTransaction(): void
    {
        [$user, $transaction, $hat] = $this->createFakeRecords();

        (new TransactionReturnService($transaction))->returnItem($hat);
        $transactionService = (new TransactionReturnService($transaction))->returnItem($hat);
        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService->getResult());

        $this->assertSame(Transaction::STATUS_FULLY_RETURNED, $transaction->getReturnStatus());
        $this->assertTrue($transaction->isReturned());
        $this->assertEquals($transaction->total_price, $user->findReturned());
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

    public function testCannotReturnFullyReturnedItemInTransaction(): void
    {
        [$user, , $hat] = $this->createFakeRecords();
        $transaction_2_items = $this->createTwoItemTransaction($user, $hat);

        $transactionService1 = (new TransactionReturnService($transaction_2_items))->returnItem($hat);
        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService1->getResult());

        $transactionService2 = (new TransactionReturnService($transaction_2_items))->returnItem($hat);
        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService2->getResult());

        $transactionService3 = (new TransactionReturnService($transaction_2_items))->returnItem($hat);
        $this->assertSame(TransactionReturnService::RESULT_ITEM_RETURNED_MAX_TIMES, $transactionService3->getResult());

        $this->assertSame(Transaction::STATUS_PARTIAL_RETURNED, $transaction_2_items->getReturnStatus());
        $this->assertEquals($hat->getPrice() * 2, number_format($user->findReturned(), 2));
    }

    private function createFakeRecords(): array
    {
        app(RotationSeeder::class)->run();

        $role = Role::factory()->create();

        /** @var User */
        $user = User::factory()->create([
            'role_id' => $role->id,
            'balance' => 300.00
        ]);

        $this->actingAs($user);

        $merch_category = Category::factory()->create([
            'name' => 'Merch'
        ]);

        $hat = Product::factory()->create([
            'name' => 'Hat',
            'price' => 11.99, // $13.43
            'category_id' => $merch_category->id,
            'pst' => true
        ]);

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

        $transaction = (new TransactionCreationService(new Request(
            [
                'products' => json_encode([
                    [
                        'id' => $hat->id,
                        'quantity' => 2,
                    ],
                ]),
                'purchaser_id' => $user->id
            ]
        ), $user))->getTransaction(); // $26.8576 -> $3.1424

        return [$user->refresh(), $transaction, $hat];
    }

    private function createTwoItemTransaction(User $user, Product $hat): Transaction
    {
        $sweater = Product::factory()->create([
            'name' => 'Sweater',
            'category_id' => $hat->category_id,
            'price' => 39.99
        ]);

        return (new TransactionCreationService(new Request([
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