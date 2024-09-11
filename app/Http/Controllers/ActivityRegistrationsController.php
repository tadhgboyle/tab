<?php

namespace App\Http\Controllers;

use App\Models\ActivityRegistration;
use App\Models\User;
use App\Models\Activity;
use App\Services\Activities\ActivityRegistrationCreateService;
use App\Services\Activities\ActivityRegistrationDeleteService;

// TODO: add return/cancel functionality
class ActivityRegistrationsController extends Controller
{
    public function store(Activity $activity, User $user)
    {
        return (new ActivityRegistrationCreateService($activity, $user))->redirect();
    }

    public function delete(Activity $activity, ActivityRegistration $activityRegistration)
    {
        return (new ActivityRegistrationDeleteService($activityRegistration))->redirect();
    }

    // TODO: livewire
    public function ajaxUserSearch(Activity $activity): string
    {
        // TODO scope by available rotations to logged in cashier?
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
                    '<td>' . $user->balance . '</td>' .
                    (($user->balance < $activity->getPriceAfterTax() || $activity->isAttending($user))
                        ? '<td><button class="button is-success is-small" disabled>Add</button></td>'
                        : '<td><a href="' . route('activities_register_user', [$activity->id, $user->id]) . '" class="button is-success is-small">Add</a></td>') .
                '</tr>';
        }

        return $output;
    }
}