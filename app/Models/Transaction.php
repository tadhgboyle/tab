<?php

namespace App\Models;

use Cknow\Money\Money;
use App\Helpers\TaxHelper;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    public const STATUS_NOT_RETURNED = 'NOT_RETURNED';
    public const STATUS_FULLY_RETURNED = 'FULLY_RETURNED';
    public const STATUS_PARTIAL_RETURNED = 'PARTIAL_RETURNED';

    use HasFactory;

    protected $fillable = [
        'returned',
    ];

    protected $casts = [
        'total_price' => MoneyIntegerCast::class,
        'purchaser_amount' => MoneyIntegerCast::class,
        'gift_card_amount' => MoneyIntegerCast::class,
        'credit_amount' => MoneyIntegerCast::class,
        'returned' => 'boolean',
    ];

    protected $with = [
        'products',
    ];

    public function purchaser(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rotation(): BelongsTo
    {
        return $this->belongsTo(Rotation::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(TransactionProduct::class);
    }

    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(GiftCard::class);
    }

    public function credits(): HasMany
    {
        return $this->hasMany(Credit::class, 'transaction_id');
    }

    public function creditableAmount(): Money
    {
        return Money::parse(0_00)
            ->add($this->gift_card_amount)
            ->add($this->credit_amount);
    }

    public function getReturnedTotal(): Money
    {
        if ($this->isReturned()) {
            return $this->total_price;
        }

        if ($this->getReturnStatus() === self::STATUS_NOT_RETURNED) {
            return Money::parse(0);
        }

        return $this->products
            ->where('returned', '>=', 1)
            ->reduce(function (Money $carry, TransactionProduct $product) {
                // todo use ::forTransactionProduct?
                return $carry->add(TaxHelper::calculateFor($product->price, $product->returned, $product->pst !== null, [
                    'pst' => $product->pst,
                    'gst' => $product->gst,
                ]));
            }, Money::parse(0));
    }

    public function isReturned(): bool
    {
        return $this->getReturnStatus() === self::STATUS_FULLY_RETURNED;
    }

    public function getReturnStatus(): string
    {
        if ($this->returned) {
            return self::STATUS_FULLY_RETURNED;
        }

        if ($this->products->sum->returned > 0) {
            return self::STATUS_PARTIAL_RETURNED;
        }

        return self::STATUS_NOT_RETURNED;
    }
}
