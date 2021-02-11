<?php

namespace App\Http\Controllers;

use App\Helpers\RoleHelper;
use App\Http\Requests\UserRequest;
use App\Role;
use Auth;
use App\User;
use App\UserLimits;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    public function new(UserRequest $request)
    {
        // Create a new User object and hash it's password if required by their role
        $user = new User();
        $user->full_name = $request->full_name;
        if (empty($request->username)) {
            $user->username = strtolower(str_replace(" ", "", $request->full_name));
        } else {
            $user->username = $request->username;
        }

        // TODO: Edge case: Their full name was unique, they did not enter a custom username, but the autogenerated username above is not unique
        // Possible Cause: "Tad hgBoyle" as full name instead of "Tadhg Boyle" -> would make "tadhgboyle" as username, which might already exist
        // Three options:
        // 1. Leave as is. Slightly slower as we need another query, but it works
        // 2. Instead of replacing whitespace with "" when making the username, replace it with "_"
        // 3. Always add a random number to the end of all autogenerated usernames
        if (User::where('username', $user->username)->count() > 0) {
            $user->username = $user->username . mt_rand(0, 100);
        }

        $balance = 0;
        if (!empty($request->balance)) {
            $balance = $request->balance;
        }

        $user->balance = $balance;
        $user->role_id = $request->role_id;

        if (in_array($request->role_id, array_column(RoleHelper::getInstance()->getStaffRoles(), 'name'))) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        // Update their category limits
        foreach ($request->limit as $category => $limit) {
            $duration = 0;
            // Default to limit per day rather than week if not specified
            empty($request->duration[$category]) ? $duration = 0 : $duration = $request->duration[$category];
            // Default to unlimited limit if not specified
            if (empty($limit)) {
                $limit = -1;
            } else if ($limit < -1) {
                return redirect() > back()->with('error', 'Limit must be -1 or above for ' . ucfirst($category) . '. (-1 means no limit)')->withInput($request->all());
            }
            UserLimits::updateOrCreate(
                ['user_id' => $user->id, 'category' => $category],
                ['limit_per' => $limit, 'duration' => $duration, 'editor_id' => Auth::id()]
            );
        }
        return redirect()->route('users_list')->with('success', 'Created user ' . $request->full_name . '.');
    }

    public function edit(UserRequest $request)
    {
        $password = null;
        $old_role = User::find($request->id)->role->name;

        if (!in_array($request->role_id, array_column(Auth::user()->role->getRolesAvailable(), 'id'))) {
            return redirect()->back()->with('error', 'You cannot manage that role.')->withInput();
        }

        $new_role = Role::find($request->role_id)->name;
        $staff_roles = array_column(RoleHelper::getInstance()->getStaffRoles(), 'name');

        // Update their category limits
        foreach ($request->limit as $category => $limit) {
            $duration = 0;
            empty($request->duration[$category]) ? $duration = 0 : $duration = $request->duration[$category];
            if (empty($limit)) {
                $limit = -1;
            } else if ($limit < -1) {
                return redirect()->back()->with('error', 'Limit must be above -1 for ' . ucfirst($category) . '. (-1 means no limit)')->withInput($request->all());
            }
            UserLimits::updateOrCreate(
                ['user_id' => $request->id, 'category' => $category],
                ['limit_per' => $limit, 'duration' => $duration, 'editor_id' => Auth::id()]
            );
        }

        // TODO: This next part is fucking terrifying. Probably can find a better solution.
        // If same role or changing from one staff role to another
        if (($old_role == $new_role) || (in_array($old_role, $staff_roles) && in_array($new_role, $staff_roles))) {
            DB::table('users')
                ->where('id', $request->id)
                ->update(['full_name' => $request->full_name, 'username' => $request->username, 'balance' => $request->balance, 'role_id' => $request->role_id]);

            return redirect()->route('users_list')->with('success', 'Updated user ' . $request->full_name . '.');
        }
        // If old role is camper and new role is staff
        else if (!in_array($old_role, $staff_roles) && in_array($new_role, $staff_roles)) {
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
        else $password = null;

        DB::table('users')
            ->where('id', $request->id)
            ->update(['full_name' => $request->full_name, 'username' => $request->username, 'balance' => $request->balance, 'role_id' => $request->role_id, 'password' => $password]);

        return redirect()->route('users_list')->with('success', 'Updated user ' . $request->full_name . '.');
    }

    public function delete($id)
    {
        $user = User::find($id);
        $user->update(['deleted' => true]);
        return redirect()->route('users_list')->with('success', 'Deleted user ' . $user->full_name . '.');
    }
}
