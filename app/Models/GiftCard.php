<?php

namespace App\Models;

use App\Concerns\Timeline\HasTimeline;
use App\Concerns\Timeline\TimelineEntry;
use App\Helpers\Permission;
use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function uses(): HasMany
    {
        return $this->hasMany(Transaction::class)->orderBy('created_at', 'DESC');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('created_at');
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
        return substr($this->code, -4);
    }

    public function fullyUsed(): bool
    {
        return $this->remaining_balance->isZero();
    }

    public function amountUsed(): Money
    {
        return $this->original_balance->subtract($this->remaining_balance);
    }


    public function canBeUsedBy(User $user): bool
    {
        return $this->users->isEmpty() || $this->users->contains($user);
    }

    public function usageBy(User $user): Money
    {
        // TODO: without('products')
        return Money::sum(Money::parse(0), ...$user->transactions->where('gift_card_id', $this->id)->map->gift_card_amount);
    }

    public function getStatusHtml(): string
    {
        if ($this->expired()) {
            return '<span class="tag is-medium">🕐 Expired</span>';
        }

        return '<span class="tag is-medium">✅ Active</span>';
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

        foreach ($this->uses as $transaction) {
            $events[] = new TimelineEntry(
                description: "Used to purchase {$transaction->gift_card_amount} worth of items",
                emoji: '🧾',
                time: $transaction->created_at,
                actor: $transaction->purchaser,
            );
        }

        foreach ($this->users as $user) {
            $events[] = new TimelineEntry(
                description: "Assigned to {$user->full_name}",
                emoji: '👤',
                time: $user->pivot->created_at,
                actor: $user,
            );
        }

        usort($events, fn ($a, $b) => $a->time->lt($b->time) ? -1 : 1);

        return array_merge($timeline, $events);
    }
}
