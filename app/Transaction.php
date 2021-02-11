<?php

namespace App;

use App\Http\Controllers\TransactionController;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Transaction extends Model
{
    
    use QueryCacheable;

    protected $cacheFor = 180;

    protected $fillable = [
        'products',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean' // TODO: Rename this to "returned"
    ];

    public function purchaser()
    {
        return $this->hasOne(User::class, 'id', 'purchaser_id');
    }

    public function cashier()
    {
        return $this->hasOne(User::class, 'id', 'cashier_id');
    }

    public function checkReturned(): int
    {
        if ($this->status == 1) {
            return 1;
        } else {
            $products_returned = 0;
            $product_count = 0;

            $products = explode(", ", $this->products);
            foreach ($products as $product) {
                $product_info = TransactionController::deserializeProduct($product, false);
                if ($product_info['returned'] >= $product_info['quantity']) {
                    $products_returned++;
                } else if ($product_info['returned'] > 0) {
                    // semi returned if at least one product has a returned value
                    return 2;
                }
                $product_count++;
            }
            if ($products_returned >= $product_count) {
                // incase something went wrong and the status wasnt updated earlier, do it now
                $this->update(['status' => '1']);
                return 1;
            }

            return 0;
        }
    }
}
