<?php

namespace App\Services\Transactions;

use App\Models\User;
use Cknow\Money\Money;
use App\Models\Product;
use App\Models\GiftCard;
use App\Services\HttpService;
use App\Helpers\TaxHelper;
use App\Helpers\Permission;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\RotationHelper;
use App\Helpers\SettingsHelper;
use App\Models\TransactionProduct;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;

class TransactionCreateService extends HttpService
{
    use TransactionService;

    public const RESULT_NO_SELF_PURCHASE = 'NO_SELF_PURCHASE';
    public const RESULT_NO_ITEMS_SELECTED = 'NO_ITEMS_SELECTED';
    public const RESULT_NEGATIVE_QUANTITY = 'NEGATIVE_QUANTITY';
    public const RESULT_NO_STOCK = 'NO_STOCK';
    public const RESULT_NOT_ENOUGH_BALANCE = 'NOT_ENOUGH_BALANCE';
    public const RESULT_NOT_ENOUGH_CATEGORY_BALANCE = 'NOT_ENOUGH_CATEGORY_BALANCE';
    public const RESULT_NO_CURRENT_ROTATION = 'NO_CURRENT_ROTATION';
    public const RESULT_INVALID_GIFT_CARD = 'INVALID_GIFT_CARD';
    public const RESULT_INVALID_GIFT_CARD_BALANCE = 'INVALID_GIFT_CARD_BALANCE';
    public const RESULT_SUCCESS = 'SUCCESS';

    public function __construct(Request $request, User $purchaser)
    {
        if (resolve(RotationHelper::class)->getCurrentRotation() === null) {
            $this->_result = self::RESULT_NO_CURRENT_ROTATION;
            $this->_message = 'Cannot create transaction with no current rotation.';
            return;
        }

        if (!hasPermission(Permission::CASHIER_SELF_PURCHASES) && $purchaser->id === auth()->id()) {
            $this->_result = self::RESULT_NO_SELF_PURCHASE;
            $this->_message = 'You cannot make purchases for yourself.';
            return;
        }

        $order_products = collect(json_decode($request->get('products')));

        if (!$order_products->count()) {
            $this->_result = self::RESULT_NO_ITEMS_SELECTED;
            $this->_message = 'Please select at least one item.';
            return;
        }

        $settingsHelper = resolve(SettingsHelper::class);

        /** @var Collection<TransactionProduct> */
        $transaction_products = Collection::make();

        $total_price = Money::parse(0);

        foreach ($order_products->all() as $product_meta) {
            $id = $product_meta->id;
            $quantity = $product_meta->quantity;

            $product = Product::find($id);

            if ($quantity < 1) {
                $this->_result = self::RESULT_NEGATIVE_QUANTITY;
                $this->_message = "Quantity must be >= 1 for item {$product->name}";
                return;
            }

            // Stock handling
            if (!$product->hasStock($quantity)) {
                $this->_result = self::RESULT_NO_STOCK;
                $this->_message = "Not enough {$product->name} in stock. Only {$product->stock} remaining.";
                return;
            }

            $transaction_products->add(TransactionProduct::from(
                $product,
                $quantity,
                $settingsHelper->getGst(),
                $product->pst ? $settingsHelper->getPst() : null
            ));

            $total_price = $total_price->add(TaxHelper::calculateFor(Money::parse($product->price), $quantity, $product->pst));
        }

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

                $gift_card_balance = Money::parse($gift_card->remaining_balance);

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

        $remaining_balance = Money::parse($purchaser->balance)->subtract($charge_user_amount);
        if ($remaining_balance->isNegative()) {
            $this->_result = self::RESULT_NOT_ENOUGH_BALANCE;
            $this->_message = "Not enough balance. {$purchaser->full_name} only has {$purchaser->balance}. Tried to spend {$total_price}.";
            return;
        }

        // Loop categories within this transaction
        foreach ($transaction_products->map(fn (TransactionProduct $product) => $product->category)->unique() as $category) {
            $user_limit = $purchaser->limitFor($category);
            $category_spending = Money::parse(0);
            $category_spent_orig = $user_limit->findSpent();

            // Loop all products in this transaction. If the product's category is the current one in the above loop, add its price to category spent
            foreach ($transaction_products->filter(fn (TransactionProduct $product) => $product->category->id === $category->id) as $product) {
                $category_spending = $category_spending->add(TaxHelper::calculateFor(Money::parse($product->price), $product->quantity, $product->pst !== null));
            }

            // Break loop if we exceed their limit
            if (!$user_limit->canSpend($category_spending)) {
                $this->_result = self::RESULT_NOT_ENOUGH_CATEGORY_BALANCE;
                $this->_message = 'Not enough balance in the ' . $category->name . ' category. (Limit: ' . $user_limit->limit . ', Remaining: ' . Money::parse($user_limit->limit)->subtract($category_spent_orig) . '). Tried to spend ' . $category_spending;
                return;
            }
        }

        $transaction_products->each(fn (TransactionProduct $product) => $product->product->removeStock(
            $order_products->firstWhere('id', $product->product_id)->quantity
        ));

        $transaction = new Transaction();
        $transaction->purchaser_id = $purchaser->id;
        $transaction->cashier_id = auth()->id();
        $transaction->total_price = $total_price->getAmount() / 100;
        $transaction->purchaser_amount = $charge_user_amount->getAmount() / 100;
        $transaction->gift_card_amount = $gift_card_amount->getAmount() / 100;
        $transaction->gift_card_id = isset($gift_card) ? $gift_card->id : null;
        if (isset($gift_card, $gift_card_remaining_balance)) {
            $gift_card->remaining_balance = $gift_card_remaining_balance->getAmount() / 100;
            $gift_card->save();
        }

        if ($request->has('rotation_id')) {
            // For seeding random rotations
            $transaction->rotation_id = $request->get('rotation_id');
        } else {
            // TODO: cannot make order without current rotation
            $transaction->rotation_id = resolve(RotationHelper::class)->getCurrentRotation()->id;
        }
        if ($request->has('created_at')) {
            // For seeding random times
            $transaction->created_at = $request->created_at;
        }
        $transaction->save();

        $transaction_products->each(function (TransactionProduct $product) use ($transaction) {
            $product->transaction_id = $transaction->id;
            $product->save();
        });

        $purchaser->update(['balance' => $remaining_balance->getAmount() / 100]);

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = 'Order #' . $transaction->id . '. ' . $purchaser->full_name . ' now has ' . $remaining_balance;
        $this->_transaction = $transaction->refresh();
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_NO_SELF_PURCHASE => redirect('/')->with('error', $this->getMessage()),
            self::RESULT_SUCCESS => redirect('/')->with([
                'success' => $this->getMessage(),
                'last_purchaser_id' => $this->_transaction->purchaser_id,
            ]),
            default => redirect()->back()->with('error', $this->getMessage()),
        };
    }
}
