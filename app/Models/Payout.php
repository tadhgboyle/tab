<?php

namespace App\Models;

use App\Enums\PayoutStatus;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payout extends Model
{
    use HasFactory;

    protected $casts = [
        'amount' => MoneyIntegerCast::class,
        'status' => PayoutStatus::class,
    ];

    protected $fillable = [
        'status',
        'type',
        'amount',
        'user_id',
        'creator_id',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
