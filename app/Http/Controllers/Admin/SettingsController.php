<?php

namespace App\Http\Controllers\Admin;

use App\Models\Settings;
use App\Helpers\Permission;
use App\Helpers\SettingsHelper;
use App\Http\Requests\SettingsRequest;
use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    public function editSettings(SettingsRequest $request)
    {
        Settings::where('setting', 'gst')->update(['value' => $request->gst]);
        Settings::where('setting', 'pst')->update(['value' => $request->pst]);
        Settings::where('setting', 'order_prefix')->update(['value' => $request->order_prefix ?? '']);
        Settings::where('setting', 'order_suffix')->update(['value' => $request->order_suffix ?? '']);

        return redirect()->route('settings')->with('success', 'Updated settings.');
    }

    public function view(
        SettingsHelper $settingsHelper
    ) {
        $vars = [];

        if (hasPermission(Permission::SETTINGS_GENERAL)) {
            $vars['gst'] = $settingsHelper->getGst();
            $vars['pst'] = $settingsHelper->getPst();
            $vars['orderPrefix'] = $settingsHelper->getOrderPrefix();
            $vars['orderSuffix'] = $settingsHelper->getOrderSuffix();
        }

        return view('pages.admin.settings.settings', $vars);
    }
}
