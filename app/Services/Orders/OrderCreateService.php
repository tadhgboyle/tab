<?php

namespace App\Services\Orders;

use App\Models\User;
use App\Models\Order;
use Cknow\Money\Money;
use App\Models\Product;
use App\Models\GiftCard;
use App\Helpers\Permission;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use App\Services\HttpService;
use App\Helpers\RotationHelper;
use App\Helpers\SettingsHelper;
use Illuminate\Support\Collection;
use App\Helpers\NotificationHelper;
use Illuminate\Http\RedirectResponse;
use App\Services\GiftCards\GiftCardAdjustmentService;

class OrderCreateService extends HttpService
{
    use OrderService;

    public const RESULT_NO_SELF_PURCHASE = 'NO_SELF_PURCHASE';
    public const RESULT_NO_ITEMS_SELECTED = 'NO_ITEMS_SELECTED';
    public const RESULT_MUST_SELECT_VARIANT = 'MUST_SELECT_VARIANT';
    public const RESULT_NEGATIVE_QUANTITY = 'NEGATIVE_QUANTITY';
    public const RESULT_PRODUCT_NOT_ACTIVE = 'PRODUCT_NOT_ACTIVE';
    public const RESULT_NO_STOCK = 'NO_STOCK';
    public const RESULT_NOT_ENOUGH_BALANCE = 'NOT_ENOUGH_BALANCE';
    public const RESULT_NOT_ENOUGH_CATEGORY_BALANCE = 'NOT_ENOUGH_CATEGORY_BALANCE';
    public const RESULT_NO_CURRENT_ROTATION = 'NO_CURRENT_ROTATION';
    public const RESULT_INVALID_GIFT_CARD = 'INVALID_GIFT_CARD';
    public const RESULT_INVALID_GIFT_CARD_BALANCE = 'INVALID_GIFT_CARD_BALANCE';
    public const RESULT_GIFT_CARD_CANNOT_BE_USED = 'GIFT_CARD_CANNOT_BE_USED';
    public const RESULT_SUCCESS = 'SUCCESS';

