<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;
use App\Http\Controllers\TransactionController;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/** @method static Transaction find */
class Transaction extends Model
{
    use QueryCacheable;
    use HasFactory;

    protected $cacheFor = 180;

    protected $fillable = [
        'products', // TODO: can we auto explode(',') this?
        'status',
    ];

    protected $casts = [
        'status' => 'boolean', // TODO: Rename this to "returned"
    ];

    protected $with = [
        'purchaser',
        'cashier',
    ];

    public function purchaser()
    {
        return $this->hasOne(User::class, 'id', 'purchaser_id');
    }

    public function cashier()
    {
        return $this->hasOne(User::class, 'id', 'cashier_id');
    }

    // TODO: when whole transaction is returned, manually deserialize and reserialize all products with return value of their original quantity
    // to keep consistency with item sales chart
    public function return()
    {
        // This should never happen, but a good security measure
        if ($this->isReturned()) {
            return redirect()->back()->with('error', 'That order has already been fully returned.');
        }

        $total_tax = $total_price = 0;
        $purchaser = $this->purchaser;

        // Loop through products from the order and deserialize them to get their prices & taxes etc when they were purchased
        $transaction_products = explode(', ', $this->products);
        foreach ($transaction_products as $product) {
            $product_metadata = TransactionController::deserializeProduct($product, false);

            if ($product_metadata['pst'] == 'null') {
                $total_tax = $product_metadata['gst'];
            } else {
                $total_tax = ($product_metadata['gst'] + $product_metadata['pst']) - 1;
            }

            $total_price += ($product_metadata['price'] * $product_metadata['quantity']) * $total_tax;
        }

        $purchaser->update(['balance' => ($purchaser->balance + $total_price)]);
        $this->update(['status' => true]);

        return redirect()->back()->with('success', 'Successfully returned order #' . $this->id . ' for ' . $purchaser->full_name);
    }

    public function returnItem(int $item_id)
    {
        if ($this->isReturned()) {
            return redirect()->back()->with('error', 'That order has already been returned, so you cannot return an item from it.');
        }

        $purchaser = $this->purchaser;
        $user_balance = $purchaser->balance;
        $found = false;

        // Loop order products until we find the matching id
        $products = explode(', ', $this->products);
        foreach ($products as $product_count) {
            // Only proceed if this is the requested item id
            if (strtok($product_count, '*') == $item_id) {
                $order_product = TransactionController::deserializeProduct($product_count);
                $found = true;

                // If it has not been returned more times than it was purchased, then ++ the returned count and refund the original cost + taxes
                if (!($order_product['returned'] < $order_product['quantity'])) {
                    return redirect()->back()->with('error', 'That item has already been returned the maximum amount of times for that order.');
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
                    self::serializeProduct($order_product['id'], $order_product['quantity'], $order_product['price'], $order_product['gst'], $order_product['pst'], $order_product['returned']),
                    $products
                );
                // Update their balance
                $purchaser->update(['balance' => $user_balance += ($order_product['price'] * $total_tax)]);
                // Now insert the funky replaced string where it was originally
                $this->update(['products' => implode(', ', $updated_products)]);
                return redirect()->back()->with('success', 'Successfully returned x1 ' . $order_product['name'] . ' for order #' . $this->id . '.');
            }
        }

        if ($found === false) {
            return redirect()->back()->with('error', 'That item was not in the original order.');
        }
    }

    public function isReturned(): bool
    {
        return $this->getReturnStatus() == 1;
    }

    public function getReturnStatus(): int
    {
        if ($this->status) {
            return 1;
        }

        $products_returned = 0;
        $product_count = 0;

        $products = explode(', ', $this->products);
        foreach ($products as $product) {
            $product_info = TransactionController::deserializeProduct($product, false);
            if ($product_info['returned'] >= $product_info['quantity']) {
                $products_returned++;
            } else {
                if ($product_info['returned'] > 0) {
                    // semi returned if at least one product has a returned value
                    return 2;
                }
            }

            $product_count++;
        }

        if ($products_returned >= $product_count) {
            // incase something went wrong and the status wasnt updated earlier, do it now
            $this->update(['status' => true]);
            return 1;
        }

        return 0;
    }
}
