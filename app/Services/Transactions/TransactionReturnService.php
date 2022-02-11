<?php

namespace App\Services\Transactions;

use App\Services\Service;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;

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

        $total_price = 0;
        $purchaser = $this->_transaction->purchaser;

        // Loop through products from the order and deserialize them to get their prices & taxes etc when they were purchased
        foreach ($this->_transaction->products as $product) {
            $product->returned = $product->quantity;
            $total_price += ($product->price * $product->quantity) * $product->getTax();
        }

        $purchaser->increment('balance', $total_price);
        $this->_transaction->update(['returned' => true]);

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = 'Successfully returned order #' . $this->_transaction->id . ' for ' . $purchaser->full_name;
        return $this;
    }

    public function returnItem(int $product_id): TransactionReturnService
    {
        if ($this->_transaction->isReturned()) {
            $this->_result = self::RESULT_ALREADY_RETURNED;
            $this->_message = 'That order has already been returned, so you cannot return an item from it.';
            return $this;
        }

        $transaction_products = $this->_transaction->products;
        if (!$transaction_products->pluck('product_id')->contains($product_id)) {
            $this->_result = self::RESULT_ITEM_NOT_IN_ORDER;
            $this->_message = 'That item is not in this order.';
            return $this;
        }

        $transaction_product = $transaction_products->firstWhere('product_id', $product_id);

        // If it has not been returned more times than it was purchased, then ++ the returned count and refund the original cost + taxes
        if ($transaction_product->returned >= $transaction_product->quantity) {
            $this->_result = self::RESULT_ITEM_RETURNED_MAX_TIMES;
            $this->_message = 'That item has already been returned the maximum amount of times for that order.';
            return $this;
        }

        $transaction_product->increment('returned');

        // Update their balance
        $this->_transaction->purchaser->increment('balance', $transaction_product->price * $transaction_product->getTax());
        $this->_result = self::RESULT_SUCCESS;
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
