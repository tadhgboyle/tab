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
            $this->_roles = Role::where('deleted', false)->orderBy('order', $order)->get();
        }

        return $this->_roles;
    }

    public function getStaffRoles(): array
    {
        if ($this->_staff_roles == null) {
            $this->_staff_roles = Role::select('id', 'name')->orderBy('order', 'ASC')->where([['staff', true], ['deleted', false]])->get()->toArray();
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
