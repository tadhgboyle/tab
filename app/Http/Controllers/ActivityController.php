<?php

namespace App\Http\Controllers;

use App\Activity;
use App\Http\Requests\ActivityRequest;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

class ActivityController extends Controller
{
    
    public function new(ActivityRequest $request)
    {
        if (Carbon::parse($request->start)->gte($request->end)) {
            return redirect()->route('activities_new')->withInput()->with('error', 'The end time must be after the start time.');
        }

        $activity = new Activity();
        $activity->name = $request->name;
        $activity->location = $request->location;
        $activity->description = $request->description;
        $activity->unlimited_slots = $request->has('unlimited_slots');
        if ($request->has('unlimited_slots')) {
            $activity->slots = -1;
        } else {
            $activity->slots = $request->slots;
        }
        $activity->price = $request->price;
        $activity->start = $request->start;
        $activity->end = $request->end;
        $activity->save();

        return redirect()->route('activities_list')->with('success', 'Created activity ' . $request->name . '.');
    }

    public function edit(ActivityRequest $request)
    {
        if (Carbon::parse($request->get('start'))->gte($request->get('end'))) {
            return redirect()->route('activities_edit', $request->activity_id)->withInput()->with('error', 'The end time must be after the start time.');
        }

        $activity = Activity::find($request->activity_id);

        $activity->update([
            'name' => $request->name,
            'location' => $request->location,
            'description' => $request->description,
            'unlimited_slots' => $request->has('unlimited_slots'),
            'slots' => $request->has('unlimited_slots') ? -1 : $request->slots,
            'price' => $request->price,
            'start' => $request->start,
            'end' => $request->end
        ]);

        return redirect()->route('activities_list')->with('success', 'Updated activity ' . $request->name . '.');
    }

    public function delete(int $id)
    {
        $activity = Activity::find($id);
        $activity->update(['deleted' => true]);
        return redirect()->route('activities_list')->with('success', 'Deleted activity ' . $activity->name . '.');
    }

    public function list()
    {
        return view('pages.activities.list', ['activities' => self::getAll()]);
    }

    public function view($id)
    {
        $activity = Activity::find($id);

        if ($activity == null) {
            return redirect()->route('activities_list')->with('error', 'Invalid activity.')->send();
        }

        $activities_manage = hasPermission('activities_manage');

        return view('pages.activities.view', [
            'activity' => $activity,
            'activities_manage' => $activities_manage,
            'can_register' => !strpos($activity->getStatus(), 'Over') && $activities_manage && $activity->hasSlotsAvailable() && hasPermission('activities_register_user')
        ]);
    }

    public function form()
    {
        $activity = Activity::find(request()->route('id'));
        if ($activity == null) {
            $start = request()->route('date') ?? Carbon::now();
        } else {
            $start = $activity->start;
        }

        return view('pages.activities.form', [
            'activity' => $activity,
            'start' => $start
            // TODO: 'categories' => CategoryHelper->getActivityCategories()
        ]);
    }

    public static function getAll()
    {
        $activities = Activity::where('deleted', false)->get(['id', 'name', 'start', 'end']);
        $return = array();

        foreach ($activities as $activity) {
            $return[] = [
                'title' => $activity->name,
                'start' => Carbon::parse($activity->start),
                'end' => Carbon::parse($activity->end),
                'url' => route('activities_view', $activity->id)
            ];
        }

        return json_encode($return);
    }

    public static function ajaxInit()
    {
        $users = User::where([['full_name', 'LIKE', '%' . \Request::get('search') . '%'], ['deleted', false]])->limit(5)->get();
        $output = '';

        if (count($users)) {
            $activity = Activity::find(\Request::get('activity'));
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

    public static function registerUser()
    {
        $activity = Activity::find(Route::current()->parameter('id'));
        $user = User::find(Route::current()->parameter('user'));
        if ($activity->registerUser($user)) {
            return redirect()->back()->with('success', 'Successfully registered ' . $user->full_name . ' to ' . $activity->name . '.');
        } else {
            return redirect()->back()->with('error', 'Could not register ' . $user->full_name . ' for ' . $activity->name . '. Is it out of slots?');
        }
    }
}
