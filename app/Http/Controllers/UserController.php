<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Category;
use App\Helpers\CategoryHelper;
use App\Helpers\RotationHelper;
use App\Helpers\UserLimitsHelper;
use App\Http\Requests\UserRequest;
use Illuminate\Http\RedirectResponse;
use App\Services\Users\UserEditService;
use App\Services\Users\UserDeleteService;
use App\Services\Users\UserCreationService;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;

class UserController extends Controller
{
    public function index(RotationHelper $rotationHelper)
    {
        $user_list_rotation_id = $rotationHelper->getUserListRotationId();

        if ($user_list_rotation_id === null) {
            $data = [
                'cannot_view_users' => true,
            ];
        } else {
            $users = User::query()->when($user_list_rotation_id !== '*', function (EloquentBuilder $query) use ($user_list_rotation_id) {
                $query->whereHas('rotations', function (EloquentBuilder $query) use ($user_list_rotation_id) {
                    return $query->where('rotation_id', $user_list_rotation_id);
                });
            })->with('role', 'rotations')->get();

            $data = [
                'rotations' => $rotationHelper->getRotations(),
                'users' => $users,
                'user_list_rotation_id' => $user_list_rotation_id,
            ];
        }

        return view('pages.users.list', $data);
    }

    public function show(CategoryHelper $categoryHelper, User $user)
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
            'activity_registrations' => $user->activityRegistrations,
            'categories' => $processed_categories,
            'rotations' => $user->rotations,
        ]);
    }

    public function create(CategoryHelper $categoryHelper, RotationHelper $rotationHelper)
    {
        $processed_categories = $categoryHelper->getCategories()->map(function (Category $category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'info' => [],
            ];
        });

        return view('pages.users.form', [
            'available_roles' => auth()->user()->role->getRolesAvailable()->all(),
            'categories' => $processed_categories->all(),
            'rotations' => $rotationHelper->getRotations(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        return (new UserCreationService($request))->redirect();
    }

    public function edit(CategoryHelper $categoryHelper, RotationHelper $rotationHelper, User $user)
    {
        if ($user->trashed()) {
            return redirect()->route('users_list')->with('error', 'That user has been deleted.')->send();
        }

        if (!auth()->user()->role->canInteract($user->role)) {
            return redirect()->route('users_list')->with('error', 'You cannot interact with that user.')->send();
        }

        $processed_categories = $categoryHelper->getCategories()->map(function (Category $category) use ($user) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'info' => UserLimitsHelper::getInfo($user, $category->id),
            ];
        });

        return view('pages.users.form', [
            'user' => $user,
            'available_roles' => auth()->user()->role->getRolesAvailable()->all(),
            'categories' => $processed_categories->all(),
            'rotations' => $rotationHelper->getRotations(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        return (new UserEditService($request, $user))->redirect();
    }

    public function delete(User $user): RedirectResponse
    {
        return (new UserDeleteService($user))->redirect();
    }
}
