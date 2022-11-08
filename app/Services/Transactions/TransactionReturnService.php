<?php

namespace App\Services\Transactions;

use App\Models\Product;
use App\Services\Service;
use App\Helpers\TaxHelper;
use App\Models\Transaction;
use App\Models\TransactionProduct;
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

        $purchaser = $this->_transaction->purchaser;

        $this->_transaction->products->each(function (TransactionProduct $product) {
            $product->update(['returned' => $product->quantity]);
        });

        $purchaser->balance = $purchaser->balance->add($this->_transaction->total_price);
        $this->_transaction->update(['returned' => true]);

        $this->_result = self::RESULT_SUCCESS;
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

        // If it has not been returned more times than it was purchased, then ++ the returned count and refund the original cost + taxes
        if ($transaction_product->returned >= $transaction_product->quantity) {
            $this->_result = self::RESULT_ITEM_RETURNED_MAX_TIMES;
            $this->_message = 'That item has already been returned the maximum amount of times for that order.';
            return $this;
        }

        $transaction_product->increment('returned');

        $product_total = TaxHelper::calculateFor(
            $transaction_product->price,
            $transaction_product->quantity - $transaction_product->returned,
            $transaction_product->pst !== null,
            [
                'pst' => $transaction_product->pst,
                'gst' => $transaction_product->gst,
            ]
        );

        $this->_transaction->purchaser->balance = $this->_transaction->purchaser->balance->add($product_total);

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
