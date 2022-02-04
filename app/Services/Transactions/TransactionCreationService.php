<?php

namespace App\Services\Transactions;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Services\Service;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ProductHelper;
use App\Helpers\RotationHelper;
use App\Helpers\SettingsHelper;
use App\Helpers\UserLimitsHelper;
use Illuminate\Http\RedirectResponse;

// TODO: Test stock stuff
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

    public function __construct(
        private Request $_request
    ) {
        if (RotationHelper::getInstance()->getCurrentRotation() === null) {
            $this->_result = self::RESULT_NO_CURRENT_ROTATION;
            $this->_message = 'Cannot create transaction with no current rotation.';
            return;
        }

        if (!hasPermission('cashier_self_purchases') && $this->_request->purchaser_id === auth()->id()) {
            $this->_result = self::RESULT_NO_SELF_PURCHASE;
            $this->_message = 'You cannot make purchases for yourself.';
            return;
        }

        if (!isset($this->_request->product)) {
            $this->_result = self::RESULT_NO_ITEMS_SELECTED;
            $this->_message = 'Please select at least one item.';
            return;
        }

        // For some reason, old() does not seem to work with array[] inputs in form. This will do for now...
        // ^ I'm sure it's a silly mistake on my end
        foreach ($this->_request->product as $product_id) {
            session()->flash("quantity[{$product_id}]", $this->_request->quantity[$product_id]);
            session()->flash("product[{$product_id}]", true);
        }

        $transaction_products = $transaction_categories = $stock_products = [];
        $total_price = 0;
        $total_tax = SettingsHelper::getInstance()->getGst();

        // Loop each product. Serialize it, and add it's cost to the transaction total
        foreach ($this->_request->product as $product_id) {
            if (!array_key_exists($product_id, $this->_request->quantity)) {
                continue;
            }

            $product = Product::find($product_id);

            $quantity = $this->_request->quantity[$product_id];
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

            $stock_products[] = $product;

            if ($product->pst) {
                $total_tax = ($total_tax + SettingsHelper::getInstance()->getPst()) - 1;
                $pst_metadata = SettingsHelper::getInstance()->getPst();
            } else {
                $pst_metadata = 'null';
            }

            // keep track of which unique categories are included in this transaction
            if (!in_array($product->category_id, $transaction_categories, true)) {
                $transaction_categories[] = $product->category_id;
            }

            $product_metadata = ProductHelper::serializeProduct($product_id, $quantity, $product->price, SettingsHelper::getInstance()->getGst(), $pst_metadata, 0);

            $transaction_products[] = $product_metadata;
            $total_price += (($product->price * $quantity) * $total_tax);
            $total_tax = SettingsHelper::getInstance()->getGst();
        }

        $purchaser = User::find($this->_request->purchaser_id);
        $remaining_balance = $purchaser->balance - $total_price;
        if ($remaining_balance < 0) {
            $this->_result = self::RESULT_NOT_ENOUGH_BALANCE;
            $this->_message = "Not enough balance. {$purchaser->full_name} only has $ {$purchaser->balance}";
            return;
        }

        // Loop categories within this transaction
        foreach ($transaction_categories as $category_id) {
            $limit_info = UserLimitsHelper::getInfo($purchaser, $category_id);
            $category_limit = $limit_info->limit_per;

            // Skip this category if they have unlimited. Saves time querying
            if ($category_limit === -1) {
                continue;
            }

            $category_spent = $category_spent_orig = UserLimitsHelper::findSpent($purchaser, $category_id, $limit_info);

            // Loop all products in this transaction. If the product's category is the current one in the above loop, add it's price to category spent
            foreach ($transaction_products as $product) {
                $product_metadata = ProductHelper::deserializeProduct($product);

                if ($product_metadata['category'] !== $category_id) {
                    continue;
                }

                $tax_percent = $product_metadata['gst'];

                if ($product_metadata['pst'] !== 'null') {
                    $tax_percent += $product_metadata['pst'] - 1;
                }

                $category_spent += ($product_metadata['price'] * $product_metadata['quantity']) * $tax_percent;
            }

            // Break loop if we exceed their limit
            if (!UserLimitsHelper::canSpend($purchaser, $category_spent, $category_id, $limit_info)) {
                $this->_result = self::RESULT_NOT_ENOUGH_CATEGORY_BALANCE;
                $this->_message = 'Not enough balance in that category: ' . Category::find($category_id)->name . ' (Limit: $' . number_format($category_limit, 2) . ', Remaining: $' . number_format($category_limit - $category_spent_orig, 2) . ').';
                return;
            }
        }

        foreach ($stock_products as $product) {
            // we already know the product has stock via hasStock() call above, so we don't need to check for the result of removeStock()
            $product->removeStock($this->_request->quantity[$product->id]);
        }

        $purchaser->update(['balance' => $remaining_balance]);

        $transaction = new Transaction();
        $transaction->purchaser_id = $purchaser->id;
        $transaction->cashier_id = auth()->id();
        $transaction->rotation_id = RotationHelper::getInstance()->getCurrentRotation()->id; // TODO: cannot make order without current rotation
        $transaction->products = implode(', ', $transaction_products);
        $transaction->total_price = $total_price;
        if ($this->_request->exists('created_at')) {
            $transaction->created_at = $this->_request->created_at; // for seeding random times
        }
        $transaction->save();

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = 'Order #' . $transaction->id . '. ' . $purchaser->full_name . ' now has $' . number_format(round($remaining_balance, 2), 2);
        $this->_total_price = $total_price;
        $this->_transaction = Transaction::find($transaction->id);
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
