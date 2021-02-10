<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Settings;

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

    public function newCat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:settings,value',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->withErrors($validator);
        }

        // TODO: Category ID's -> Allow for renaming of categories
        $settings = new Settings();
        $settings->setting = 'category';
        $settings->value = strtolower($request->name);
        $settings->editor_id = Auth::id();
        $settings->save();

        return redirect()->route('settings')->with('success', 'Created new category ' . $request->name . '.');
    }

    public function deleteCat(Request $request)
    {
        // TODO: Fallback "default" category for items whose categories were deleted?
        Settings::where([['setting', 'category'], ['value', $request->name]])->delete();
        return redirect()->route('settings')->with('success', 'Deleted category ' . ucfirst($request->name) . '.');
    }
}
