<?php

namespace App\Helpers;

use Cookie;
use App\Models\Rotation;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

class RotationHelper extends Helper
{
    private Collection $_rotations;
    private ?Rotation $_current_rotation;

    public function getRotations(): Collection
    {
        return $this->_rotations ??= Rotation::orderBy('start', 'ASC')->get();
    }

    public function getCurrentRotation(): ?Rotation
    {
        if (!isset($this->_current_rotation)) {

            $this->_current_rotation = null;

            foreach ($this->getRotations() as $rotation) {

                if ($rotation->start->isPast() && $rotation->end->isFuture()) {
                    $this->_current_rotation = $rotation;
                    break;
                }
            }
        }

        return $this->_current_rotation;
    }

    public function getStatisticsRotation(): int|string
    {
        $current_rotation = RotationHelper::getInstance()->getCurrentRotation();

        return hasPermission('statistics_select_rotation') ? Cookie::get('statistics_rotation', $current_rotation?->id) : $current_rotation?->id;
    }

    public function doesRotationOverlap($start, $end): bool
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        foreach ($this->getRotations() as $rotation) {
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