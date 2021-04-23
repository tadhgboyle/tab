<?php

namespace App\Http\Controllers;

use Auth;
use App\Role;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Http\Requests\RoleRequest;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function new(RoleRequest $request)
    {
        $staff = $request->has('staff');
        $superuser = $staff && $request->has('superuser');

        $role = new Role();
        $role->name = $request->name;
        $role->order = $request->order;
        $role->staff = $staff;
        $role->superuser = $superuser;
        $role->permissions = PermissionHelper::parseNodes($request->permissions);
        $role->save();

        return redirect()->route('settings')->with('success', 'Created role ' . $request->name . '.');
    }

    public function edit(RoleRequest $request)
    {
        $staff = $request->has('staff');
        $superuser = $staff && $request->has('superuser');

        DB::table('roles')
            ->where('id', $request->role_id)
            ->update(['name' => $request->name, 'order' => $request->order, 'superuser' => $superuser, 'staff' => $staff, 'permissions' => PermissionHelper::parseNodes($request->permissions)]);

        return redirect()->route('settings')->with('success', 'Edited role ' . $request->name . '.');
    }

    public function delete(Request $request)
    {
        // TODO: add same validation from frontend
        $old_role = Role::find($request->old_role);
        if (!$request->has('new_role')) {
            $old_role->update(['deleted' => true]);

            $message = 'Deleted role ' . $old_role->name . '.';
        } else {
            $new_role = Role::find($request->new_role);

            if (!$new_role->staff) {
                $fields = [
                    'role_id' => $new_role->id,
                    'password' => null,
                ];
            } else {
                $fields = [
                    'role_id' => $new_role->id,
                ];
            }

            DB::table('users')->where('role_id', $old_role->id)->update($fields);
            $old_role->update(['deleted' => true]);

            $message = 'Deleted role ' . $old_role->name . ', and placed all it\'s users into ' . $new_role->name . '.';
        }

        return redirect()->route('settings')->with('success', $message);
    }

    public function form()
    {
        $role = Role::find(request()->route('id'));

        if (!is_null($role)) {
            if (!Auth::user()->role->canInteract($role)) {
                return redirect()->route('settings')->with('error', 'You cannot interact with that role.')->send();
            }

            $affected_users = DB::table('users')->where('role_id', $role->id)->count();
            $available_roles = $role->getRolesAvailable(Auth::user()->role);
        }

        return view('pages.settings.roles.form', [
            'role' => $role,
            'affected_users' => $affected_users ?? null,
            'available_roles' => $available_roles ?? null,
            'permissionHelper' => PermissionHelper::getInstance(),
        ]);
    }

    public function order()
    {
        $roles = json_decode(\Request::get('roles'))->roles;

        $i = 1;
        foreach ($roles as $role) {
            Role::find($role)->update(['order' => $i]);
            $i++;
        }

        return $roles;
    }
}
