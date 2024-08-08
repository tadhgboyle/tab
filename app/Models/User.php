<?php

namespace App\Models;

use Cknow\Money\Money;
use App\Concerns\Timeline\HasTimeline;
use Cknow\Money\Casts\MoneyIntegerCast;
use App\Concerns\Timeline\TimelineEntry;
use Lab404\Impersonate\Models\Impersonate;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements HasTimeline
{
    use HasFactory;
    use SoftDeletes;
    use Impersonate;

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

    protected $with = [
        // 'userLimits',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'purchaser_id')->with('products');
    }

    public function activityRegistrations(): HasMany
    {
        return $this->hasMany(ActivityRegistration::class);
    }

    public function giftCards(): BelongsToMany
    {
        return $this->belongsToMany(GiftCard::class);
    }

    public function rotations(): BelongsToMany
    {
        // TODO why is this distinct?
        return $this->belongsToMany(Rotation::class)->distinct();
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }

    public function userLimits(): HasMany
    {
        return $this->hasMany(UserLimit::class);
    }

    public function limitFor(Category $category): UserLimit
    {
        $userLimit = $this->userLimits->where('category_id', $category->id)->first();
        if ($userLimit) {
            return $userLimit;
        }

        return UserLimit::create([
            'user_id' => $this->id,
            'category_id' => $category->id,
            'limit' => Money::parse(-1_00),
            'duration' => UserLimit::LIMIT_DAILY,
        ]);
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
            if ($transaction->isReturned()) {
                $returned = $returned->add($transaction->total_price);
                return;
            }

            if ($transaction->status === Transaction::STATUS_NOT_RETURNED) {
                return;
            }

            $returned = $returned->add($transaction->getReturnedTotal());
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
     * Taking their amount spent and subtracting the amount they have returned and sum of their payouts.
     */
    public function findOwing(): Money
    {
        return $this->findSpent()
            ->subtract($this->findReturned())
            ->subtract($this->findPaidOut());
    }

    public function findPaidOut(): Money
    {
        return Money::sum(Money::parse(0), ...$this->payouts->map->amount);
    }

    public function canImpersonate()
    {
        return $this->role->superuser;
    }

    public function canBeImpersonated()
    {
        return $this->role->staff;
    }

    public function timeline(): array
    {
        $timeline = [
            new TimelineEntry(
                description: 'Created',
                emoji: '👶',
                time: $this->created_at,
            ),
        ];

        $events = [];
        foreach ($this->transactions as $transaction) {
            $events[] = new TimelineEntry(
                description: "Purchased {$transaction->total_price}",
                emoji: '🛒',
                time: $transaction->created_at,
                actor: $transaction->cashier,
            );
        }

        foreach ($this->activityRegistrations as $activityRegistration) {
            $events[] = new TimelineEntry(
                description: "Registered for {$activityRegistration->activity->name}",
                emoji: '🎟️',
                time: $activityRegistration->created_at,
                actor: $activityRegistration->cashier,
            );
        }

        foreach ($this->payouts as $payout) {
            $events[] = new TimelineEntry(
                description: "Paid out {$payout->amount}",
                emoji: '💸',
                time: $payout->created_at,
                actor: $payout->cashier,
            );
        }

        usort($events, fn ($a, $b) => $a->time->lt($b->time) ? -1 : 1);

        return array_merge($timeline, $events);
    }
}
