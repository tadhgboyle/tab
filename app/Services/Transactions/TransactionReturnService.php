<?php

namespace App\Services\Transactions;

use App\Models\Credit;
use App\Models\Product;
use App\Services\Service;
use App\Helpers\TaxHelper;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use Cknow\Money\Money;
use Illuminate\Http\RedirectResponse;

// todo split into two services, one for returning an order and one for returning an item
// todo test credit reasons
class TransactionReturnService extends Service
{
    use TransactionService;

    public const RESULT_ALREADY_RETURNED = 1;
    public const RESULT_ITEM_RETURNED_MAX_TIMES = 2;
    public const RESULT_ITEM_NOT_IN_ORDER = 3;
    public const RESULT_SUCCESS = 4;

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

        $this->_transaction->products->each(function (TransactionProduct $transactionProduct) {
            $returned = $transactionProduct->returned;
            $transactionProduct->update(['returned' => $transactionProduct->quantity]);
            if ($transactionProduct->product->restore_stock_on_return) {
                $transactionProduct->product->adjustStock(
                    $transactionProduct->quantity - $returned
                );
            }
        });

        $purchaser = $this->_transaction->purchaser;
        $creditable_amount = $this->_transaction->creditableAmount();
        if ($creditable_amount->isPositive()) {
            $purchaser->credits()->create([
                'transaction_id' => $this->_transaction->id,
                'amount' => $creditable_amount,
                'reason' => 'Refund for order #' . $this->_transaction->id,
            ]);
        }

        $purchaser->update(['balance' => $purchaser->balance->add($this->_transaction->purchaser_amount)]);

        $this->_transaction->update(['returned' => true]);

        $this->_result = self::RESULT_SUCCESS;
        // todo update message to reflect if a credit was made
        $this->_message = 'Successfully returned order #' . $this->_transaction->id . ' for ' . $purchaser->full_name;
        return $this;
    }

    public function returnItem(Product $product): TransactionReturnService
    {
        if ($this->_transaction->isReturned()) {
            $this->_result = self::RESULT_ALREADY_RETURNED;
            $this->_message = 'That order has already been returned, so you cannot return an item from it.';
            return $this;
        }

        $transaction_products = $this->_transaction->products;
        if (!$transaction_products->contains('product_id', $product->id)) {
            $this->_result = self::RESULT_ITEM_NOT_IN_ORDER;
            $this->_message = 'That item is not in this order.';
            return $this;
        }

        $transaction_product = $transaction_products->firstWhere('product_id', $product->id);

        if (!$transaction_product) {
            $this->_result = self::RESULT_ITEM_NOT_IN_ORDER;
            $this->_message = 'That item is not in this order.';
            return $this;
        }

        // If it has not been returned more times than it was purchased, then ++ the returned count and refund the original cost + taxes
        if ($transaction_product->returned >= $transaction_product->quantity) {
            $this->_result = self::RESULT_ITEM_RETURNED_MAX_TIMES;
            $this->_message = 'That item has already been returned the maximum amount of times for that order.';
            return $this;
        }

        $transaction_product->increment('returned');

        $product_total = TaxHelper::forTransactionProduct($transaction_product);
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
                // available credit = $5
                // product is $10
                // give them $5 credit and $5 cash
                $amount_to_credit = $creditable_amount->subtract($transaction_credits_amount);
                $purchaser->credits()->create([
                    'transaction_id' => $this->_transaction->id,
                    'amount' => $amount_to_credit,
                    'reason' => 'Partial refund of ' . $transaction_product->product->name . ' for order #' . $this->_transaction->id,
                ]);
                $purchaser->update(['balance' => $purchaser->balance->add($product_total->subtract($amount_to_credit))]);
            } else {
                // if no, credit the amount of the product
                $purchaser->credits()->create([
                    'transaction_id' => $this->_transaction->id,
                    'amount' => $product_total,
                    'reason' => 'Refund of ' . $transaction_product->product->name . ' for order #' . $this->_transaction->id,
                ]);
            }
        } else {
            // if no, return in cash
            $purchaser->update(['balance' => $purchaser->balance->add($product_total)]);
        }

        if ($transaction_product->product->restore_stock_on_return) {
            $transaction_product->product->adjustStock(1);
        }

        // set transaction to returned if all items have been returned
        if ($this->_transaction->products->sum->returned >= $this->_transaction->products->sum->quantity) {
            $this->_transaction->update(['returned' => true]);
        }

        $this->_result = self::RESULT_SUCCESS;
        // todo update message to reflect if it was a credit or cash refund
        $this->_message = 'Successfully returned x1 ' . $transaction_product->product->name . ' for order #' . $this->_transaction->id . '.';
        return $this;
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS => redirect()->back()->with('success', $this->getMessage()),
            default => redirect()->back()->with('error', $this->getMessage()),
        };
    }
}
