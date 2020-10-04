<?php

namespace App\Http\Controllers;

use App\Activity;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

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
            return redirect()->route('activities_new')->withInput()->with('error', 'The end time must be after the start time.');
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

    public static function ajaxInit() {
        $activity = Activity::find(\Request::get('activity'));
        $users = User::where([['full_name', 'LIKE', '%' . \Request::get('search') . '%'], ['deleted', false]])->limit(5)->get();
        $output = '';

        if ($users) {
            foreach ($users as $key => $user) {
                $output .= 
                '<tr>' .
                    '<td>' . $user->full_name . '</td>' . 
                    '<td>$' . number_format($user->balance, 2) . '</td>' .
                    (($user->balance < $activity->getPrice() || $activity->isAttending($user)) ?
                    '<td><button class="button is-success is-small" disabled>Add</button></td>' :
                    '<td><a href="' . route('activities_user_add', [$activity->id, $user->id]) . '" class="button is-success is-small">Add</a></td>') . 
                '</tr>';
            }
        }

        return $output;
    }

    public static function registerUser() {
        $activity = Activity::find(Route::current()->parameter('id'));
        $user = User::find(Route::current()->parameter('user'));
        if ($activity->registerUser($user)) {
            return redirect()->back()->with('success', 'Successfully registered ' . $user->full_name . ' to ' . $activity->name . '.');
        } else return redirect()->back()->with('error', 'Could not register ' . $user->full_name . ' for ' . $activity->name . '. Is it out of slots?');
    }
}
