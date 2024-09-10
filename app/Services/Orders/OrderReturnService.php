<?php

namespace App\Services\Orders;

use App\Models\Order;
use Cknow\Money\Money;
use App\Enums\OrderStatus;
use App\Models\OrderProduct;
use App\Services\HttpService;
use Illuminate\Http\RedirectResponse;
use App\Services\GiftCards\GiftCardAdjustmentService;

class OrderReturnService extends HttpService
{
    use OrderService;

    public const RESULT_ALREADY_RETURNED = 'ALREADY_RETURNED';
    public const RESULT_SUCCESS = 'SUCCESS';

    public function __construct(Order $order)
    {
        $this->_order = $order;

        if ($this->_order->isReturned()) {
            $this->_result = self::RESULT_ALREADY_RETURNED;
            $this->_message = 'That order has already been fully returned.';
            return;
        }

        $this->updateOrderProductAttributes();
        [$purchaserAmount, $giftCardAmount] = $this->refundPurchaser();
        $this->createOrderReturn($purchaserAmount, $giftCardAmount);

        $this->_order->update(['status' => OrderStatus::FullyReturned]);

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = 'Successfully returned order ' . $this->_order->identifier . ' for ' . $this->_order->purchaser->full_name;
    }

    private function updateOrderProductAttributes(): void
    {
        $this->_order->products->each(function (OrderProduct $orderProduct) {
            $returned = $orderProduct->returned;
            $orderProduct->update(['returned' => $orderProduct->quantity]);
            if ($orderProduct->product->restore_stock_on_return) {
                $amountToRestore = $orderProduct->quantity - $returned;
                if ($orderProduct->productVariant) {
                    $orderProduct->productVariant->adjustStock(
                        $amountToRestore
                    );
                } else {
                    $orderProduct->product->adjustStock(
                        $amountToRestore
                    );
                }
            }
        });
    }

    private function refundPurchaser(): array
    {
        $giftCardAmount = $this->totalToRefundGiftCard();
        $purchaserAmount = $this->totalToRefundPurchaser($giftCardAmount);

        if ($giftCardAmount->isPositive()) {
            $gift_card = $this->_order->giftCard;
            $gift_card->update(['remaining_balance' => $gift_card->remaining_balance->add($giftCardAmount)]);

            $giftCardAdjustmentService = new GiftCardAdjustmentService($gift_card, $this->_order);
            $giftCardAdjustmentService->refund($giftCardAmount);
        }

        if ($purchaserAmount->isPositive()) {
            $purchaser = $this->_order->purchaser;
            $purchaser->update(['balance' => $purchaser->balance->add($purchaserAmount)]);
        }

        return [$purchaserAmount, $giftCardAmount];
    }

    private function createOrderReturn(Money $purchaserAmount, Money $giftCardAmount): void
    {
        $this->_order->return()->create([
            'returner_id' => auth()->id(),
            'total_return_amount' => $purchaserAmount->add($giftCardAmount),
            'purchaser_amount' => $purchaserAmount,
            'gift_card_amount' => $giftCardAmount,
            'caused_by_product_return' => false,
        ]);
    }

    private function totalToRefundGiftCard(): Money
    {
        if ($this->_order->gift_card_amount->isZero()) {
            return Money::parse(0);
        }

        return $this->_order->gift_card_amount->subtract(
            $this->_order->getReturnedTotalToGiftCard()
        );
    }

    private function totalToRefundPurchaser(Money $giftCardRefund): Money
    {
        return $this->_order->getOwingTotal()->subtract($giftCardRefund);
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS => redirect()->back()->with('success', $this->getMessage()),
            default => redirect()->back()->with('error', $this->getMessage()),
        };
    }
}
