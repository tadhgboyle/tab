<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use App\Helpers\CategoryHelper;
use App\Http\Requests\ActivityRequest;

// TODO: add return/cancel functionality
// TODO: fix - add pst check box
class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::all(['id', 'name', 'start', 'end']);
        $return = [];

        foreach ($activities as $activity) {
            $return[] = [
                'title' => $activity->name,
                'start' => Carbon::parse($activity->start),
                'end' => Carbon::parse($activity->end),
                'url' => hasPermission('activities_view') ? route('activities_view', $activity->id) : '',
            ];
        }

        return view('pages.activities.list', [
            'activities' => json_encode($return),
        ]);
    }

    public function show(Activity $activity)
    {
        return view('pages.activities.view', [
            'activity' => $activity,
            'activities_manage' => hasPermission('activities_manage'),
            'can_register' => !$activity->end->isPast() && $activity->hasSlotsAvailable() && hasPermission('activities_register_user'),
        ]);
    }

    public function create(CategoryHelper $categoryHelper)
    {
        $start = request()->route('date') !== null ? Carbon::parse(request()->route('date')) : Carbon::now();
        $end = $start->copy()->addHour();

        return view('pages.activities.form', [
            'start' => $start,
            'end' => $end,
            'categories' => $categoryHelper->getActivityCategories(),
        ]);
    }

    public function store(ActivityRequest $request): RedirectResponse
    {
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

    public function edit(CategoryHelper $categoryHelper, Activity $activity)
    {
        return view('pages.activities.form', [
            'activity' => $activity,
            'start' => $activity->start,
            'end' => $activity->end,
            'categories' => $categoryHelper->getActivityCategories(),
        ]);
    }

    public function update(ActivityRequest $request, Activity $activity): RedirectResponse
    {
        if (Carbon::parse($request->get('start'))->gte($request->get('end'))) {
            return redirect()->route('activities_edit', $request->activity_id)->with('error', 'The end time must be after the start time.');
        }

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

        return redirect()->route('activities_view', $request->activity_id)->with('success', 'Updated activity.');
    }

    public function delete(Activity $activity)
    {
        $activity->delete();

        return redirect()->route('activities_list')->with('success', 'Deleted activity ' . $activity->name . '.');
    }


    // TODO: livewire
    public function ajaxUserSearch(Activity $activity): string
    {
        $users = User::query()
                        ->where('full_name', 'LIKE', '%' . request('search') . '%')
                        ->limit(7)
                        ->get()
                        ->all();
        $output = '';

        foreach ($users as $user) {
            $output .=
                '<tr>' .
                    '<td>' . $user->full_name . '</td>' .
                    '<td>$' . number_format($user->balance, 2) . '</td>' .
                    (($user->balance < $activity->getPrice() || $activity->isAttending($user))
                        ? '<td><button class="button is-success is-small" disabled>Add</button></td>'
                        : '<td><a href="' . route('activities_user_add', [$activity->id, $user->id]) . '" class="button is-success is-small">Add</a></td>') .
                '</tr>';
        }

        return $output;
    }

    public function registerUser(Activity $activity, User $user): RedirectResponse
    {
        return $activity->registerUser($user);
    }
}
