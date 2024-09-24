<?php

namespace App\Models;

use App\Enums\PayoutStatus;
use Cknow\Money\Money;
use App\Enums\OrderStatus;
use App\Enums\FamilyMemberRole;
use App\Enums\UserLimitDuration;
use App\Concerns\Timeline\HasTimeline;
use Cknow\Money\Casts\MoneyIntegerCast;
use App\Concerns\Timeline\TimelineEntry;
use Lab404\Impersonate\Models\Impersonate;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Laravel\Cashier\Billable;

class User extends Authenticatable implements HasTimeline
{
    use Billable;

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
        'balance' => MoneyIntegerCast::class,
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function family(): HasOneThrough
    {
        return $this->hasOneThrough(Family::class, FamilyMember::class, null, 'id', null, 'family_id');
    }

    public function familyMember(): HasOne
    {
        return $this->hasOne(FamilyMember::class);
    }

    public function familyRole(): FamilyMemberRole
    {
        return $this->familyMember?->role;
    }

    public function isFamilyAdmin(?Family $family = null): bool
    {
        return $this->family?->is($family ?? $this->family) && $this->familyRole() === FamilyMemberRole::Admin;
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'purchaser_id');
    }

    public function brokeredOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'cashier_id');
    }

    public function activityRegistrations(): HasMany
    {
        return $this->hasMany(ActivityRegistration::class);
    }

    public function brokeredActivityRegistrations(): HasMany
    {
        return $this->hasMany(ActivityRegistration::class, 'cashier_id');
    }

    public function giftCards(): HasManyThrough
    {
        return $this->hasManyThrough(GiftCard::class, GiftCardAssignment::class, 'user_id', 'id', 'id', 'gift_card_id');
    }

    public function rotations(): BelongsToMany
    {
        return $this->belongsToMany(Rotation::class);
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
        return UserLimit::firstOrCreate([
            'user_id' => $this->id,
            'category_id' => $category->id,
        ], [
            'limit' => Money::parse(-1_00),
            'duration' => UserLimitDuration::Daily,
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
            ->add(...$this->orders->map->total_price)
            ->add(...$this->activityRegistrations->map->total_price);
    }

    public function findSpentInCash(): Money
    {
        return Money::parse(0)
            ->add(...$this->orders->map->purchaser_amount)
            ->add(...$this->activityRegistrations->map->total_price);
    }

    /**
     * Find how much a user has returned in total.
     * This will see if a whole order has been returned, or if not, check all items in an unreturned order.
     */
    public function findReturned(): Money
    {
        $returned = Money::parse(0);

        $this->orders->where('status', '!=', OrderStatus::NotReturned)->each(function (Order $order) use (&$returned) {
            if ($order->isReturned()) {
                $returned = $returned->add($order->total_price);
                return;
            }

            $returned = $returned->add($order->getReturnedTotal());
        });

        $returned = $returned->add(
            ...$this->activityRegistrations
                ->where('returned', true)
                ->map->total_price
        );

        return $returned;
    }

    public function findReturnedToCash(): Money
    {
        $returned = Money::parse(0);

        $this->orders->where('status', '!=', OrderStatus::NotReturned)->each(function (Order $order) use (&$returned) {
            if ($order->isReturned()) {
                $returned = $returned->add($order->purchaser_amount);
                return;
            }

            $returned = $returned->add($order->getReturnedTotalToCash());
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
     * Taking their amount spent in cash and subtracting the amount they have returned to cash and sum of their payouts.
     */
    public function findOwing(): Money
    {
        return $this->findSpentInCash()
            ->subtract($this->findReturnedToCash())
            ->subtract($this->findPaidOut());
    }

    public function findPaidOut(): Money
    {
        return Money::sum(Money::parse(0), ...$this->payouts->where('status', PayoutStatus::Paid)->map->amount);
    }

    public function canImpersonate()
    {
        return $this->role->superuser;
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
        foreach ($this->orders()->with('cashier')->get() as $order) {
            $events[] = new TimelineEntry(
                description: "Purchased {$order->total_price}",
                emoji: '🛒',
                time: $order->created_at,
                actor: $order->cashier,
                link: route('orders_view', $order),
            );
        }

        foreach ($this->activityRegistrations()->with('activity', 'cashier')->get() as $activityRegistration) {
            $events[] = new TimelineEntry(
                description: "Registered for {$activityRegistration->activity->name}",
                emoji: '🎟️',
                time: $activityRegistration->created_at,
                actor: $activityRegistration->cashier,
                link: route('activities_view', $activityRegistration->activity),
            );
        }

        foreach ($this->payouts->where('status', PayoutStatus::Paid) as $payout) {
            $events[] = new TimelineEntry(
                description: "Paid out {$payout->amount}",
                emoji: '💸',
                time: $payout->created_at,
                actor: $payout->creator,
            );
        }

        usort($events, fn ($a, $b) => $a->time->lt($b->time) ? -1 : 1);

        return array_merge($timeline, $events);
    }
}
