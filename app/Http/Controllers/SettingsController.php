<?php

namespace App\Http\Controllers;

use App\Models\GiftCard;
use App\Models\Settings;
use App\Helpers\Permission;
use App\Helpers\RoleHelper;
use App\Helpers\CategoryHelper;
use App\Helpers\RotationHelper;
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
        SettingsHelper $settingsHelper,
        CategoryHelper $categoryHelper,
        RoleHelper $roleHelper,
        RotationHelper $rotationHelper
    ) {
        $user = auth()->user();

        $vars = [];

        if ($user->hasPermission(Permission::SETTINGS_GENERAL)) {
            $vars['gst'] = $settingsHelper->getGst();
            $vars['pst'] = $settingsHelper->getPst();
        }

        if ($user->hasPermission(Permission::SETTINGS_CATEGORIES_MANAGE)) {
            $vars['categories'] = $categoryHelper->getCategories();
        }

        if ($user->hasPermission(Permission::SETTINGS_ROLES_MANAGE)) {
            $vars['roles'] = $roleHelper->getRoles('ASC');
        }

        if ($user->hasPermission(Permission::SETTINGS_ROTATIONS_MANAGE)) {
            $vars['rotations'] = $rotationHelper->getRotations(true);
            $vars['currentRotation'] = $rotationHelper->getCurrentRotation()?->name;
        }

        if ($user->hasPermission(Permission::SETTINGS_GIFT_CARDS_MANAGE)) {
            $vars['giftCards'] = GiftCard::query()->with('issuer')->withCount('assignments', 'uses')->orderBy('created_at', 'DESC')->get();
        }

        return view('pages.settings.settings', $vars);
    }
}
