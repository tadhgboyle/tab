<?php

namespace App\Services\Transactions;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Services\Service;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\RotationHelper;
use App\Helpers\SettingsHelper;
use App\Helpers\UserLimitsHelper;
use App\Models\TransactionProduct;
use Illuminate\Http\RedirectResponse;

class TransactionCreationService extends Service
{
    use TransactionService;

    private float $_total_price;

    public const RESULT_NO_SELF_PURCHASE = 0;
    public const RESULT_NO_ITEMS_SELECTED = 1;
    public const RESULT_NEGATIVE_QUANTITY = 2;
    public const RESULT_NO_STOCK = 3;
    public const RESULT_NOT_ENOUGH_BALANCE = 4;
    public const RESULT_NOT_ENOUGH_CATEGORY_BALANCE = 5;
    public const RESULT_NO_CURRENT_ROTATION = 6;
    public const RESULT_SUCCESS = 7;

    public function __construct(Request $request)
    {
        if (resolve(RotationHelper::class)->getCurrentRotation() === null) {
            $this->_result = self::RESULT_NO_CURRENT_ROTATION;
            $this->_message = 'Cannot create transaction with no current rotation.';
            return;
        }

        if (!hasPermission('cashier_self_purchases') && $request->purchaser_id === auth()->id()) {
            $this->_result = self::RESULT_NO_SELF_PURCHASE;
            $this->_message = 'You cannot make purchases for yourself.';
            return;
        }

        if (!$request->has('product')) {
            $this->_result = self::RESULT_NO_ITEMS_SELECTED;
            $this->_message = 'Please select at least one item.';
            return;
        }

        // For some reason, old() does not seem to work with array[] inputs in form. This will do for now...
        // ^ I'm sure it's a silly mistake on my end
        // TODO apparently dot notation works
        foreach ($request->product as $product_id) {
            session()->flash("quantity[{$product_id}]", $request->quantity[$product_id]);
            session()->flash("product[{$product_id}]", true);
        }

        $settingsHelper = resolve(SettingsHelper::class);
        $transaction_products = $transaction_categories = $stock_products = [];
        $total_price = 0;
        $total_tax = $settingsHelper->getGst();

        foreach ($request->product as $product_id) {
            if (!array_key_exists($product_id, $request->quantity)) {
                continue;
            }

            $product = Product::find($product_id);

            $quantity = $request->quantity[$product_id];
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

            if ($product->pst) {
                $total_tax = ($total_tax + $settingsHelper->getPst()) - 1;
            }

            $transaction_categories[] = (int) $product->category_id;
            $stock_products[] = $product;
            $transaction_products[] = TransactionProduct::of(
                $product_id,
                $product->category_id,
                $quantity,
                $product->price,
                $settingsHelper->getGst(),
                $product->pst ? $settingsHelper->getPst() : null
            );

            $total_price += (($product->price * $quantity) * $total_tax);
            $total_tax = $settingsHelper->getGst();
        }

        $purchaser = User::find($request->purchaser_id);
        $remaining_balance = $purchaser->balance - $total_price;
        if ($remaining_balance < 0) {
            $this->_result = self::RESULT_NOT_ENOUGH_BALANCE;
            $this->_message = "Not enough balance. {$purchaser->full_name} only has \${$purchaser->balance}. Tried to spend \${$total_price}.";
            return;
        }

        // Loop categories within this transaction
        foreach (array_unique($transaction_categories) as $category_id) {
            $limit_info = UserLimitsHelper::getInfo($purchaser, $category_id);
            $category_limit = $limit_info->limit_per;

            // Skip this category if they have unlimited. Saves time querying
            if ($category_limit === -1) {
                continue;
            }

            $category_spent = $category_spent_orig = UserLimitsHelper::findSpent($purchaser, $category_id, $limit_info);

            // Loop all products in this transaction. If the product's category is the current one in the above loop, add it's price to category spent
            foreach ($transaction_products as $product) {
                if ($product->category_id !== $category_id) {
                    continue;
                }

                $tax_percent = $product->gst;

                if ($product->pst !== null) {
                    $tax_percent += $product->pst - 1;
                }

                $category_spent += ($product->price * $product->quantity) * $tax_percent;
            }

            // Break loop if we exceed their limit
            if (!UserLimitsHelper::canSpend($purchaser, $category_spent, $category_id, $limit_info)) {
                $this->_result = self::RESULT_NOT_ENOUGH_CATEGORY_BALANCE;
                $this->_message = 'Not enough balance in the ' . Category::find($category_id)->name . ' category. (Limit: $' . number_format($category_limit, 2) . ', Remaining: $' . number_format($category_limit - $category_spent_orig, 2) . '). Tried to spend $' . number_format($category_spent, 2);
                return;
            }
        }

        foreach ($stock_products as $product) {
            $product->removeStock($request->quantity[$product->id]);
        }

        $transaction = new Transaction();
        $transaction->purchaser_id = $purchaser->id;
        $transaction->cashier_id = auth()->id();
        $transaction->rotation_id = $request->rotation_id ?? resolve(RotationHelper::class)->getCurrentRotation()->id; // TODO: cannot make order without current rotation
        $transaction->total_price = $total_price;
        if ($request->exists('created_at')) {
            $transaction->created_at = $request->created_at; // for seeding random times
        }
        $transaction->save();

        foreach ($transaction_products as $transaction_products_instance) {
            $transaction_products_instance->transaction_id = $transaction->id;
            $transaction_products_instance->save();
        }

        $purchaser->update(['balance' => $remaining_balance]);

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = 'Order #' . $transaction->id . '. ' . $purchaser->full_name . ' now has $' . number_format(round($remaining_balance, 2), 2);
        $this->_total_price = $total_price;
        $this->_transaction = $transaction->refresh();
    }

    public function getTotalPrice(): float
    {
        return $this->_total_price;
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_NO_SELF_PURCHASE => redirect('/')->with('error', $this->getMessage()),
            self::RESULT_SUCCESS => redirect('/')->with('success', $this->getMessage()),
            default => redirect()->back()->withInput()->with('error', $this->getMessage()),
        };
    }
}
