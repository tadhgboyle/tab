<?php

namespace App\Http\Controllers;

use App\Role;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule as ValidationRule;

class RoleController extends Controller
{

    private static ?RoleController $_instance = null;

    public static function getInstance(): RoleController
    {
        if (self::$_instance == null) {
            self::$_instance = new RoleController;
        }
        return self::$_instance;
    }

    private ?Collection $_roles = null;
    private ?array $_staff_roles = null;


    public function getRoles(string $order = 'DESC'): object
    {
        if ($this->_roles == null) {
            $this->_roles = Role::orderBy('order', $order)->get();
        }
        return $this->_roles;
    }

    public function getStaffRoles(): array
    {
        if ($this->_staff_roles == null) {
            $this->_staff_roles = Role::select('id', 'name')->orderBy('order', 'ASC')->where('staff', true)->get()->toArray();
        }

        return $this->_staff_roles;
    }

    public function new(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|unique:roles,name',
            'order' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $staff = $request->has('staff');

        $superuser = false;

        if ($staff) {
            $permissions = array();
            if (is_array($request->permissions)) {
                foreach ($request->permissions as $permission => $value) {
                    if ($value) $permissions[] = $permission;
                }
            }
            $permissions = json_encode($permissions);
            if ($request->has('superuser')) {
                $superuser = true;
            }
        } else {
            $permissions = '[]';
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
    
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'min:2',
                ValidationRule::unique('roles')->ignore($request->id, 'id')
            ],
            'order' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $staff = $request->has('staff');

        $superuser = false;

        if ($staff) {
            $permissions = array();
            // TODO: if they dont have "users" permission checked, ignore "users_list" etc
            if (is_array($request->permissions)) {
                foreach ($request->permissions as $permission => $value) {
                    if ($value) {
                        $permissions[] = $permission;
                    }
                }
            }
            $permissions = json_encode($permissions);
            $superuser = $request->has('superuser');
        } else {
            $permissions = '[]';
        }

        DB::table('roles')
            ->where('id', $request->id)
            ->update(['name' => $request->name, 'order' => $request->order, 'superuser' => $superuser, 'staff' => $staff, 'permissions' => $permissions]);

        return redirect()->route('settings')->with('success', 'Edited role ' . $request->name . '.');
    }

    public function delete(Request $request, $id)
    {
        // TODO: Test
        $new_role = Role::find($request->get('new_role'));
        $old_role = Role::find($id);
        
        $fields = [
            'role' => $new_role->id
        ];
        if (!$new_role->staff) {
            $fields[] = [
                'password' => null
            ];
        }

        $users = User::all()->where('role', $id);
        foreach($users as $user) {
            $user->update($fields);
        }

        $old_role->update(['deleted' => true]);
    }

    public function order() {

        $roles = json_decode(\Request::get('roles'))->roles;
        
        $i = 1;
        foreach ($roles as $role) {
            Role::find($role)->update(['order' => $i]);
            $i++;
        }

        return $roles;
    }
}
