<?php

namespace App\Http\Controllers\Admin;

use App\Models\Activity;
use App\Helpers\Permission;
use Illuminate\Support\Carbon;
use App\Helpers\CategoryHelper;
use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\ActivityRequest;

class ActivityController extends Controller
{
    public function __construct(
        private NotificationHelper $notificationHelper
    ) {
        // ...
    }

    public function index()
    {
        return view('pages.admin.activities.list');
    }

    public function calendar()
    {
        $activities = Activity::all(['id', 'name', 'start', 'end']);
        $return = [];

        foreach ($activities as $activity) {
            $return[] = [
                'title' => $activity->name,
                'start' => Carbon::parse($activity->start),
                'end' => Carbon::parse($activity->end),
                'url' => hasPermission(Permission::ACTIVITIES_VIEW) ? route('activities_view', $activity->id) : '',
            ];
        }

        return view('pages.admin.activities.calendar', [
            'activities' => json_encode($return),
        ]);
    }

    public function show(Activity $activity)
    {
        return view('pages.admin.activities.view', [
            'activity' => $activity,
            'can_register' => !$activity->ended() && $activity->hasSlotsAvailable() && hasPermission(Permission::ACTIVITIES_MANAGE_REGISTRATIONS),
            'can_remove' => !$activity->started() && hasPermission(Permission::ACTIVITIES_MANAGE_REGISTRATIONS),
        ]);
    }

    public function create(CategoryHelper $categoryHelper)
    {
        $start = old('start') ?: Carbon::now()->setTimeFrom(now()->setHour(9)->setMinute(0)->setSeconds(0));
        $end = old('end') ?: $start->copy()->addHour();

        return view('pages.admin.activities.form', [
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
        $activity->pst = $request->has('pst');
        $activity->start = $request->start;
        $activity->end = $request->end;
        $activity->save();

        $this->notificationHelper->sendSuccessNotification('Activity Created', "Created activity $activity->name", [
            ['name' => 'view_activity', 'url' => route('activities_view', $activity->id)],
        ]);

        return redirect()->route('activities_calendar');
    }

    public function edit(CategoryHelper $categoryHelper, Activity $activity)
    {
        return view('pages.admin.activities.form', [
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
            'pst' => $request->has('pst'),
            'start' => $request->start,
            'end' => $request->end,
        ]);

        $this->notificationHelper->sendSuccessNotification('Activity Updated', "Updated activity $activity->name", [
            ['name' => 'view_activity', 'url' => route('activities_view', $activity->id)],
        ]);

        return redirect()->route('activities_view', $request->activity_id);
    }

    public function delete(Activity $activity)
    {
        $activity->delete();

        return redirect()->route('activities_calendar')->with('success', "Deleted activity $activity->name");
    }
}
