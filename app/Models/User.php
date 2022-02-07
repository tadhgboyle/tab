<?php

namespace App\Models;

use Carbon\Carbon;
use JetBrains\PhpStorm\Pure;
use App\Helpers\ProductHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\QueryCache\Traits\QueryCacheable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use QueryCacheable;
    use HasFactory;
    use SoftDeletes;

    protected int $cacheFor = 180;

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
        'balance' => 'float'
    ];

    protected $with = [
        'role',
    ];

    public function role(): HasOne
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }

    public function rotations(): BelongsToMany
    {
        return $this->belongsToMany(Rotation::class)->distinct();
    }

    private Collection $_activity_transactions;
    private Collection $_transactions;
    private Collection $_activities;

    // TODO: add a "root" user? only they can edit superadmin roles

    // TODO: finish
    // public function limits(): HasMany
    // {
    //     return $this->hasMany(UserLimits::class);
    // }

    // public function getCategoryLimit(string $category): float
    // {
    //     foreach ($this->limits as $limit) {
    //         if ($limit->category == $category) {
    //             return $limit->duration;
    //         }
    //     }
    //     return -1;
    // }

    #[Pure]
    public function hasPermission($permission): bool
    {
        return $this->role->hasPermission($permission);
    }

    public function getActivityTransactions(): Collection
    {
        return $this->_activity_transactions ??= DB::table('activity_transactions')
            ->where('user_id', $this->id)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function getTransactions(): Collection
    {
        return $this->_transactions ??= Transaction::query()
            ->where('purchaser_id', $this->id)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function getActivities(): Collection
    {
        if (!isset($this->_activities)) {
            $this->_activities = new Collection();

            $this->getActivityTransactions()->each(function ($activity) {
                $this->_activities->add([
                    'created_at' => Carbon::parse($activity->created_at),
                    'cashier' => self::find($activity->cashier_id),
                    'activity' => Activity::find($activity->activity_id),
                    'price' => $activity->activity_price,
                    'returned' => $activity->returned,
                ]);
            });
        }

        return $this->_activities;
    }

    /**
     * Find how much a user has spent in total.
     * Does not factor in returned items/orders.
     */
    public function findSpent(): float
    {
        return (float) ($this->getTransactions()->sum('total_price') + $this->getActivityTransactions()->sum('total_price'));
    }

    /**
     * Find how much a user has returned in total.
     * This will see if a whole order has been returned, or if not, check all items in an unreturned order.
     */
    public function findReturned(): float
    {
        $returned = 0.00;

        $transactions = $this->getTransactions();
        foreach ($transactions as $transaction) {
            if ($transaction->returned) {
                $returned += $transaction->total_price;
                continue;
            }

            $transaction_products = explode(', ', $transaction->products);
            foreach ($transaction_products as $transaction_product) {
                $product = ProductHelper::deserializeProduct($transaction_product, false);
                if ($product['returned'] < 1) {
                    continue;
                }

                $tax = $product['gst'];
                if ($product['pst'] !== 'null') {
                    $tax += ($product['pst'] - 1);
                }

                $returned += ($product['returned'] * $product['price'] * $tax);
            }
        }

        $returned += $this->getActivityTransactions()->where('returned', true)->sum('total_price');

        return (float) $returned;
    }

    /**
     * Find how much money a user owes.
     * Taking their amount spent and subtracting the amount they have returned.
     */
    public function findOwing(): float
    {
        return $this->findSpent() - $this->findReturned();
    }
}
