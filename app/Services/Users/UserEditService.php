<?php

namespace App\Services\Users;

use App\Models\Role;
use App\Models\User;
use App\Services\Service;
use App\Helpers\RoleHelper;
use App\Helpers\UserLimitsHelper;
use App\Http\Requests\UserRequest;
use Illuminate\Http\RedirectResponse;

class UserEditService extends Service
{
    use UserService;

    public const RESULT_CANT_MANAGE_THAT_ROLE = 0;
    public const RESULT_INVALID_LIMIT = 1;
    public const RESULT_SUCCESS_IGNORED_PASSWORD = 2;
    public const RESULT_SUCCESS_APPLIED_PASSWORD = 3;

    private UserRequest $_request;

    public function __construct(UserRequest $request)
    {
        $this->_request = $request;
        $user = User::find($this->_request->id);
        $this->_user = $user;

        if (!auth()->user()->role->getRolesAvailable()->pluck('id')->contains($this->_request->role_id)) {
            $this->_result = self::RESULT_CANT_MANAGE_THAT_ROLE;
            $this->_message = 'You cannot manage users with that role.';
            return;
        }

        $old_role = $user->role;
        $new_role = Role::find($this->_request->role_id);

        // Update their category limits
        [$message, $result] = UserLimitsHelper::createOrEditFromRequest($this->_request, $user, $this::class);
        if (!is_null($message) && !is_null($result)) {
            $this->_message = $message;
            $this->_result = $result;
            return;
        }

        foreach ($user->rotations as $rotation) {
            if (!in_array($rotation->id, $this->_request->rotations)) {
                $user->rotations()->detach($rotation->id);
            }
        }

        foreach ($this->_request->rotations as $rotation_id) {
            $user->rotations()->attach($rotation_id);
        }

        // If same role or changing from one staff role to another
        if ($old_role->id == $new_role->id || (RoleHelper::getInstance()->isStaffRole($old_role->id) && RoleHelper::getInstance()->isStaffRole($new_role->id))) {
            $user->update([
                'full_name' => $this->_request->full_name,
                'username' => $this->_request->username,
                'balance' => $this->_request->balance,
                'role_id' => $this->_request->role_id
            ]);

            $this->_result = self::RESULT_SUCCESS_IGNORED_PASSWORD;
            $this->_message = "Updated user {$this->_request->full_name}";
            return;
        }

        // Determine if their password should be created or removed
        if (!RoleHelper::getInstance()->isStaffRole($old_role->id) && RoleHelper::getInstance()->isStaffRole($new_role->id)) {
            $password = bcrypt($this->_request->password);
        } else {
            $password = null;
        }

        $user->update([
            'full_name' => $this->_request->full_name,
            'username' => $this->_request->username,
            'balance' => $this->_request->balance,
            'role_id' => $this->_request->role_id,
            'password' => $password
        ]);

        $this->_result = self::RESULT_SUCCESS_APPLIED_PASSWORD;
        $this->_message = "Updated user {$this->_request->full_name}";
    }

    public function redirect(): RedirectResponse
    {
        switch ($this->getResult()) {
            case self::RESULT_SUCCESS_IGNORED_PASSWORD:
            case self::RESULT_SUCCESS_APPLIED_PASSWORD:
                return redirect()->route('users_list')->with('success', $this->getMessage());
            default:
                return redirect()->back()->withInput()->with('error', $this->getMessage());
        }
    }
}
