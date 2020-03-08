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
        return Settings::all()['0']['value'];
    }

    public static function getPst()
    {
        return Settings::all()['1']['value'];
    }

    public static function getCategories()
    {
        return Settings::all()->where('setting', 'category');
    }

    public function editTax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gst' => 'required|numeric',
            'pst' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withInput($request->all())
                ->withErrors($validator);
        }
        // this is inefficient. find better solution
        DB::table('settings')->where('setting', '=', 'gst')
            ->update(['value' => $request->gst]);
        DB::table('settings')->where('setting', '=', 'pst')
            ->update(['value' => $request->pst]);
        return redirect('/settings')->with('success', 'Updated settings.');
    }

    public function newCat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withInput($request->all())
                ->withErrors($validator);
        }

        $settings = new Settings();
        $settings->setting = "category";
        $settings->value = strtolower($request->name);
        $settings->editor_id = $request->editor_id;
        $settings->save();

        return redirect('/settings')->with('success', 'Created new category ' . $request->name . '.');
    }

    public function deleteCat(Request $request)
    {
        Settings::where('id', $request->id)->delete();
        return redirect('/settings')->with('success', 'Deleted category.');
    }
}
