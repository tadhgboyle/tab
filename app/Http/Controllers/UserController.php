<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Role;
use App\Models\User;
use App\Models\Category;
use App\Models\UserLimits;
use App\Helpers\RoleHelper;
use App\Helpers\CategoryHelper;
use App\Helpers\UserLimitsHelper;
use App\Http\Requests\UserRequest;
use App\Services\UserCreationService;

class UserController extends Controller
{
    public function new(UserRequest $request)
    {
        return (new UserCreationService($request))->redirect();
    }

    public function edit(UserRequest $request)
    {
        if (!in_array($request->role_id, array_column(Auth::user()->role->getRolesAvailable(), 'id'))) {
            return redirect()->back()->with('error', 'You cannot manage users with that role.')->withInput();
        }

        $password = null;
        $user = User::find($request->id);
        $old_role = $user->role->name;

        $new_role = Role::find($request->role_id)->name;
        $staff_roles = array_column(RoleHelper::getInstance()->getStaffRoles(), 'name');

        // Update their category limits
        foreach ($request->limit as $category_id => $limit) {
            $duration = 0;
            empty($request->duration[$category_id]) ? $duration = 0 : $duration = $request->duration[$category_id];
            if (empty($limit)) {
                $limit = -1;
            } else {
                if ($limit < -1) {
                    return redirect()->back()->with('error', 'Limit must be above -1 for ' . Category::find($category_id)->name . '. (-1 means no limit)')->withInput($request->all());
                }
            }
            UserLimits::updateOrCreate(
                ['user_id' => $request->id, 'category_id' => $category_id],
                ['limit_per' => $limit, 'duration' => $duration, 'editor_id' => Auth::id()]
            );
        }

        // TODO: This next part is fucking terrifying. Probably can find a better solution.
        // If same role or changing from one staff role to another
        if (($old_role == $new_role) || (in_array($old_role, $staff_roles) && in_array($new_role, $staff_roles))) {
            $user->update($request->all(['full_name', 'user_name', 'balance', 'role_id']));
            return redirect()->route('users_list')->with('success', 'Updated user ' . $request->full_name . '.');
        }
        // If old role is camper and new role is staff
        else {
            if (!in_array($old_role, $staff_roles) && in_array($new_role, $staff_roles)) {
                if (!empty($request->password)) {
                    if ($request->password == $request->password_confirmation) {
                        $password = bcrypt($request->password);
                    } else {
                        return redirect()->back()->with('error', 'Please confirm the password.')->withInput();
                    }
                } else {
                    return redirect()->back()->with('error', 'Please enter a password.')->withInput();
                }
            }
            // If new role is camper
            else {
                $password = null;
            }
        }

        $user->update(['full_name' => $request->full_name, 'username' => $request->username, 'balance' => $request->balance, 'role_id' => $request->role_id, 'password' => $password]);

        return redirect()->route('users_list')->with('success', 'Updated user ' . $request->full_name . '.');
    }

    public function delete($id)
    {
        $user = User::find($id);
        $user->update(['deleted' => true]);
        return redirect()->route('users_list')->with('success', 'Deleted user ' . $user->full_name . '.');
    }

    public function list()
    {
        return view('pages.users.list', [
            'users' => User::where('deleted', false)->get(),
        ]);
    }

    public function view()
    {
        $user = User::find(request()->route('id'));
        if ($user == null) {
            return redirect()->route('users_list')->with('error', 'Invalid user.')->send();
        }

        $processed_categories = [];
        $categories = CategoryHelper::getInstance()->getCategories();

        foreach ($categories as $category) {
            $info = UserLimitsHelper::getInfo($user, $category->id);

            $processed_categories[$category->id] = [
                'name' => $category->name,
                'limit' => $info->limit_per,
                'duration' => $info->duration,
                'spent' => UserLimitsHelper::findSpent($user, $category->id, $info),
            ];
        }

        return view('pages.users.view', [
            'user' => $user,
            'can_interact' => Auth::user()->role->canInteract($user->role),
            'transactions' => $user->getTransactions(),
            'activity_transactions' => $user->getActivities(),
            'categories' => $processed_categories,
        ]);
    }

    public function form()
    {
        $user = User::find(request()->route('id'));
        if ($user != null) {
            if ($user->deleted) {
                return redirect()->route('users_list')->with('error', 'That user has been deleted.')->send();
            }

            if (!Auth::user()->role->canInteract($user->role)) {
                return redirect()->route('users_list')->with('error', 'You cannot interact with that user.')->send();
            }
        }

        $processed_categories = [];
        $categories = CategoryHelper::getInstance()->getCategories()->sortBy('name');

        foreach ($categories as $category) {
            $processed_categories[] = [
                'id' => $category->id,
                'name' => $category->name,
                'info' => $user == null ? [] : UserLimitsHelper::getInfo($user, $category->id),
            ];
        }

        return view('pages.users.form', [
            'user' => $user,
            'available_roles' => Auth::user()->role->getRolesAvailable(),
            'categories' => $processed_categories,
        ]);
    }
}
