<?php

namespace App\Models;

use Cknow\Money\Money;
use App\Helpers\TaxHelper;
use JetBrains\PhpStorm\Pure;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'full_name',
        'username',
        'balance',
        'password',
        'role_id'
    ];

    protected $casts = [
        'full_name' => 'string',
        'username' => 'string',
        'balance' => MoneyIntegerCast::class,
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'purchaser_id');
    }

    public function activityRegistrations(): HasMany
    {
        return $this->hasMany(ActivityRegistration::class, 'user_id', 'id');
    }

    public function rotations(): BelongsToMany
    {
        return $this->belongsToMany(Rotation::class)->distinct();
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }

    public function hasPermission($permission): bool
    {
        return $this->role->hasPermission($permission);
    }

    /**
     * Find how much a user has spent in total.
     * Does not factor in returned items/orders.
     */
    public function findSpent(): Money
    {
        return Money::parse(0)
            ->add(...$this->transactions->map->total_price)
            ->add(...$this->activityRegistrations->map->total_price);
    }

    /**
     * Find how much a user has returned in total.
     * This will see if a whole order has been returned, or if not, check all items in an unreturned order.
     */
    public function findReturned(): Money
    {
        $returned = Money::parse(0);

        $this->transactions->each(function (Transaction $transaction) use (&$returned) {
            if ($transaction->returned) {
                $returned = $returned->add($transaction->total_price);
                return;
            }

            foreach ($transaction->products->filter(fn (TransactionProduct $product) => $product->returned > 0) as $product) {
                $returned = $returned->add(TaxHelper::calculateFor($product->price, $product->returned, $product->pst !== null, [
                    'gst' => $product->gst,
                    'pst' => $product->pst,
                ]));
            }
        });

        $returned = $returned->add(
            ...$this->activityRegistrations
            ->where('returned', true)
            ->map->total_price
        );

        return $returned;
    }

    /**
     * Find how much money a user owes.
     * Taking their amount spent and subtracting the amount they have returned.
     */
    public function findOwing(): Money
    {
        return $this->findSpent()
            ->subtract($this->findReturned())
            ->subtract(...$this->payouts->map->amount);
    }
}
