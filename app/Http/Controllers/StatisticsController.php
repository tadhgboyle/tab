<?php

namespace App\Http\Controllers;

use App\Helpers\RotationHelper;
use Illuminate\Support\Facades\Cookie;

class StatisticsController extends Controller
{
    public function index(RotationHelper $rotationHelper)
    {
        $statistics_rotation_id = resolve(RotationHelper::class)->getStatisticsRotationId();

        if ($statistics_rotation_id === null) {
            $data = [
                'cannot_view_statistics' => true,
            ];
        } else {
            $data = [
                'rotations' => $rotationHelper->getRotations(),
                'stats_rotation_id' => $rotationHelper->getStatisticsRotationId(),
            ];
        }

        return view('pages.statistics.statistics', $data);
    }
}
