<?php

namespace App\Services;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\SettingsHelper;
use App\Helpers\UserLimitsHelper;
use App\Http\Controllers\TransactionController;

class TransactionCreationService
{
    private int $_result;
    private string $_message;
    private float $_total_price;

    public function __construct(
        private Request $_request
    ) {
        $this->create();
    }

    public function create()
    {
        if (!hasPermission('cashier_self_purchases')) {
            if ($this->_request->purchaser_id == auth()->id()) {
                $this->_result = 0;
                $this->_message = 'You cannot make purchases for yourself.';
                return;
            }
        }

        if (!isset($this->_request->product)) {
            $this->_result = 1;
            $this->_message = 'Please select at least one item.';
            return;
        }

        // For some reason, old() does not seem to work with array[] inputs in form. This will do for now...
        // ^ I'm sure its a silly mistake on my end
        foreach ($this->_request->product as $product_id) {
            session()->flash("quantity[{$product_id}]", $this->_request->quantity[$product_id]);
            session()->flash("product[{$product_id}]", true);
        }

        $transaction_products = $transaction_categories = $stock_products = [];
        $total_price = 0;
        $quantity = 1;
        $product_metadata = $pst_metadata = '';
        $total_tax = SettingsHelper::getInstance()->getGst();

        // Loop each product. Serialize it, and add it's cost to the transaction total
        foreach ($this->_request->product as $product_id) {
            if (!array_key_exists($product_id, $this->_request->quantity)) {
                continue;
            }

            $product = Product::find($product_id);

            $quantity = $this->_request->quantity[$product_id];
            if ($quantity < 1) {
                $this->_result = 2;
                $this->_message = 'Quantity must be >= 1 for item ' . $product->name;
                return;
            }

            // Stock handling
            if (!$product->hasStock($quantity)) {
                $this->_result = 3;
                $this->_message = 'Not enough ' . $product->name . ' in stock. Only ' . $product->stock . ' remaining.';
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
            if (!in_array($product->category_id, $transaction_categories)) {
                $transaction_categories[] = $product->category_id;
            }

            $product_metadata = TransactionController::serializeProduct($product_id, $quantity, $product->price, SettingsHelper::getInstance()->getGst(), $pst_metadata, 0);

            $transaction_products[] = $product_metadata;
            $total_price += (($product->price * $quantity) * $total_tax);
            $total_tax = SettingsHelper::getInstance()->getGst();
        }

        $purchaser = User::find($this->_request->purchaser_id);
        $remaining_balance = $purchaser->balance - $total_price;
        if ($remaining_balance < 0) {
            $this->_result = 4;
            $this->_message = 'Not enough balance. ' . $purchaser->full_name . ' only has $' . $purchaser->balance;
            return;
        }

        $category_spent = $category_limit = 0.00;
        // Loop categories within this transaction
        foreach ($transaction_categories as $category_id) {
            $limit_info = UserLimitsHelper::getInfo($purchaser, $category_id);
            $category_limit = $limit_info->limit_per;

            // Skip this category if they have unlimited. Saves time querying
            if ($category_limit == -1) {
                continue;
            }

            $category_spent = $category_spent_orig = UserLimitsHelper::findSpent($purchaser, $category_id, $limit_info);

            // Loop all products in this transaction. If the product's category is the current one in the above loop, add it's price to category spent
            foreach ($transaction_products as $product) {
                $product_metadata = TransactionController::deserializeProduct($product);

                if ($product_metadata['category'] != $category_id) {
                    continue;
                }

                $tax_percent = $product_metadata['gst'];

                if ($product_metadata['pst'] != 'null') {
                    $tax_percent += $product_metadata['pst'] - 1;
                }

                $category_spent += ($product_metadata['price'] * $product_metadata['quantity']) * $tax_percent;
            }

            // Break loop if we exceed their limit
            if (!UserLimitsHelper::canSpend($purchaser, $category_spent, $category_id, $limit_info)) {
                $this->_result = 5;
                $this->_message = 'Not enough balance in that category: ' . Category::find($category_id)->name . ' (Limit: $' . number_format($category_limit, 2) . ', Remaining: $' . number_format($category_limit - $category_spent_orig, 2) . ').';
                return;
            }
        }

        foreach ($stock_products as $product) {
            // we already know the product has stock via hasStock() call above, so we dont need to check for the result of removeStock()
            $product->removeStock($this->_request->quantity[$product->id]);
        }

        $purchaser->update(['balance' => $remaining_balance]);

        $transaction = new Transaction();
        $transaction->purchaser_id = $purchaser->id;
        $transaction->cashier_id = auth()->id();
        $transaction->products = implode(', ', $transaction_products);
        $transaction->total_price = $total_price;
        $transaction->save();

        $this->_result = 6;
        $this->_message = 'Order #' . $transaction->id . '. ' . $purchaser->full_name . ' now has $' . number_format(round($remaining_balance, 2), 2);
        $this->_total_price = $total_price;

    }

    public function getResult(): int
    {
        return $this->_result;
    }

    public function getMessage(): string
    {
        return $this->_message;
    }

    public function getTotalPrice(): float
    {
        return $this->_total_price;
    }

    public function redirect()
    {
        switch ($this->getResult()) {
            case 0:
                return redirect('/')->with('error', $this->getMessage());
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
                return redirect()->back()->withInput()->with('error', $this->getMessage());
            case 6:
                return redirect('/')->with('success', $this->getMessage());
        }
    }
}
