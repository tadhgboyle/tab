<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function update(Request $request)
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
        return redirect('/settings');
    }
}
