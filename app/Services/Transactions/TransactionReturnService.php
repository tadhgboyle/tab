<?php

namespace App\Services\Transactions;

use App\Services\GiftCards\GiftCardAdjustmentService;
use App\Services\HttpService;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use Cknow\Money\Money;
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
        $gift_card_amount = $this->totalToRefundGiftCard();
        $purchaser_amount = $this->totalToRefundPurchaser($gift_card_amount);

        if ($gift_card_amount->isPositive()) {
            $gift_card = $this->_transaction->giftCard;
            $gift_card->update(['remaining_balance' => $gift_card->remaining_balance->add($gift_card_amount)]);

            $giftCardAdjustmentService = new GiftCardAdjustmentService($gift_card, $this->_transaction);
            $giftCardAdjustmentService->refund($gift_card_amount);
        }

        if ($purchaser_amount->isPositive()) {
            $purchaser->update(['balance' => $purchaser->balance->add($purchaser_amount)]);
        }
    }

    private function totalToRefundGiftCard(): Money
    {
        if ($this->_transaction->gift_card_amount->isZero()) {
            return Money::parse(0);
        }

        return $this->_transaction->gift_card_amount->subtract(
            $this->_transaction->getAmountRefundedToGiftCard()
        );
    }

    private function totalToRefundPurchaser(Money $giftCardRefund): Money
    {
        return $this->_transaction->getOwingTotal()->subtract($giftCardRefund);
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS => redirect()->back()->with('success', $this->getMessage()),
            default => redirect()->back()->with('error', $this->getMessage()),
        };
    }
}
