<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettingsRequest;
use App\Models\Settings;
use App\Helpers\RoleHelper;
use App\Helpers\CategoryHelper;
use App\Helpers\RotationHelper;
use App\Helpers\SettingsHelper;

class SettingsController extends Controller
{
    public function editSettings(SettingsRequest $request)
    {
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
