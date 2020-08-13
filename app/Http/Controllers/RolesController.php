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
            'order' => 'required|numeric|unique:roles,order',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $role = new Roles();
        $role->name = $request->name;
        $role->order = $request->order;
        $role->staff = $request->has('staff');
        $role->superuser = $request->has('superuser');
        $role->permissions = json_encode($request->permissions);
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
        if ($request->has('superuser')) $superuser = true;

        DB::table('roles')
            ->where('id', $request->id)
            ->update(['name' => $request->name, 'order' => $request->order, 'superuser' => $superuser, 'staff' => $staff, 'permissions' => json_encode($request->permissions)]);
        return redirect()->route('settings')->with('success', 'Edited role ' . $request->name . '.');
    }

    public function delete(Request $request)
    {
        // TODO
    }
}
