<?php

namespace App\Models;

use App\Concerns\Timeline\HasTimeline;
use App\Concerns\Timeline\TimelineEntry;
use App\Helpers\Permission;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GiftCard extends Model implements HasTimeline
{
    use HasFactory;

    protected $casts = [
        'original_balance' => MoneyIntegerCast::class,
        'remaining_balance' => MoneyIntegerCast::class,
        // 'code' => 'encrypted', // this does not work in a `GiftCard::firstWhere('code', encrypt(request()->query('code')));` query
    ];

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function uses(): HasMany
    {
        return $this->hasMany(Transaction::class)->orderBy('created_at', 'DESC');
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

        foreach ($this->uses as $transaction) {
            $timeline[] = new TimelineEntry(
                description: "Used to purchase {$transaction->gift_card_amount} worth of items",
                emoji: '🧾',
                time: $transaction->created_at,
                actor: $transaction->purchaser,
            );
        }

        return $timeline;
    }
}
