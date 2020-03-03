<?php

namespace App\Http\Controllers;

use Validator;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{

    public function new(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|min:4',
            'balance' => 'required|numeric',
            'role' => 'required',
            'password' => 'required_if:role,==,cashier|required_if:role,==,administrator|nullable|confirmed|min:6',
        ]);
        if ($validator->fails()) {
            return redirect('/users/new')
                ->withInput($request->all())
                ->withErrors($validator);
        }
        $user = new User();
        $user->full_name = $request->full_name;
        if (empty($request->username)) {
            $user->username = strtolower(str_replace(" ", "", $request->full_name));
        } else {
            $user->username = $request->username;
        }
        $user->balance = $request->balance;
        $user->role = $request->role;
        if ($request->role != "camper") {
            $user->password = bcrypt($request->password);
        }
        $user->save();
        return redirect('/users');
    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|min:4',
            'username' => 'required',
            'balance' => 'required|numeric',
            'role' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withInput($request->all())
                ->withErrors($validator);
        }
        $password = NULL;
        $old_role = strtolower(DB::table('users')->where('id', $request->id)->pluck('role')->first());
        $new_role = strtolower($request->role);
        $staff_roles = array(
            'cashier',
            'administrator'
        );
        // if same role or changing from one staff role to another
        if (($old_role == $new_role) || (in_array($old_role, $staff_roles) && in_array($new_role, $staff_roles))) {
            DB::table('users')
                ->where('id', $request->id)
                ->update(['full_name' => $request->full_name, 'username' => $request->username, 'balance' => $request->balance, 'role' => $request->role]);
            return redirect('/users');
        }
        // if old role is camper and new role is staff
        else if (!in_array($old_role, $staff_roles) && in_array($new_role, $staff_roles)) {
            if (isset($request->password)) {
                if ($request->password == $request->password_confirmation) {
                    $password = bcrypt($request->password);
                } else {
                    return redirect()->back()->with('error', 'Please confirm the password')->withInput($request->all());
                }
            } else {
                return redirect()->back()->with('error', 'Please enter a password')->withInput($request->all());
            }
        }
        // if new role is camper
        else {
            $password = NULL;
        }
        DB::table('users')
            ->where('id', $request->id)
            ->update(['full_name' => $request->full_name, 'username' => $request->username, 'balance' => $request->balance, 'role' => $request->role, 'password' => $password]);
        return redirect('/users');
    }

    public function delete($id)
    {
        User::where('id', $id)->delete();
        return redirect('/users');
    }
}
