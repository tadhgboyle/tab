<?php

namespace App\Models;

use App\Concerns\Timeline\HasTimeline;
use App\Concerns\Timeline\TimelineEntry;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Family extends Model implements HasTimeline
{
    public function memberships(): HasMany
    {
        return $this->hasMany(FamilyMembership::class);
    }

    public function totalSpent(): Money
    {
        return Money::sum(Money::parse(0), ...$this->membershipsWithUserRelations->map->user->map->findSpentInCash());
    }

    public function totalOwing(): Money
    {
        return Money::sum(Money::parse(0), ...$this->membershipsWithUserRelations->map->user->map->findOwing());
    }

    public function membershipsWithUserRelations(): HasMany
    {
        return $this->memberships()->with('user', 'user.orders', 'user.activityRegistrations');
    }

    public function timeline(): array
    {
        $events = [
            new TimelineEntry(
                description: 'Created',
                emoji: '🏠',
                time: $this->created_at,
            ),
        ];

        foreach ($this->memberships()->with('user')->get() as $membership) {
            $events[] = new TimelineEntry(
                description: "{$membership->user->full_name} joined",
                emoji: '👨‍👩‍👧‍👦',
                time: $membership->created_at,
                link: route('families_user_view', $membership->user),
            );
        }

        usort($events, fn ($a, $b) => $a->time <=> $b->time);

        return $events;
    }
}
