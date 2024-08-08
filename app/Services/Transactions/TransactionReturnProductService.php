<?php

namespace App\Services\Transactions;

use App\Services\GiftCards\GiftCardAdjustmentService;
use App\Services\HttpService;
use App\Helpers\TaxHelper;
use App\Models\TransactionProduct;
use Cknow\Money\Money;
use Illuminate\Http\RedirectResponse;
use App\Models\Transaction;

class TransactionReturnProductService extends HttpService
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

        $gift_card_refund_amount = $this->totalToRefundGiftCard($product_total);
        $purchaser_refund_amount = $this->totalToRefundPurchaser($product_total, $gift_card_refund_amount);

        if ($gift_card_refund_amount->isPositive()) {
            $gift_card = $this->_transaction->giftCard;
            $gift_card->update(['remaining_balance' => $gift_card->remaining_balance->add($gift_card_refund_amount)]);

            $giftCardAdjustmentService = new GiftCardAdjustmentService($gift_card, $this->_transaction);
            $giftCardAdjustmentService->refund($gift_card_refund_amount);
        }

        if ($purchaser_refund_amount->isPositive()) {
            $purchaser->update(['balance' => $purchaser->balance->add($purchaser_refund_amount)]);
        }
    }

    private function totalToRefundGiftCard(Money $productTotal): Money
    {
        if ($this->_transaction->gift_card_amount->isZero()) {
            return Money::parse(0);
        }

        $amountRefundedToGiftCard = $this->_transaction->getAmountRefundedToGiftCard();
        $amountLeftToRefundOnGiftCard = $this->_transaction->gift_card_amount->subtract($amountRefundedToGiftCard);

        if ($amountLeftToRefundOnGiftCard->greaterThanOrEqual($productTotal)) {
            return $productTotal;
        }

        return $amountLeftToRefundOnGiftCard;
    }

    private function totalToRefundPurchaser(Money $productTotal, Money $giftCardRefund): Money
    {
        return $productTotal->subtract($giftCardRefund);
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

        $this->_transaction->update([
            'status' => $this->_transaction->products->sum->returned === $this->_transaction->products->sum->quantity
                ? Transaction::STATUS_FULLY_RETURNED
                : Transaction::STATUS_PARTIAL_RETURNED,
        ]);
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS => redirect()->back()->with('success', $this->getMessage()),
            default => redirect()->back()->with('error', $this->getMessage()),
        };
    }
}
