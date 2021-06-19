<?php

namespace App\Http\Controllers;

use App\Helpers\RoleHelper;
use Illuminate\Http\Request;
use App\Helpers\CategoryHelper;
use App\Helpers\RotationHelper;
use App\Helpers\SettingsHelper;
use App\Models\Rotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public static function editStatsTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stats_time' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        DB::table('settings')->where('setting', 'stats_time')->update(['value' => $request->stats_time]);
        return redirect()->route('statistics')->send();
    }

    public function editSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gst' => 'required|numeric',
            'pst' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        // TODO: This is probably not as efficient as it could be... use updateOrCreate() ?
        DB::table('settings')->where('setting', 'gst')->update(['value' => $request->gst]);
        DB::table('settings')->where('setting', 'pst')->update(['value' => $request->pst]);
        return redirect()->route('settings')->with('success', 'Updated settings.');
    }

    public function view()
    {
        return view('pages.settings.settings', [
            'gst' => SettingsHelper::getInstance()->getGst(),
            'pst' => SettingsHelper::getInstance()->getPst(),
            'categories' => CategoryHelper::getInstance()->getCategories(),
            'roles' => RoleHelper::getInstance()->getRoles('ASC'),
            'rotations' => Rotation::orderBy('start', 'ASC')->get(),
            'currentRotation' => RotationHelper::getInstance()->getCurrentRotation()?->name
        ]);
    }
}
