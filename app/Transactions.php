<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Transactions extends Model
{
    use QueryCacheable;

    protected $cacheFor = 180;
    protected $fillable = ['products', 'status'];

    // TODO
    // protected $casts = [
    //     'purchaser_id' => User::class,
    //     'cashier_id' => User::class
    // ];
}
