<?php

namespace App\Http\Controllers;

use App\Helpers\RoleHelper;
use App\Models\Settings;
use Illuminate\Http\Request;
use App\Helpers\CategoryHelper;
use App\Helpers\RotationHelper;
use App\Helpers\SettingsHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    // TODO: settingsrequest
    public function editSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gst' => 'required|numeric',
            'pst' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->route('settings')->withErrors($validator);
        }

        Settings::where('setting', 'gst')->update(['value' => $request->gst]);
        Settings::where('setting', 'pst')->update(['value' => $request->pst]);

        return redirect()->route('settings')->with('success', 'Updated tax settings.');
    }

    public function view(
        SettingsHelper $settingsHelper,
        CategoryHelper $categoryHelper,
        RoleHelper $roleHelper,
        RotationHelper $rotationHelper
    ) {
        return view('pages.settings.settings', [
            'gst' => $settingsHelper->getGst(),
            'pst' => $settingsHelper->getPst(),
            'categories' => $categoryHelper->getCategories(),
            'roles' => $roleHelper->getRoles('ASC'),
            'rotations' => $rotationHelper->getRotations(),
            'currentRotation' => $rotationHelper->getCurrentRotation()?->name
        ]);
    }
}
