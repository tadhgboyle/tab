<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Transactions extends Model
{
    use QueryCacheable;

    protected $cacheFor = 180;
}
