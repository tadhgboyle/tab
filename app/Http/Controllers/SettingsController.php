<?php

namespace App\Http\Controllers;

use App\Helpers\RoleHelper;
use Illuminate\Http\Request;
use App\Helpers\SettingsHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Settings;
use Auth;

class SettingsController extends Controller
{

    public static function editStatsTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stats_time' => 'required'
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
            'manage_general' => Auth::user()->hasPermission('settings_general'),
            'manage_roles' => Auth::user()->hasPermission('settings_roles_manage'),
            'manage_categories' => Auth::user()->hasPermission('settings_categories_manage'),
            'gst' => SettingsHelper::getInstance()->getGst(),
            'pst' => SettingsHelper::getInstance()->getPst(),
            'categories' => SettingsHelper::getInstance()->getCategories(),
            'roles' => RoleHelper::getInstance()->getRoles('ASC')
        ]);
    }

    public function categoryForm()
    {
        return view('pages.settings.categories.form');
    }

    public function newCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:settings,value',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        // TODO: Category ID's -> Allow for renaming of categories
        $settings = new Settings();
        $settings->setting = 'category';
        $settings->value = strtolower($request->name);
        $settings->editor_id = auth()->id();
        $settings->save();

        return redirect()->route('settings')->with('success', 'Created new category ' . $request->name . '.');
    }

    public function deleteCategory(Request $request)
    {
        // TODO: Fallback "default" category for items whose categories were deleted?
        Settings::where([['setting', 'category'], ['value', $request->name]])->delete();
        return redirect()->route('settings')->with('success', 'Deleted category ' . ucfirst($request->name) . '.');
    }
}
