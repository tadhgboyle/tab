<?php

namespace App\Helpers;

use App\Models\Rotation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Database\Eloquent\Collection;

class RotationHelper
{
    private Collection $rotations;
    private ?Rotation $currentRotation;

    /** @return Collection<int, Rotation> */
    public function getRotations(bool $with_users_count = false): Collection
    {
        return $this->rotations ??= Rotation::query()
            ->when($with_users_count, fn($query) => $query->withCount('users'))
            ->orderBy('start', 'ASC')
            ->get();
    }

    public function getCurrentRotation(): ?Rotation
    {
        return $this->currentRotation ??= $this->getRotations()->first(static function (Rotation $rotation) {
            return $rotation->start->isPast() && $rotation->end->isFuture();
        });
    }

    public function doesRotationOverlap($start, $end, ?int $ignore_id = null): bool
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        foreach ($this->getRotations()->where('id', '<>', $ignore_id) as $rotation) {
            if (
                ($start->between($rotation->start, $rotation->end) || $end->between($rotation->start, $rotation->end))
                ||
                ($rotation->start->between($start, $end) || $rotation->end->between($start, $end))
            ) {
                return true;
            }
        }

        return false;
    }

    // TODO extract into more generic for use with cashier list, and more
    public function getStatisticsRotationId(): string|int|null
    {
        $default = $this->getCurrentRotation()?->id;

        if (hasPermission(Permission::STATISTICS_SELECT_ROTATION)) {
            return Cookie::get('stats_rotation_id', $default ?? '*');
        }

        return $default;
    }

    public function getUserListRotationId(): string|int|null
    {
        $default = $this->getCurrentRotation()?->id;

        if (hasPermission(Permission::USERS_LIST_SELECT_ROTATION)) {
            return Cookie::get('user_list_rotation_id', $default ?? '*');
        }

        return $default;
    }
}
