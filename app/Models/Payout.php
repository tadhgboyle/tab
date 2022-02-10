<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Payout extends Model
{
    use QueryCacheable;
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'identifier' => 'string',
        'amount' => 'float',
    ];

    protected $dates = [
        'created_at',
    ];

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
