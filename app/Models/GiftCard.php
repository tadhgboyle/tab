<?php

namespace App\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GiftCard extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'original_balance' => MoneyIntegerCast::class,
        'remaining_balance' => MoneyIntegerCast::class,
        // 'code' => 'encrypted', // this does not work in a `GiftCard::firstWhere('code', encrypt(request()->query('code')));` query
    ];

    public function issuer(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'issuer_id');
    }

    public function uses(): HasMany
    {
        return $this->hasMany(Transaction::class, 'gift_card_id', 'id')
            ->orderBy('created_at', 'DESC');
    }

    public function fullyUsed(): bool
    {
        return $this->remaining_balance->isZero();
    }
}
