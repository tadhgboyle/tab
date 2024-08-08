<?php

namespace App\Services\Orders;

use Cknow\Money\Money;
use App\Helpers\TaxHelper;
use App\Models\Order;
use App\Services\HttpService;
use App\Models\OrderProduct;
use Illuminate\Http\RedirectResponse;
use App\Services\GiftCards\GiftCardAdjustmentService;

class OrderReturnProductService extends HttpService
{
    use OrderService;

    public const RESULT_ALREADY_RETURNED = 'ALREADY_RETURNED';
    public const RESULT_ITEM_RETURNED_MAX_TIMES = 'ITEM_RETURNED_MAX_TIMES';
    public const RESULT_SUCCESS = 'SUCCESS';

    private OrderProduct $_orderProduct;

    public function __construct(OrderProduct $orderProduct)
    {
        $this->_order = $orderProduct->order;
        $this->_orderProduct = $orderProduct;

        if ($this->_order->isReturned()) {
            $this->_result = self::RESULT_ALREADY_RETURNED;
            $this->_message = 'That order has already been returned, so you cannot return an item from it.';
            return;
        }

        // If it has not been returned more times than it was purchased, then ++ the returned count and refund the original cost + taxes
        if ($this->_orderProduct->returned >= $this->_orderProduct->quantity) {
            $this->_result = self::RESULT_ITEM_RETURNED_MAX_TIMES;
            $this->_message = 'That item has already been returned the maximum amount of times for that order.';
            return;
        }

        $this->_orderProduct->increment('returned');

        $this->refundPurchaser();
        $this->restoreStock();
        $this->updateOrderReturnedAttribute();

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = 'Successfully returned x1 ' . $this->_orderProduct->product->name . ' for order #' . $this->_order->id . '.';

    }

    private function refundPurchaser(): void
    {
        $purchaser = $this->_order->purchaser;

        $product_total = TaxHelper::forOrderProduct($this->_orderProduct, 1);

        $gift_card_refund_amount = $this->totalToRefundGiftCard($product_total);
        $purchaser_refund_amount = $this->totalToRefundPurchaser($product_total, $gift_card_refund_amount);

        if ($gift_card_refund_amount->isPositive()) {
            $gift_card = $this->_order->giftCard;
            $gift_card->update(['remaining_balance' => $gift_card->remaining_balance->add($gift_card_refund_amount)]);

            $giftCardAdjustmentService = new GiftCardAdjustmentService($gift_card, $this->_order);
            $giftCardAdjustmentService->refund($gift_card_refund_amount);
        }

        if ($purchaser_refund_amount->isPositive()) {
            $purchaser->update(['balance' => $purchaser->balance->add($purchaser_refund_amount)]);
        }
    }

    private function totalToRefundGiftCard(Money $productTotal): Money
    {
        if ($this->_order->gift_card_amount->isZero()) {
            return Money::parse(0);
        }

        $amountRefundedToGiftCard = $this->_order->getAmountRefundedToGiftCard();
        $amountLeftToRefundOnGiftCard = $this->_order->gift_card_amount->subtract($amountRefundedToGiftCard);

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
        if ($this->_orderProduct->product->restore_stock_on_return) {
            $this->_orderProduct->product->adjustStock(1);
        }
    }

    private function updateOrderReturnedAttribute(): void
    {
        // Reload the orderProdicts to get the updated returned count
        $this->_order->load('products');

        $this->_order->update([
            'status' => $this->_order->products->sum->returned === $this->_order->products->sum->quantity
                ? Order::STATUS_FULLY_RETURNED
                : Order::STATUS_PARTIAL_RETURNED,
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
