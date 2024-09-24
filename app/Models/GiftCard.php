<?php

namespace App\Models;

use App\Enums\GiftCardStatus;
use Cknow\Money\Money;
use App\Helpers\Permission;
use Illuminate\Support\Str;
use App\Enums\GiftCardAdjustmentType;
use App\Concerns\Timeline\HasTimeline;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use App\Concerns\Timeline\TimelineEntry;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GiftCard extends Model implements HasTimeline
{
    use HasFactory;

    protected $casts = [
        'original_balance' => MoneyIntegerCast::class,
        'remaining_balance' => MoneyIntegerCast::class,
        // 'code' => 'encrypted', // this does not work in a `GiftCard::firstWhere('code', encrypt(request()->query('code')));` query
        'expires_at' => 'date',
    ];

    protected $fillable = [
        'remaining_balance',
    ];

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(GiftCardAdjustment::class);
    }

    public function uses(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(GiftCardAssignment::class)->with('user', 'assigner');
    }

    public function expired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    public function code(): string
    {
        return hasPermission(Permission::SETTINGS_GIFT_CARDS_MANAGE)
            ? $this->code
            : '••••••' . $this->last4();
    }

    public function last4(): string
    {
        return Str::take($this->code, -4);
    }

    public function fullyUsed(): bool
    {
        return $this->remaining_balance->isZero();
    }

    public function canBeUsedBy(User $user): bool
    {
        return $this->assignments->isEmpty() || $this->assignments->map->user->contains($user);
    }

    public function usageBy(User $user): Money
    {
        return Money::sum(Money::parse(0), ...$user->orders()->where('gift_card_id', $this->id)->get()->map->gift_card_amount);
    }

    public function getStatusAttribute(): GiftCardStatus
    {
        if ($this->expired()) {
            return GiftCardStatus::Expired;
        }

        return GiftCardStatus::Active;
    }

    public function timeline(): array
    {
        $timeline = [
            new TimelineEntry(
                description: "Created with a balance of {$this->original_balance}",
                emoji: '🎁',
                time: $this->created_at,
                actor: $this->issuer,
            ),
        ];

        $events = [];

        foreach ($this->adjustments()->with('order', 'order.purchaser')->get() as $adjustment) {
            $events[] = new TimelineEntry(
                description: $adjustment->type === GiftCardAdjustmentType::Charge
                    ? "Used to purchase {$adjustment->amount} worth of items"
                    : "Returned {$adjustment->amount}",
                emoji: '🧾',
                time: $adjustment->created_at,
                actor: $adjustment->order->purchaser,
                link: route('orders_view', $adjustment->order),
            );
        }

        foreach ($this->assignments()->withTrashed()->with('user', 'assigner', 'deletedBy')->get() as $assignment) {
            $events[] = new TimelineEntry(
                description: "Assigned to {$assignment->user->full_name}",
                emoji: '👤',
                time: $assignment->created_at,
                actor: $assignment->assigner,
                link: route('users_view', $assignment->user),
            );

            if ($assignment->trashed()) {
                $events[] = new TimelineEntry(
                    description: "Unassigned from {$assignment->user->full_name}",
                    emoji: '👤',
                    time: $assignment->deleted_at,
                    actor: $assignment->deletedBy,
                    link: route('users_view', $assignment->user),
                );
            }
        }

        if ($this->expired()) {
            $timeline[] = new TimelineEntry(
                description: 'Expired',
                emoji: '🕐',
                time: $this->expires_at,
            );
        }

        usort($events, fn ($a, $b) => $a->time->lt($b->time) ? -1 : 1);

        return array_merge($timeline, $events);
    }
}
