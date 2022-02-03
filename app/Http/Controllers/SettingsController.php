<?php

namespace App\Http\Controllers;

use App\Helpers\RoleHelper;
use Illuminate\Http\Request;
use App\Helpers\CategoryHelper;
use App\Helpers\RotationHelper;
use App\Helpers\SettingsHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public function editSettings(Request $request)
    {
        if ($validator = Validator::make($request->all(), [
            'gst' => 'required|numeric',
            'pst' => 'required|numeric',
        ])->fails()) {
            return redirect()->route('settings')->withInput()->withErrors($validator);
        }

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
            'rotations' => RotationHelper::getInstance()->getRotations(),
            'currentRotation' => RotationHelper::getInstance()->getCurrentRotation()?->name
        ]);
    }
}
