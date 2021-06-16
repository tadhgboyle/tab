<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Activity;
use Illuminate\Support\Carbon;
use App\Helpers\CategoryHelper;
use Illuminate\Support\Facades\Route;
use App\Http\Requests\ActivityRequest;

// TODO: add return/cancel functionality
class ActivityController extends Controller
{
    public function new(ActivityRequest $request)
    {
        if (Carbon::parse($request->start)->gte($request->end)) {
            return redirect()->route('activities_new')->withInput()->with('error', 'The end time must be after the start time.');
        }

        $activity = new Activity();
        $activity->name = $request->name;
        $activity->category_id = $request->category_id;
        $activity->location = $request->location;
        $activity->description = $request->description;
        $activity->unlimited_slots = $request->has('unlimited_slots');
        $activity->slots = $request->has('unlimited_slots') ? -1 : $request->slots;
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

        // TODO: not updating, fillables?
        $activity->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'location' => $request->location,
            'description' => $request->description,
            'unlimited_slots' => $request->has('unlimited_slots'),
            'slots' => $request->has('unlimited_slots') ? -1 : $request->slots,
            'price' => $request->price,
            'start' => $request->start,
            'end' => $request->end,
        ]);

        return redirect()->route('activities_list')->with('success', 'Updated activity ' . $request->name . '.');
    }

    public function delete(int $id)
    {
        $activity = Activity::find($id);

        $activity->delete();

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
            'can_register' => !strpos($activity->getStatus(), 'Over') && $activities_manage && $activity->hasSlotsAvailable() && hasPermission('activities_register_user'),
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
            'start' => $start,
            'categories' => CategoryHelper::getInstance()->getActivityCategories(),
        ]);
    }

    public static function getAll()
    {
        $activities = Activity::all(['id', 'name', 'start', 'end']);
        $return = [];

        foreach ($activities as $activity) {
            $return[] = [
                'title' => $activity->name,
                'start' => Carbon::parse($activity->start),
                'end' => Carbon::parse($activity->end),
                'url' => route('activities_view', $activity->id),
            ];
        }

        return json_encode($return);
    }

    public static function ajaxInit()
    {
        $users = User::where('full_name', 'LIKE', '%' . \Request::get('search') . '%')->limit(5)->get();
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

        return $activity->registerUser($user);
    }
}
