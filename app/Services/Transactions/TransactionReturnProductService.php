<?php

namespace App\Services\Transactions;

use App\Models\Credit;
use App\Models\Product;
use App\Models\User;
use App\Services\Service;
use App\Helpers\TaxHelper;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use Cknow\Money\Money;
use Illuminate\Http\RedirectResponse;

// todo test credit reasons
class TransactionReturnProductService extends Service
{
    use TransactionService;

    public const RESULT_ALREADY_RETURNED = 'ALREADY_RETURNED';
    public const RESULT_ITEM_RETURNED_MAX_TIMES = 'ITEM_RETURNED_MAX_TIMES';
    public const RESULT_SUCCESS = 'SUCCESS';

    private TransactionProduct $_transactionProduct;

    public function __construct(Transaction $transaction, TransactionProduct $transactionProduct)
    {
        $this->_transaction = $transaction;
        $this->_transactionProduct = $transactionProduct;
    }

    public function return(): TransactionReturnProductService
    {
        if ($this->_transaction->isReturned()) {
            $this->_result = self::RESULT_ALREADY_RETURNED;
            $this->_message = 'That order has already been returned, so you cannot return an item from it.';
            return $this;
        }

        // If it has not been returned more times than it was purchased, then ++ the returned count and refund the original cost + taxes
        if ($this->_transactionProduct->returned >= $this->_transactionProduct->quantity) {
            $this->_result = self::RESULT_ITEM_RETURNED_MAX_TIMES;
            $this->_message = 'That item has already been returned the maximum amount of times for that order.';
            return $this;
        }

        $this->_transactionProduct->increment('returned');

        $this->refundPurchaser();
        $this->restoreStock();
        $this->updateTransactionReturnedAttribute();

        $this->_result = self::RESULT_SUCCESS;
        // todo update message to reflect if it was a credit or cash refund
        $this->_message = 'Successfully returned x1 ' . $this->_transactionProduct->product->name . ' for order #' . $this->_transaction->id . '.';
        return $this;
    }

    private function refundPurchaser(): void
    {
        $product_total = TaxHelper::forTransactionProduct($this->_transactionProduct);
        $purchaser = $this->_transaction->purchaser;

        // check if there is any gift card used
        $creditable_amount = $this->_transaction->creditableAmount();
        if ($creditable_amount->isPositive()) {
            // check if we have credited the same amount as the creditable amount yet
            $transaction_credits_amount = $this->_transaction->credits->reduce(function (Money $carry, Credit $credit) {
                return $carry->add($credit->amount);
            }, Money::parse(0));

            // if yes, return in cash
            if ($transaction_credits_amount->equals($creditable_amount)) {
                $purchaser->update(['balance' => $purchaser->balance->add($product_total)]);
            } elseif ($transaction_credits_amount->add($product_total)->greaterThan($creditable_amount)) {
                // if crediting this product exceeds the creditable amount, only credit the difference and return the rest in cash
                $amount_to_credit = $creditable_amount->subtract($transaction_credits_amount);
                $purchaser->credits()->create([
                    'transaction_id' => $this->_transaction->id,
                    'amount' => $amount_to_credit,
                    'reason' => 'Partial refund of ' . $this->_transactionProduct->product->name . ' for order #' . $this->_transaction->id,
                ]);
                $purchaser->update(['balance' => $purchaser->balance->add($product_total->subtract($amount_to_credit))]);
            } else {
                // if no, credit the amount of the product
                $purchaser->credits()->create([
                    'transaction_id' => $this->_transaction->id,
                    'amount' => $product_total,
                    'reason' => 'Refund of ' . $this->_transactionProduct->product->name . ' for order #' . $this->_transaction->id,
                ]);
            }
        } else {
            // if no, return in cash
            $purchaser->update(['balance' => $purchaser->balance->add($product_total)]);
        }
    }

    private function restoreStock(): void
    {
        if ($this->_transactionProduct->product->restore_stock_on_return) {
            $this->_transactionProduct->product->adjustStock(1);
        }
    }

    private function updateTransactionReturnedAttribute(): void
    {
        // set transaction to returned if all items have been returned
        if ($this->_transaction->products->sum->returned >= $this->_transaction->products->sum->quantity) {
            $this->_transaction->update(['returned' => true]);
        }
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS => redirect()->back()->with('success', $this->getMessage()),
            default => redirect()->back()->with('error', $this->getMessage()),
        };
    }
}
