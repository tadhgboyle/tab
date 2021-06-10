<?php

namespace Tests\Feature;

use App\Models\Settings;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionCreationService;
use App\Services\TransactionReturnService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class TransactionReturnTest extends TestCase
{
    use RefreshDatabase;

    public function testCanReturnTransaction()
    {
        [$user, $transaction, $hat] = $this->createFakeRecords();

        $transactionService = (new TransactionReturnService($transaction->id))->return();

        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService->getResult());
        $this->assertSame(Transaction::STATUS_FULLY_RETURNED, $transactionService->getTransaction()->getReturnStatus());
        $this->assertTrue($transactionService->getTransaction()->isReturned());
    }

    public function testUserBalanceUpdatedAfterItemReturn()
    {
        [$user, $transaction, $hat] = $this->createFakeRecords();

        $user_balance_before = $user->balance;
        print('user bal ' . $user_balance_before . "\r\n");
        print('hat price ' . $hat->getPrice() . "\r\n");

        $transactionService = (new TransactionReturnService($transaction->id))->returnItem($hat->id);

        print('expected bal ' . $user_balance_before + $hat->getPrice() . "\r\n");

        $user->refresh();

        print('actual bal ' . $user->balance);

        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService->getResult());
        $this->assertSame(Transaction::STATUS_PARTIAL_RETURNED, $transactionService->getTransaction()->getReturnStatus());
        $this->assertEquals($user_balance_before + $hat->getPrice(), $user->balance); // TODO: their balance is getting set to the price of the hat...
    }

    public function testUserBalanceUpdatedAfterTransactionReturn()
    {
        [$user, $transaction, $hat] = $this->createFakeRecords();
        
        $transactionService = (new TransactionReturnService($transaction->id))->return();

        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService->getResult());
        $this->assertSame(Transaction::STATUS_FULLY_RETURNED, $transactionService->getTransaction()->getReturnStatus());
        $this->assertEquals($user->balance + $transaction->total_price, $user->refresh()->balance);
    }

    public function testCanReturnPartiallyReturnedItemInTransaction()
    {
        [$user, $transaction, $hat] = $this->createFakeRecords();

        (new TransactionReturnService($transaction->id))->returnItem($hat->id);
        $transactionService = (new TransactionReturnService($transaction->id))->returnItem($hat->id);

        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService->getResult());
        $this->assertSame(Transaction::STATUS_PARTIAL_RETURNED, $transactionService->getTransaction()->getReturnStatus());
    }

    public function testCannotReturnFullyReturnedTransaction()
    {
        [$user, $transaction, $hat] = $this->createFakeRecords();

        $transactionService1 = (new TransactionReturnService($transaction->id))->return();
        $transactionService2 = (new TransactionReturnService($transaction->id))->return();

        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService1->getResult());
        $this->assertSame(TransactionReturnService::RESULT_ALREADY_RETURNED, $transactionService2->getResult()); // TODO: fix
        $this->assertSame(Transaction::STATUS_FULLY_RETURNED, $transactionService2->getTransaction()->getReturnStatus());
    }

    public function testCannotReturnFullyReturnedItemInTransaction()
    {
        [$user, $transaction, $hat] = $this->createFakeRecords();

        $transactionService1 = (new TransactionReturnService($transaction->id))->returnItem($hat->id);
        $transactionService2 = (new TransactionReturnService($transaction->id))->returnItem($hat->id);
        $transactionService3 = (new TransactionReturnService($transaction->id))->returnItem($hat->id);

        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService1->getResult());
        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService2->getResult());
        $this->assertSame(TransactionReturnService::RESULT_ITEM_RETURNED_MAX_TIMES, $transactionService3->getResult()); // TODO: fix
        $this->assertSame(Transaction::STATUS_FULLY_RETURNED, $transactionService3->getTransaction()->getReturnStatus());
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
