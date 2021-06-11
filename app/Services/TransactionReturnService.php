<?php

namespace App\Services;

use App\Helpers\ProductHelper;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\TransactionController;

class TransactionReturnService extends TransactionService
{
    public const RESULT_ALREADY_RETURNED = 1;
    public const RESULT_ITEM_RETURNED_MAX_TIMES = 2;
    public const RESULT_ITEM_NOT_IN_ORDER = 3;
    public const RESULT_SUCCESS = 4;

    public function __construct(Transaction | int $transaction)
    {
        if ($transaction instanceof Transaction) {
            $this->_transaction = $transaction;
            return;
        }

        $transaction = Transaction::find($transaction);

        if ($transaction == null) {
            return redirect()->route('orders_list')->with('error', 'No transaction found with that ID.')->send();
        }

        $this->_transaction = $transaction;
    }

    // TODO: when whole transaction is returned, manually deserialize and reserialize all products with return value of their original quantity
    // to keep consistency with item sales chart
    public function return()
    {
        // This should never happen, but a good security measure
        if ($this->_transaction->isReturned()) {
            $this->_result = self::RESULT_ALREADY_RETURNED;
            $this->_message = 'That order has already been fully returned.';
            return $this;
        }

        $total_tax = $total_price = 0;
        $purchaser = $this->_transaction->purchaser;

        // Loop through products from the order and deserialize them to get their prices & taxes etc when they were purchased
        $transaction_products = explode(', ', $this->_transaction->products);
        foreach ($transaction_products as $product) {
            $product_metadata = ProductHelper::deserializeProduct($product, false);

            if ($product_metadata['pst'] == 'null') {
                $total_tax = $product_metadata['gst'];
            } else {
                $total_tax = ($product_metadata['gst'] + $product_metadata['pst']) - 1;
            }

            $total_price += ($product_metadata['price'] * $product_metadata['quantity']) * $total_tax;
        }

        $purchaser->update(['balance' => ($purchaser->balance + $total_price)]);
        $this->_transaction->update(['status' => true]);

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = 'Successfully returned order #' . $this->_transaction->id . ' for ' . $purchaser->full_name;
        return $this;
    }

    public function returnItem(int $item_id)
    {
        if ($this->_transaction->isReturned()) {
            $this->_result = self::RESULT_ALREADY_RETURNED;
            $this->_message = 'That order has already been returned, so you cannot return an item from it.';
            return $this;
        }

        $purchaser = $this->_transaction->purchaser;
        $user_balance = $purchaser->balance;
        $found = false;

        // Loop order products until we find the matching id
        $products = explode(', ', $this->_transaction->products);
        foreach ($products as $product_count) {
            // Only proceed if this is the requested item id
            if (strtok($product_count, '*') == $item_id) {
                $order_product = ProductHelper::deserializeProduct($product_count);
                $found = true;

                // If it has not been returned more times than it was purchased, then ++ the returned count and refund the original cost + taxes
                if (!($order_product['returned'] < $order_product['quantity'])) {
                    $this->_result = self::RESULT_ITEM_RETURNED_MAX_TIMES;
                    $this->_message = 'That item has already been returned the maximum amount of times for that order.';
                    return $this;
                }

                $order_product['returned']++;

                $total_tax = 0.00;

                // Check taxes and apply correct %
                if ($order_product['pst'] == 'null') {
                    $total_tax = $order_product['gst'];
                } else {
                    $total_tax = (($order_product['pst'] + $order_product['gst']) - 1);
                }

                // Gets funky now. find the exact string of the original item from the string of order items and replace with the new string where the R value is ++
                $updated_products = str_replace(
                    $product_count,
                    ProductHelper::serializeProduct($order_product['id'], $order_product['quantity'], $order_product['price'], $order_product['gst'], $order_product['pst'], $order_product['returned']),
                    $products
                );
                // Update their balance
                $purchaser->update(['balance' => $user_balance += ($order_product['price'] * $total_tax)]);
                // Now insert the funky replaced string where it was originally
                $this->_transaction->update(['products' => implode(', ', $updated_products)]);

                $this->_result = self::RESULT_SUCCESS;
                $this->_message = 'Successfully returned x1 ' . $order_product['name'] . ' for order #' . $this->_transaction->id . '.';
                return $this;
            }
        }

        if ($found === false) {
            $this->_result = self::RESULT_ITEM_NOT_IN_ORDER;
            $this->_message = 'That item was not in the original order.';
        }

        return $this;
    }

    public function redirect(): RedirectResponse
    {
        switch ($this->getResult()) {
            case self::RESULT_SUCCESS:
                return redirect()->back()->with('success', $this->getMessage());
            default:
                return redirect()->back()->with('error', $this->getMessage());
        }
    }
}