    public function __construct(Request $request, User $purchaser)
    {
        if (resolve(RotationHelper::class)->getCurrentRotation() === null) {
            $this->_result = self::RESULT_NO_CURRENT_ROTATION;
            $this->_message = 'Cannot create order with no current rotation.';
            return;
        }

        if (!hasPermission(Permission::CASHIER_SELF_PURCHASES) && $purchaser->id === auth()->id()) {
            $this->_result = self::RESULT_NO_SELF_PURCHASE;
            $this->_message = 'You cannot make purchases for yourself.';
            return;
        }

        $order_products_from_request = collect(json_decode($request->get('products')));

        if (!$order_products_from_request->count()) {
            $this->_result = self::RESULT_NO_ITEMS_SELECTED;
            $this->_message = 'Please select at least one item.';
            return;
        }

        $settingsHelper = resolve(SettingsHelper::class);

        /** @var Collection<OrderProduct> */
        $order_products = Collection::make();

        foreach ($order_products_from_request->all() as $product_meta) {
            $id = $product_meta->id;
            $variantId = isset($product_meta->variantId) ? $product_meta->variantId : null;
            $quantity = $product_meta->quantity;

            $product = Product::find($id);
            $productVariant = $variantId ? $product->variants->find($variantId) : null;
            if ($product->hasVariants() && !$productVariant) {
                $this->_result = self::RESULT_MUST_SELECT_VARIANT;
                $this->_message = "You must select a variant for item {$product->name}";
                return;
            }

            if ($quantity < 1) {
                $this->_result = self::RESULT_NEGATIVE_QUANTITY;
                $this->_message = "Quantity must be >= 1 for item {$product->name}";
                return;
            }

            if (!$product->isActive()) {
                $this->_result = self::RESULT_PRODUCT_NOT_ACTIVE;
                $this->_message = "Product {$product->name} is not active.";
                return;
            }

            // Stock handling
            if ($productVariant) {
                if (!$productVariant->hasStock($quantity)) {
                    $this->_result = self::RESULT_NO_STOCK;
                    $this->_message = "Not enough {$productVariant->description()} in stock. Only {$productVariant->stock} remaining.";
                    return;
                }
            } else {
                if (!$product->hasStock($quantity)) {
                    $this->_result = self::RESULT_NO_STOCK;
                    $this->_message = "Not enough {$product->name} in stock. Only {$product->stock} remaining.";
                    return;
                }
            }

            $order_products->add(OrderProduct::from(
                $product,
                $productVariant,
                $quantity,
                $settingsHelper->getGst(),
                $product->pst ? $settingsHelper->getPst() : null
            ));
        }

        $total_price = $order_products->reduce(fn (Money $carry, OrderProduct $product) => $carry->add($product->total_price), Money::parse(0));

        $charge_user_amount = $total_price;

        $gift_card_amount = Money::parse(0);

        if ($charge_user_amount->isPositive()) {
            $gift_card_code = $request->get('gift_card_code');
            if ($gift_card_code) {
                $gift_card = GiftCard::firstWhere('code', $gift_card_code);
                if (!$gift_card) {
                    $this->_result = self::RESULT_INVALID_GIFT_CARD;
                    $this->_message = "Gift card with code {$gift_card_code} does not exist.";
                    return;
                }

                if (!$gift_card->canBeUsedBy($purchaser)) {
                    $this->_result = self::RESULT_GIFT_CARD_CANNOT_BE_USED;
                    $this->_message = "Gift card with code {$gift_card_code} cannot be used by {$purchaser->full_name}.";
                    return;
                }

                $gift_card_balance = $gift_card->remaining_balance;

                if ($gift_card_balance->isZero()) {
                    $this->_result = self::RESULT_INVALID_GIFT_CARD_BALANCE;
                    $this->_message = "Gift card with code {$gift_card_code} has a $0.00 balance.";
                    return;
                }

                if ($gift_card_balance->greaterThanOrEqual($total_price)) {
                    $charge_user_amount = Money::parse(0);
                    $gift_card_amount = $total_price;
                    $gift_card_remaining_balance = $gift_card_balance->subtract($total_price);
                } else {
                    $charge_user_amount = $charge_user_amount->subtract($gift_card_balance);
                    $gift_card_amount = $gift_card_balance;
                    $gift_card_remaining_balance = Money::parse(0);
                }
            }
        }

        // TODO can this be moved above?
        $remaining_balance = $purchaser->balance->subtract($charge_user_amount);
        if ($remaining_balance->isNegative()) {
            $this->_result = self::RESULT_NOT_ENOUGH_BALANCE;
            $this->_message = "Not enough balance. {$purchaser->full_name} only has {$purchaser->balance}. Tried to spend {$total_price}.";
            return;
        }

        // Loop categories within this order
        foreach ($order_products->map(fn (OrderProduct $product) => $product->category)->unique() as $category) {
            $user_limit = $purchaser->limitFor($category);
            $category_spending = Money::parse(0);
            $category_spent_orig = $user_limit->findSpent();

            // Loop all products in this order. If the product's category is the current one in the above loop, add its price to category spent
            foreach ($order_products->filter(fn (OrderProduct $orderProduct) => $orderProduct->category->id === $category->id) as $orderProduct) {
                $category_spending = $category_spending->add(
                    // TODO use new method on OrderProduct?
                    $orderProduct->total_price,
                );
            }

            // Break loop if we exceed their limit
            if (!$user_limit->canSpend($category_spending)) {
                $this->_result = self::RESULT_NOT_ENOUGH_CATEGORY_BALANCE;
                $this->_message = 'Not enough balance in the ' . $category->name . ' category. (Limit: ' . $user_limit->limit . ', Remaining: ' . $user_limit->limit->subtract($category_spent_orig) . '). Tried to spend ' . $category_spending;
                return;
            }
        }

        $order_products->each(function (OrderProduct $orderProduct) {
            if ($orderProduct->productVariant) {
                $orderProduct->productVariant->removeStock($orderProduct->quantity);
            } else {
                $orderProduct->product->removeStock($orderProduct->quantity);
            }
        });

        $order = new Order();
        $order->purchaser_id = $purchaser->id;
        $order->cashier_id = auth()->id();
        $order->total_price = $total_price;
        $order->total_tax = $total_tax = $order_products->reduce(fn (Money $carry, OrderProduct $product) => $carry->add($product->total_tax), Money::parse(0));
        $order->subtotal = $total_price->subtract($total_tax);
        $order->purchaser_amount = $charge_user_amount;
        $order->gift_card_amount = $gift_card_amount;
        $order->gift_card_id = isset($gift_card) ? $gift_card->id : null;
        // TODO: cannot make order without current rotation
        $order->rotation_id = resolve(RotationHelper::class)->getCurrentRotation()->id;
        $order->save();

        if (isset($gift_card, $gift_card_remaining_balance)) {
            $gift_card->remaining_balance = $gift_card_remaining_balance;
            $gift_card->save();

            $giftCardAdjustmentService = new GiftCardAdjustmentService($gift_card, $order);
            $giftCardAdjustmentService->charge($gift_card_amount);
        }

        $order_products->each(function (OrderProduct $product) use ($order) {
            $product->order_id = $order->id;
            $product->save();
        });

        $purchaser->update(['balance' => $remaining_balance]);

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = $purchaser->full_name . ' now has ' . $remaining_balance;
        $this->_order = $order->refresh();
    }

    public function redirect(): RedirectResponse
    {
        $this->buildNotification();

        return match ($this->getResult()) {
            self::RESULT_NO_SELF_PURCHASE => redirect()->route('cashier')->with('error', $this->getMessage()),
            self::RESULT_SUCCESS => redirect()->route('cashier')->with([
                'last_purchaser_id' => $this->_order->purchaser_id,
            ]),
            default => redirect()->back()->with('error', $this->getMessage()),
        };
    }

    private function buildNotification(): void
    {
        if ($this->getResult() === self::RESULT_SUCCESS) {
            app(NotificationHelper::class)->sendSuccessNotification('Order Created', $this->getMessage(), [
                ['name' => 'view_order', 'url' => route('orders_view', $this->_order)]
            ]);
        }
    }
}
