<?php

namespace App\Http\Controllers;

use App\Helpers\RotationHelper;

class StatisticsPageController extends Controller
{
    public function view(RotationHelper $rotationHelper)
    {
        return view('pages.statistics.statistics', [
            'rotations' => $rotationHelper->getRotations(),
            'stats_rotation_id' => $rotationHelper->getCurrentRotation(),
        ]);
    }
}
