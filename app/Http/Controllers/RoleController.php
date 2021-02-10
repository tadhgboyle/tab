<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{

    public function new(RoleRequest $request)
    {
        $staff = $request->has('staff');

        $superuser = false;

        $permissions = array();
        if ($staff) {
            if (is_array($request->permissions)) {
                foreach ($request->permissions as $permission => $value) {
                    if ($value) $permissions[] = $permission;
                }
            }
            if ($request->has('superuser')) {
                $superuser = true;
            }
        }

        $role = new Role();
        $role->name = $request->name;
        $role->order = $request->order;
        $role->staff = $staff;
        $role->superuser = $superuser;
        $role->permissions = $permissions;
        $role->save();

        return redirect()->route('settings')->with('success', 'Created role ' . $request->name . '.');
    }

    public function edit(RoleRequest $request)
    {
        $staff = $request->has('staff');

        $superuser = false;

        $permissions = array();
        if ($staff) {
            // TODO: if they dont have "users" permission checked, ignore "users_list" etc
            if (is_array($request->permissions)) {
                foreach ($request->permissions as $permission => $value) {
                    if ($value) {
                        $permissions[] = $permission;
                    }
                }
            }
            $superuser = $request->has('superuser');
        }

        DB::table('roles')
            ->where('id', $request->id)
            ->update(['name' => $request->name, 'order' => $request->order, 'superuser' => $superuser, 'staff' => $staff, 'permissions' => $permissions]);

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
                    'role' => $new_role->id,
                    'password' => null
                ];
            } else {
                $fields = [
                    'role' => $new_role->id
                ];
            }

            DB::table('users')->where('role', $old_role->id)->update($fields);

            $message = 'Deleted role ' . $old_role->name . ', and placed all it\'s users into ' . $new_role->name . '.';
        }

        return redirect()->route('settings')->with('success', $message);
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
