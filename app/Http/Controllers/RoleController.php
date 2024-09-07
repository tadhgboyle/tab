<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Http\Requests\RoleRequest;
use Illuminate\Http\RedirectResponse;

class RoleController extends Controller
{
    public function create(PermissionHelper $permissionHelper)
    {
        return view('pages.settings.roles.form', [
            'permissionHelper' => $permissionHelper,
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $staff = $request->has('staff');
        $superuser = $staff && $request->has('superuser');
        $permissions = $staff
            ? PermissionHelper::parseNodes($request->permissions)
            : [];

        $role = new Role();
        $role->name = $request->name;
        $role->order = $request->order;
        $role->staff = $staff;
        $role->superuser = $superuser;
        $role->permissions = $permissions;
        $role->save();

        return redirect()->route('settings')->with('success', 'Created role ' . $role->name . '.');
    }

    public function edit(PermissionHelper $permissionHelper, Role $role)
    {
        if (!auth()->user()->role->canInteract($role)) {
            return redirect()->route('settings')->with('error', 'You cannot interact with that role.');
        }

        return view('pages.settings.roles.form', [
            'role' => $role,
            'affected_users' => $role->users,
            'available_roles' => $role->getRolesAvailable(auth()->user()->role)->all(),
            'permissionHelper' => $permissionHelper,
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        if (!auth()->user()->role->canInteract($role)) {
            return redirect()->route('settings')->with('error', 'You cannot interact with that role.');
        }

        // TODO: what do we do when they make a role staff which has users in it already

        $staff = $request->has('staff');
        $superuser = $staff && $request->has('superuser');
        $permissions = $staff
            ? PermissionHelper::parseNodes($request->permissions)
            : [];

        $fields = [
            'name' => $request->name,
        ];

        if (!$role->superuser) {
            $fields = array_merge($fields, [
                'order' => $request->order,
                'staff' => $staff,
                'superuser' => $superuser,
                'permissions' => $permissions,
            ]);
        }

        $role->update($fields);

        return redirect()->route('settings')->with('success', 'Edited role ' . $role->name . '.');
    }

    public function delete(Request $request, Role $role)
    {
        if ($role->id == auth()->user()->role_id) {
            return redirect()->back()->with('error', 'You cannot delete your own role.');
        }

        // TODO: add same validation from frontend
        // TODO: tests
        if (!$request->has('new_role')) {
            $role->delete();

            $message = 'Deleted role ' . $role->name . '.';
        } else {
            $new_role = Role::find($request->new_role);

            $fields = [
                'role_id' => $new_role->id,
            ];

            if (!$new_role->staff) {
                $fields = array_merge($fields, [
                    'password' => null,
                ]);
            }

            $role->users()->update($fields);

            $role->delete();

            $message = "Deleted role {$role->name}, and placed all it's users into {$new_role->name}.";
        }

        return redirect()->route('settings')->with('success', $message);
    }
}
