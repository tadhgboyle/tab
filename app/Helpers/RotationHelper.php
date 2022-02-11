<?php

namespace App\Helpers;

use App\Models\Rotation;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

class RotationHelper extends Helper
{
    private Collection $rotations;
    private ?Rotation $currentRotation;

    public function getRotations(): Collection
    {
        return $this->rotations ??= Rotation::query()->orderBy('start', 'ASC')->get();
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
}
