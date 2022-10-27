<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use App\Helpers\CategoryHelper;
use App\Helpers\RotationHelper;
use App\Helpers\UserLimitsHelper;
use App\Http\Requests\UserRequest;
use App\Services\Users\UserEditService;
use App\Services\Users\UserDeleteService;
use App\Services\Users\UserCreationService;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class UserController extends Controller
{
    public function new(UserRequest $request)
    {
        return (new UserCreationService($request))->redirect();
    }

    public function edit(UserRequest $request)
    {
        return (new UserEditService($request))->redirect();
    }

    public function delete(User $user)
    {
        return (new UserDeleteService($user->id))->redirect();
    }

    public function list(RotationHelper $rotationHelper)
    {
        $users = User::query()->unless(hasPermission('users_list_select_rotation'), function (EloquentBuilder $query) use ($rotationHelper) {
            $query->whereHas('rotations', function (EloquentBuilder $query) use ($rotationHelper) {
                return $query->where('rotation_id', $rotationHelper->getCurrentRotation()->id);
            });
        })->get();

        return view('pages.users.list', [
            'rotations' => $rotationHelper->getRotations(),
            'users' => $users,
            'selectedRotation' => $rotationHelper->getCurrentRotation(),
        ]);
    }

    public function view(User $user, CategoryHelper $categoryHelper)
    {
        $processed_categories = [];

        $categoryHelper->getCategories()->each(function ($category) use ($user, &$processed_categories) {
            $info = UserLimitsHelper::getInfo($user, $category->id);

            $processed_categories[$category->id] = [
                'name' => $category->name,
                'limit' => $info->limit_per,
                'duration' => $info->duration,
                'spent' => UserLimitsHelper::findSpent($user, $category->id, $info),
            ];
        });

        return view('pages.users.view', [
            'user' => $user,
            'can_interact' => auth()->user()->role->canInteract($user->role),
            'transactions' => $user->transactions->sortByDesc('created_at'),
            'activity_transactions' => $user->getActivities(),
            'categories' => $processed_categories,
            'rotations' => $user->rotations,
            'payouts' => $user->payouts->sortByDesc('created_at'),
        ]);
    }

    public function form(CategoryHelper $categoryHelper, RotationHelper $rotationHelper, ?User $user = null)
    {
        if ($user !== null) {
            if ($user->trashed()) {
                return redirect()->route('users_list')->with('error', 'That user has been deleted.')->send();
            }

            if (!auth()->user()->role->canInteract($user->role)) {
                return redirect()->route('users_list')->with('error', 'You cannot interact with that user.')->send();
            }
        }

        $id = $categoryHelper->getCategories()->map(fn ($cat) => $cat)->get(0)->id;

        $processed_categories = [];
        $categoryHelper->getCategories()->each(function (Category $category) use ($user, &$processed_categories) {
            $processed_categories[] = [
                'id' => $category->id,
                'name' => $category->name,
                'info' => $user === null ? [] : UserLimitsHelper::getInfo($user, $category->id),
            ];
        });

        return view('pages.users.form', [
            'user' => $user,
            'available_roles' => auth()->user()->role->getRolesAvailable()->all(),
            'categories' => $processed_categories,
            'rotations' => $rotationHelper->getRotations(),
        ]);
    }
}
