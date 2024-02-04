<?php

namespace App\Services\Transactions;

use App\Services\HttpService;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use Illuminate\Http\RedirectResponse;

class TransactionReturnService extends HttpService
{
    use TransactionService;

    public const RESULT_ALREADY_RETURNED = 'ALREADY_RETURNED';
    public const RESULT_SUCCESS = 'SUCCESS';

    public function __construct(Transaction $transaction)
    {
        $this->_transaction = $transaction;

        // This should never happen, but a good security measure
        if ($this->_transaction->isReturned()) {
            $this->_result = self::RESULT_ALREADY_RETURNED;
            $this->_message = 'That order has already been fully returned.';
            return;
        }

        $this->updateTransactionProductAttributes();
        $this->refundPurchaser();

        $this->_transaction->update(['status' => Transaction::STATUS_FULLY_RETURNED]);

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = 'Successfully returned order #' . $this->_transaction->id . ' for ' . $this->_transaction->purchaser->full_name;
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
        $purchaser = $this->_transaction->purchaser;

        if ($this->_transaction->total_price->isPositive()) {
            $purchaser->update(['balance' => $purchaser->balance->add($this->_transaction->total_price)]);
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
