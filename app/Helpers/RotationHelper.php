<?php

namespace App\Helpers;

use App\Models\Rotation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class RotationHelper extends Helper
{
    private Collection $_rotations;
    private Rotation $_current_rotation;

    public function getRotations(): Collection
    {
        if (!isset($this->_rotations)) {
            $this->_rotations = Rotation::orderBy('start', 'ASC')->get();
        }

        return $this->_rotations;
    }

    public function getCurrentRotation(): Rotation
    {
        if (!isset($this->_current_rotation)) {
            $date = Carbon::now();

            foreach ($this->getRotations() as $rotation) {

                if ($rotation->start >= $date && $rotation->end <= $date) {
                    $this->_current_rotation = $rotation;
                    break;
                }
            }

            $this->_current_rotation = null;
        }

        return $this->_current_rotation;
    }
}