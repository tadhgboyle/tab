<?php

namespace App\Helpers;

use App\Models\Rotation;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

class RotationHelper extends Helper
{
    private Collection $_rotations;
    private ?Rotation $_current_rotation;

    public function getRotations(): Collection
    {
        if (!isset($this->_rotations)) {
            $this->_rotations = Rotation::orderBy('start', 'ASC')->get();
        }

        return $this->_rotations;
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

    public function doesRotationOverlap(Carbon $start, Carbon $end): bool
    {
        foreach ($this->getRotations() as $rotation) {
            if (
                (Carbon::parse($start)->between($rotation->start, $rotation->end) || Carbon::parse($end)->between($rotation->start, $rotation->end))
                ||
                (Carbon::parse($rotation->start)->between($start, $end) || Carbon::parse($rotation->end)->between($start, $end))
            ) {
                return true;
            }
        }

        return false;
    }
}