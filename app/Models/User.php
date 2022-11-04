<?php

namespace App\Models;

use Carbon\Carbon;
use JetBrains\PhpStorm\Pure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

    private Collection $_activity_transactions;
    private Collection $_activities;

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

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'purchaser_id');
    }

    public function rotations(): BelongsToMany
    {
        return $this->belongsToMany(Rotation::class)->distinct();
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }

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

    public function getActivities(): Collection
    {
        if (!isset($this->_activities)) {
            $this->_activities = new Collection();

            $this->getActivityTransactions()->each(function ($activity) {
                $this->_activities->add([
                    'created_at' => Carbon::parse($activity->created_at),
                    'cashier' => self::find($activity->cashier_id),
                    'activity' => Activity::find($activity->activity_id),
                    'total_price' => $activity->total_price,
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
        return (float) ($this->transactions->sum('total_price') + $this->getActivityTransactions()->sum('total_price'));
    }

    /**
     * Find how much a user has returned in total.
     * This will see if a whole order has been returned, or if not, check all items in an unreturned order.
     */
    public function findReturned(): float
    {
        $returned = 0.00;

        $this->transactions->each(function (Transaction $transaction) use (&$returned) {
            if ($transaction->returned) {
                $returned += $transaction->total_price;
                return;
            }

            foreach ($transaction->products as $product) {
                if ($product->returned === 0) {
                    continue;
                }

                $tax = $product->gst;
                if ($product->pst !== null) {
                    $tax += ($product->pst - 1);
                }

                $returned += ($product->returned * $product->price * $tax);
            }
        });

        $returned += $this->getActivityTransactions()->where('returned', true)->sum('total_price');

        return (float) $returned;
    }

    /**
     * Find how much money a user owes.
     * Taking their amount spent and subtracting the amount they have returned.
     */
    public function findOwing(): float
    {
        return ($this->findSpent() - $this->findReturned()) - $this->payouts()->sum('amount');
    }
}
