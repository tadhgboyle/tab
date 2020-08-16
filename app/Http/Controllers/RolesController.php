<?php

namespace App\Http\Controllers;

use App\Roles;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule as ValidationRule;

class RolesController extends Controller
{
    public function new(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|unique:roles,name',
            'order' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $permissions = array();
        if (is_array($request->permissions)) {
            foreach ($request->permissions as $permission => $value) {
                if ($value) $permissions[] = $permission;
                echo $permission;
            }
        }
        $permissions = json_encode($permissions);

        $role = new Roles();
        $role->name = $request->name;
        $role->order = $request->order;
        $role->staff = $request->has('staff');
        $role->superuser = $request->has('superuser');
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

        $staff = false;
        if ($request->has('staff')) $staff = true;

        $superuser = false;

        if ($staff) {
            $permissions = array();
            if (is_array($request->permissions)) {
                foreach ($request->permissions as $permission => $value) {
                    if ($value) $permissions[] = $permission;
                    echo $permission;
                }
            }
            $permissions = json_encode($permissions);
            if ($request->has('superuser')) $superuser = true;
        } else $permissions = '[]';

        // TODO: add "users" (etc) permissions automatically so they dont need to select another checkbox on form

        DB::table('roles')
            ->where('id', $request->id)
            ->update(['name' => $request->name, 'order' => $request->order, 'superuser' => $superuser, 'staff' => $staff, 'permissions' => $permissions]);
        return redirect()->route('settings')->with('success', 'Edited role ' . $request->name . '.');
    }

    public function delete(Request $request)
    {
        // TODO: Figure out what to do with users whos role was deleted....
    }
}
