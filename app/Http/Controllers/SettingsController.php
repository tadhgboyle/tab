<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Helpers\Permission;
use App\Helpers\SettingsHelper;
use App\Http\Requests\SettingsRequest;

class SettingsController extends Controller
{
    public function editSettings(SettingsRequest $request)
    {
        Settings::where('setting', 'gst')->update(['value' => $request->gst]);
        Settings::where('setting', 'pst')->update(['value' => $request->pst]);

        return redirect()->route('settings')->with('success', 'Updated tax settings.');
    }

    public function view(
        SettingsHelper $settingsHelper
    ) {
        $vars = [];

        if (hasPermission(Permission::SETTINGS_GENERAL)) {
            $vars['gst'] = $settingsHelper->getGst();
            $vars['pst'] = $settingsHelper->getPst();
        }

        return view('pages.settings.settings', $vars);
    }
}
