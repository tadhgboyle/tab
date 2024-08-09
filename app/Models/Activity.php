<?php

namespace App\Models;

use App\Concerns\Timeline\HasTimeline;
use App\Concerns\Timeline\TimelineEntry;
use Cknow\Money\Money;
use App\Helpers\TaxHelper;
use Carbon\CarbonInterface;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Activity extends Model implements HasTimeline
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'category_id',
        'location',
        'description',
        'unlimited_slots',
        'slots',
        'price',
        'pst',
        'start',
        'end',
    ];

    protected $casts = [
        'name' => 'string',
        'location' => 'string',
        'description' => 'string',
        'unlimited_slots' => 'boolean',
        'slots' => 'integer',
        'price' => MoneyIntegerCast::class,
        'pst' => 'boolean',
    ];

    protected $dates = [
        'start',
        'end',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function slotsAvailable(): int
    {
        if ($this->unlimited_slots) {
            return -1;
        }

        return $this->slots - $this->attendants->count();
    }

    public function hasSlotsAvailable(int $count = 1): bool
    {
        if ($this->unlimited_slots) {
            return true;
        }

        $current_attendees = $this->attendants->count();
        return ($this->slots - ($current_attendees + $count)) >= 0;
    }

    public function getPriceAfterTax(): Money
    {
        return TaxHelper::calculateFor($this->price, 1, $this->pst);
    }

    public function attendants(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, ActivityRegistration::class, null, 'id', null, 'user_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(ActivityRegistration::class);
    }

    public function isAttending(User $user): bool
    {
        return $this->attendants->contains($user);
    }

    public function getStatusHtml(): string
    {
        if ($this->end->isPast()) {
            return '<span class="tag is-medium">🕐 Over</span>';
        }

        if ($this->start->isPast()) {
            return '<span class="tag is-medium">✅ In Progress</span>';
        }

        $starts_in_words = $this->start->diffForHumans(now(), CarbonInterface::DIFF_ABSOLUTE, false, 3);
        return '<span class="tag is-medium">🔮 Starts in ' . $starts_in_words . '</span>';
    }

    public function timeline(): array
    {
        $timeline = [
            new TimelineEntry(
                description: 'Created',
                emoji: '📅',
                time: $this->created_at,
            ),
        ];

        if ($this->start->isPast()) {
            $timeline[] = new TimelineEntry(
                description: 'Started',
                emoji: '🚀',
                time: $this->start,
            );
        }

        if ($this->end->isPast()) {
            $timeline[] = new TimelineEntry(
                description: 'Ended',
                emoji: '🏁',
                time: $this->end,
            );
        }

        foreach ($this->registrations()->with('user', 'cashier')->get() as $registration) {
            $timeline[] = new TimelineEntry(
                description: "Registered {$registration->user->full_name}",
                emoji: '🎟️',
                time: $registration->created_at,
                actor: $registration->cashier,
            );
        }

        usort($timeline, fn ($a, $b) => $a->time <=> $b->time);

        return $timeline;
    }
}
