<?php

namespace Tests\Feature\Transaction;

use App\Models\GiftCard;
use App\Models\Rotation;
use Cknow\Money\Money;
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

    public function testCreditIsNotMadeForCreditableAmountIfZero(): void
    {
        [$user, $transaction] = $this->createFakeRecords();

        $this->assertCount(0, $user->credits);

        $transactionService = (new TransactionReturnService($transaction))->return();
        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService->getResult());
        $this->assertEquals(Money::parse(0), $transactionService->getTransaction()->gift_card_amount);
        $this->assertCount(0, $user->credits);
    }

    public function testCreditIsMadeForCreditableAmountIfPositive(): void
    {
        $giftCard = GiftCard::factory()->create([
            'original_balance' => $giftCardAmount = Money::parse(1_00),
            'remaining_balance' => $giftCardAmount,
            'issuer_id' => User::factory()->create([
                'role_id' => Role::factory()->create(['name' => 'Admin'])->id,
            ])->id,
        ]);
        [$user, $transaction] = $this->createFakeRecords(gift_card_code: $giftCard->code, create_credit_amount: $creditAmount = Money::parse(4_00));

        $charged_transaction_amount = $transaction->purchaser_amount;
        $balance_before = $user->balance;

        $transactionService = (new TransactionReturnService($transaction))->return();
        $this->assertSame(TransactionReturnService::RESULT_SUCCESS, $transactionService->getResult());
        $user = $user->refresh();
        $this->assertCount(2, $user->credits); // one made by passing create_credit_amount, one made by the return
        $this->assertEquals($giftCardAmount->add($creditAmount), $user->credits->last()->amount);
        $this->assertEquals("Refund for order #{$transaction->id}", $user->credits->last()->reason);
        $this->assertEquals($giftCardAmount, $transactionService->getTransaction()->gift_card_amount);
        $this->assertEquals($transaction->id, $user->credits->last()->transaction_id);
        $this->assertEquals($charged_transaction_amount->add($giftCardAmount)->add($creditAmount), $transaction->total_price);
        $this->assertEquals($balance_before->add($charged_transaction_amount), $user->balance);
        $this->assertEquals($creditAmount, $transaction->credit_amount);
        $this->assertEquals($giftCardAmount->add($creditAmount), $transaction->creditableAmount());
        $this->assertEquals("Test Credit", $user->credits->first()->reason);
        $this->assertEquals($creditAmount, $user->credits->first()->amount_used);
    }

    /**
     * @param int $hat_count
     * @param string|null $gift_card_code
     * @param Money|null $create_credit_amount
     * @return array<User, Transaction, Product>
     */
    private function createFakeRecords(int $hat_count = 2, string $gift_card_code = null, Money $create_credit_amount = null): array
    {
        app(RotationSeeder::class)->run();

        $role = Role::factory()->create();

        /** @var User */
        $user = User::factory()->create([
            'role_id' => $role->id,
            'balance' => 300_00
        ]);

        if ($create_credit_amount) {
            $user->credits()->create([
                'amount' => $create_credit_amount,
                'transaction_id' => Transaction::factory()->create([
                    'purchaser_id' => $user->id,
                    'cashier_id' => $user->id,
                    'purchaser_amount' => $create_credit_amount,
                    'gift_card_amount' => $create_credit_amount,
                    'total_price' => $create_credit_amount,
                    'rotation_id' => Rotation::all()->random()->id,
                ])->id,
                'reason' => 'Test Credit',
            ]);
        }

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

        $transaction = (new TransactionCreationService(new Request([
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
