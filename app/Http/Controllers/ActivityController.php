<?php

namespace App\Http\Controllers;

use App\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function new(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:255|unique:activities',
            'location' => 'min:3|max:36',
            'description' => 'min:3|max:255',
            'slots' => 'required|numeric|min:-1|not_in:0',
            'price' => 'required|numeric',
            'start' => 'required',
            'end' => 'required_if:all_day,1',
            'pst' => 'boolean',
            'all_day' => 'boolean',
        ]);
        if ($validator->fails()) {
            return redirect()->route('activities_new')->withInput()->withErrors($validator);
        }

        $activity = new Activity();

        $activity->name = $request->name;
        $activity->location = $request->location;
        $activity->description = $request->description;
        $activity->start = $request->start;
        if ($request->has('all_day')) {
            $activity->end = $request->end;
            $activity->all_day = true;
        } else {
            $activity->end = null;
            $activity->all_day = false;
        }
        $activity->save();

        return redirect()->route('activities_list')->with('success', 'Created activity ' . $request->name . '.');
    }

    public static function getAll($json = false) 
    {
        $activities = Activity::all();
        return $json ? json_encode($activities) : $activities;
    }
}
