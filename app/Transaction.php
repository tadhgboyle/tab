<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Transaction extends Model
{
    use QueryCacheable;

    protected $cacheFor = 180;
    protected $fillable = ['products', 'status'];

    // TODO: See if we can either 1. rename these to remove the _id 2. use getters instead (just so it doesnt look weird)
    protected $casts = [
        'purchaser_id' => User::class,
        'cashier_id' => User::class
    ];
}
