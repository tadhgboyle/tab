<?php

namespace App\Models;

use Cknow\Money\Money;
use App\Helpers\TaxHelper;
use Carbon\CarbonInterface;
use App\Concerns\Timeline\HasTimeline;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Concerns\Timeline\TimelineEntry;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'start' => 'datetime',
        'end' => 'datetime',
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

        return $this->slots - $this->registrations()->count();
    }

    public function hasSlotsAvailable(int $count = 1): bool
    {
        if ($this->unlimited_slots) {
            return true;
        }

        $current_registrations = $this->registrations()->count();
        return ($this->slots - ($current_registrations + $count)) >= 0;
    }

    public function getPriceAfterTax(): Money
    {
        return TaxHelper::calculateFor($this->price, 1, $this->pst);
    }

    public function registrations(bool $withoutReturned = true): HasMany
    {
        return $this->hasMany(ActivityRegistration::class)->when($withoutReturned, fn (Builder $query) => $query->where('returned', false));
    }

    public function isAttending(User $user): bool
    {
        return $this->registrations()->where('user_id', $user->id)->exists();
    }

    public function getStatusHtml(): string
    {
        if ($this->ended()) {
            return '<span class="tag is-medium">🕐 Over</span>';
        }

        if ($this->inProgress()) {
            return '<span class="tag is-medium">✅ In Progress</span>';
        }

        return '<span class="tag is-medium">🔮 Waiting</span>';
    }

    public function inProgress(): bool
    {
        return $this->started() && !$this->ended();
    }

    public function started(): bool
    {
        return $this->start->isPast();
    }

    public function ended(): bool
    {
        return $this->end->isPast();
    }

    public function countdown(): string
    {
        return now()->diffForHumans($this->start, CarbonInterface::DIFF_ABSOLUTE, false, 2);
    }

    public function duration(): string
    {
        return $this->start->diffForHumans($this->end, CarbonInterface::DIFF_ABSOLUTE, false, 2);
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

        if (!$this->started()) {
            $timeline[] = new TimelineEntry(
                description: 'Starting',
                emoji: '🕐',
                time: $this->start,
            );
        } else if ($this->started()) {
            $timeline[] = new TimelineEntry(
                description: 'Started',
                emoji: '🚀',
                time: $this->start,
            );
        }

        if ($this->ended()) {
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
