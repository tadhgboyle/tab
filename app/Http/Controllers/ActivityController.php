<?php

namespace App\Http\Controllers;

use App\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class ActivityController extends Controller
{
    public function new(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:255|unique:activities',
            'location' => 'min:3|max:36',
            'description' => 'min:3|max:255',
            'slots' => 'required_if:unlimited_slots,0|numeric|min:1',
            'price' => 'required|numeric',
            'start' => 'required',
            'end' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->route('activities_new')->withInput()->withErrors($validator);
        }

        if (Carbon::parse($request->get('start'))->gte($request->get('end'))) {
            return redirect()->route('activities_new')->withInput()->with(['error', 'The end time must be after the start time.']);
        }

        $activity = new Activity();

        $activity->name = $request->name;
        $activity->location = $request->location;
        $activity->description = $request->description;
        $activity->unlimited_slots = $request->has('unlimited_slots');
        if ($request->has('unlimited_slots')) {
            $activity->slots = -1;
        } else $activity->slots = $request->slots;
        $activity->price = $request->price;
        $activity->pst = $request->has('pst');
        $activity->start = $request->start;
        $activity->end = $request->end;
        $activity->save();

        return redirect()->route('activities_list')->with('success', 'Created activity ' . $request->name . '.');
    }

    public function list() {
        return view('pages.activities.list', ['activities' => self::getAll(true, ['id', 'name', 'start', 'end'])]);
    }

    public static function getAll($json = false, $columns = ['*']) 
    {
        $activities = Activity::all($columns);
        $return = array();

        foreach ($activities as $activity) {
            $return[] = [
                'title' => $activity->name,
                'start' => Carbon::parse($activity->start),
                'end' => Carbon::parse($activity->end),
                'url' => route('activities_view', $activity->id)
            ];
        }

        return $json ? json_encode($return) : $return;
    }
}
