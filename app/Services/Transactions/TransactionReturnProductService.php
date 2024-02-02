<?php

namespace App\Services\Transactions;

use App\Services\Service;
use App\Helpers\TaxHelper;
use App\Models\TransactionProduct;
use Illuminate\Http\RedirectResponse;

class TransactionReturnProductService extends Service
{
    use TransactionService;

    public const RESULT_ALREADY_RETURNED = 'ALREADY_RETURNED';
    public const RESULT_ITEM_RETURNED_MAX_TIMES = 'ITEM_RETURNED_MAX_TIMES';
    public const RESULT_SUCCESS = 'SUCCESS';

    private TransactionProduct $_transactionProduct;

    public function __construct(TransactionProduct $transactionProduct)
    {
        $this->_transaction = $transactionProduct->transaction;
        $this->_transactionProduct = $transactionProduct;

        if ($this->_transaction->isReturned()) {
            $this->_result = self::RESULT_ALREADY_RETURNED;
            $this->_message = 'That order has already been returned, so you cannot return an item from it.';
            return;
        }

        // If it has not been returned more times than it was purchased, then ++ the returned count and refund the original cost + taxes
        if ($this->_transactionProduct->returned >= $this->_transactionProduct->quantity) {
            $this->_result = self::RESULT_ITEM_RETURNED_MAX_TIMES;
            $this->_message = 'That item has already been returned the maximum amount of times for that order.';
            return;
        }

        $this->_transactionProduct->increment('returned');

        $this->refundPurchaser();
        $this->restoreStock();
        $this->updateTransactionReturnedAttribute();

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = 'Successfully returned x1 ' . $this->_transactionProduct->product->name . ' for order #' . $this->_transaction->id . '.';
        return;
    }

    private function refundPurchaser(): void
    {
        $purchaser = $this->_transaction->purchaser;
        $product_total = TaxHelper::forTransactionProduct($this->_transactionProduct, 1);
        $purchaser->update(['balance' => $purchaser->balance->add($product_total)]);
    }

    private function restoreStock(): void
    {
        if ($this->_transactionProduct->product->restore_stock_on_return) {
            $this->_transactionProduct->product->adjustStock(1);
        }
    }

    private function updateTransactionReturnedAttribute(): void
    {
        // Reload the transactionProdicts to get the updated returned count
        $this->_transaction->load('products');

        if ($this->_transaction->products->sum->returned === $this->_transaction->products->sum->quantity) {
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
