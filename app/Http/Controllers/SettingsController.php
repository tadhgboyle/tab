<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Settings;

class SettingsController extends Controller
{

    public static function getGst()
    {
        return Settings::where('setting', 'gst')->pluck('value')->first();
    }

    public static function getPst()
    {
        return Settings::where('setting', 'pst')->pluck('value')->first();
    }

    public static function getStaffDiscount()
    {
        return Settings::where('setting', 'staff_discount')->pluck('value')->first();
    }

    public static function getSelfPurchases(): bool
    {
        return Settings::where('setting', 'self_purchases')->pluck('value')->first() == 'true';
    }

    public static function getLookBack()
    {
        return Settings::where('setting', 'lookBack')->pluck('value')->first();
    }

    public static function getCategories()
    {
        return Settings::where('setting', 'category')->orderBy('value')->get();
    }

    public static function editLookBack(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lookback' => 'required'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        DB::table('settings')->where('setting', 'lookBack')->update(['value' => $request->lookback]);
        return redirect('/statistics')->send();
    }

    public function editSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gst' => 'required|numeric',
            'pst' => 'required|numeric',
            'staff_discount' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        // TODO: This is probably not as efficient as it could be...
        DB::table('settings')->where('setting', 'gst')->update(['value' => $request->gst]);
        DB::table('settings')->where('setting', 'pst')->update(['value' => $request->pst]);
        DB::table('settings')->where('setting', 'staff_discount')->update(['value' => $request->staff_discount]);
        DB::table('settings')->where('setting', 'self_purchases')->update(['value' => $request->has('self_purchases') ? 'true' : 'false']);
        return redirect('/settings')->with('success', 'Updated settings.');
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
        $settings->editor_id = $request->editor_id;
        $settings->save();

        return redirect('/settings')->with('success', 'Created new category ' . $request->name . '.');
    }

    public function deleteCat(Request $request)
    {
        // TODO: Fallback "default" category for items whose categories were deleted?
        Settings::where([['setting', 'category'], ['value', $request->name]])->delete();
        return redirect('/settings')->with('success', 'Deleted category ' . ucfirst($request->name) . '.');
    }
}
