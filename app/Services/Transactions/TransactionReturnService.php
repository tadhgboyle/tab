<?php

namespace App\Services\Transactions;

use App\Services\Service;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use Illuminate\Http\RedirectResponse;

// todo test credit reasons
class TransactionReturnService extends Service
{
    use TransactionService;

    public const RESULT_ALREADY_RETURNED = 'ALREADY_RETURNED';
    public const RESULT_SUCCESS = 'SUCCESS';

    public function __construct(Transaction $transaction)
    {
        $this->_transaction = $transaction;
    }

    public function return(): TransactionReturnService
    {
        // This should never happen, but a good security measure
        if ($this->_transaction->isReturned()) {
            $this->_result = self::RESULT_ALREADY_RETURNED;
            $this->_message = 'That order has already been fully returned.';
            return $this;
        }

        $this->updateTransactionProductAttributes();
        $this->refundPurchaser();

        $this->_transaction->update(['returned' => true]);

        $this->_result = self::RESULT_SUCCESS;
        // todo update message to reflect if a credit was made
        $this->_message = 'Successfully returned order #' . $this->_transaction->id . ' for ' . $this->_transaction->purchaser->full_name;
        return $this;
    }

    private function updateTransactionProductAttributes(): void
    {
        $this->_transaction->products->each(function (TransactionProduct $transactionProduct) {
            $returned = $transactionProduct->returned;
            $transactionProduct->update(['returned' => $transactionProduct->quantity]);
            if ($transactionProduct->product->restore_stock_on_return) {
                $transactionProduct->product->adjustStock(
                    $transactionProduct->quantity - $returned
                );
            }
        });
    }

    private function refundPurchaser(): void
    {
        // todo what happens when returning an already partially returned order?
        $purchaser = $this->_transaction->purchaser;
        $creditable_amount = $this->_transaction->creditableAmount();

        // Issue credit if there is a creditable amount
        if ($creditable_amount->isPositive()) {
            $purchaser->credits()->create([
                'transaction_id' => $this->_transaction->id,
                'amount' => $creditable_amount,
                'reason' => 'Refund for order #' . $this->_transaction->id,
            ]);
        }

        // Refund the purchaser the amount they paid in cash
        $purchaser->update(['balance' => $purchaser->balance->add($this->_transaction->purchaser_amount)]);
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS => redirect()->back()->with('success', $this->getMessage()),
            default => redirect()->back()->with('error', $this->getMessage()),
        };
    }
}
