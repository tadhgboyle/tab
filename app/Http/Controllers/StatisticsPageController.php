<?php

namespace App\Http\Controllers;

use App\Helpers\RotationHelper;

class StatisticsPageController extends Controller
{
    public function view()
    {
        return view('pages.statistics.statistics', [
            'rotations' => RotationHelper::getInstance()->getRotations(),
            'stats_rotation_id' => RotationHelper::getInstance()->getStatisticsRotation(),
        ]);
    }
}
