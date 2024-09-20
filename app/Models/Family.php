<?php

namespace App\Models;

use App\Concerns\Timeline\HasTimeline;
use App\Concerns\Timeline\TimelineEntry;
use App\Enums\OrderStatus;
use App\Enums\UserLimitDuration;
use App\Helpers\Permission;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Family extends Model implements HasTimeline
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, FamilyMember::class, 'family_id', 'id', 'id', 'user_id');
    }

    public function totalSpent(): Money
    {
        return Money::sum(Money::parse(0), ...$this->membersWithUserRelations->map->user->map->findSpentInCash());
    }

    public function totalOwing(): Money
    {
        return Money::sum(Money::parse(0), ...$this->membersWithUserRelations->map->user->map->findOwing());
    }

    public function membersWithUserRelations(): HasMany
    {
        return $this->members()->with('user', 'user.orders', 'user.activityRegistrations');
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

        foreach ($this->members()->with('user')->get() as $member) {
            $events[] = new TimelineEntry(
                description: "{$member->user->full_name} joined",
                emoji: '👨‍👩‍👧‍👦',
                time: $member->created_at,
                link: $this->familyMemberLink($member),
            );
        }

        usort($events, fn ($a, $b) => $a->time <=> $b->time);

        return $events;
    }

    private function familyMemberLink(FamilyMember $familyMember): ?string
    {
        if (request()->routeIs('family_view')) {
            return auth()->user()->isFamilyAdmin($this) ? route('families_member_view', [$this, $familyMember]) : null;
        }

        return hasPermission(Permission::USERS_VIEW) ? route('users_view', $familyMember->user) : null;
    }
}
