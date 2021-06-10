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
use App\Services\TransactionReturnService;
use App\Services\TransactionCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionReturnTest extends TestCase
{
    use RefreshDatabase;

    public function testCanReturnTransaction()
    {
        [$user, $transaction, $hat] = $this->createFakeRecords();

        $transactionService = (new TransactionReturnService($transaction))->return();

        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService->getResult());
        $this->assertSame(Transaction::STATUS_FULLY_RETURNED, $transactionService->getTransaction()->getReturnStatus());
        $this->assertTrue($transactionService->getTransaction()->isReturned());
    }

    public function testUserBalanceUpdatedAfterItemReturn()
    {
        [$user, $transaction, $hat] = $this->createFakeRecords();

        $transactionService = (new TransactionReturnService($transaction))->returnItem($hat->id);

        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService->getResult());
        $this->assertSame(Transaction::STATUS_PARTIAL_RETURNED, $transaction->getReturnStatus());
        $this->assertEquals(number_format($user->balance + $hat->getPrice(), 2), number_format($user->refresh()->balance, 2)); // TODO: not need to use number format to round (3 and 4 decimal places are off)
    }

    public function testUserBalanceUpdatedAfterTransactionReturn()
    {
        [$user, $transaction, $hat] = $this->createFakeRecords();

        $transactionService = (new TransactionReturnService($transaction))->return();

        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService->getResult());
        $this->assertSame(Transaction::STATUS_FULLY_RETURNED, $transaction->getReturnStatus());
        $this->assertEquals($user->balance + $transaction->total_price, $user->refresh()->balance);
    }

    public function testCanReturnPartiallyReturnedItemInTransaction()
    {
        [$user, $transaction, $hat] = $this->createFakeRecords();

        (new TransactionReturnService($transaction))->returnItem($hat->id);
        $transactionService = (new TransactionReturnService($transaction))->returnItem($hat->id);

        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService->getResult());
        $this->assertSame(Transaction::STATUS_FULLY_RETURNED, $transaction->getReturnStatus());
    }

    public function testCannotReturnFullyReturnedTransaction()
    {
        [$user, $transaction, $hat] = $this->createFakeRecords();

        $transactionService1 = (new TransactionReturnService($transaction))->return();
        $transactionService2 = (new TransactionReturnService($transaction))->return();

        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService1->getResult());
        $this->assertSame(TransactionReturnService::RESULT_ALREADY_RETURNED, $transactionService2->getResult());
        $this->assertSame(Transaction::STATUS_FULLY_RETURNED, $transaction->getReturnStatus());
    }

    public function testCannotReturnFullyReturnedItemInTransaction()
    {
        [$user, $transaction, $hat] = $this->createFakeRecords();

        $transactionService1 = (new TransactionReturnService($transaction))->returnItem($hat->id);
        $transactionService2 = (new TransactionReturnService($transaction))->returnItem($hat->id);
        $transactionService3 = (new TransactionReturnService($transaction))->returnItem($hat->id);

        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService1->getResult());
        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService2->getResult());
        // TODO: make new testing transaction with another product type
        //$this->assertSame(TransactionReturnService::RESULT_ITEM_RETURNED_MAX_TIMES, $transactionService3->getResult());
        //$this->assertSame(Transaction::STATUS_PARTIAL_RETURNED, $transactionService3->getTransaction()->getReturnStatus());
    }

    private function createFakeRecords(): array
    {
        $role = Role::factory()->create();

        $user = User::factory()->create([
            'role_id' => $role->id,
            'balance' => 30.00
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
                'editor_id' => $user->id
            ],
            [
                'setting' => 'pst',
                'value' => '1.07',
                'editor_id' => $user->id
            ]
        ]);

        $transaction = (new TransactionCreationService($this->createFakeRequest($user, $hat)))->getTransaction(); // $26.8576 -> $3.1424

        return [$user->refresh(), $transaction, $hat];
    }

    private function createFakeRequest(User $user, Product $hat): Request
    {
        return new Request([
            'product' => [
                $hat->id,
            ],
            'quantity' => [
                $hat->id => 2,
            ],
            'purchaser_id' => $user->id
        ]);
    }
}
