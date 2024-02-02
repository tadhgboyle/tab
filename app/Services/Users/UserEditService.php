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

    public const RESULT_CANT_MANAGE_THAT_ROLE = 'CANT_MANAGE_THAT_ROLE';
    public const RESULT_INVALID_LIMIT = 'INVALID_LIMIT';
    public const RESULT_SUCCESS_IGNORED_PASSWORD = 'SUCCESS_IGNORED_PASSWORD';
    public const RESULT_SUCCESS_APPLIED_PASSWORD = 'SUCCESS_APPLIED_PASSWORD';

    private UserRequest $_request;

    public function __construct(UserRequest $request, User $user)
    {
        $this->_request = $request;
        $this->_user = $user;

        if (!auth()->user()->role->getRolesAvailable()->pluck('id')->contains($this->_request->role_id)) {
            $this->_result = self::RESULT_CANT_MANAGE_THAT_ROLE;
            $this->_message = 'You cannot manage users with that role.';
            return;
        }

        $old_role = $user->role;
        $new_role = Role::find($this->_request->role_id);

        // Update their category limits
        [$message, $result] = UserLimitsHelper::createOrEditFromRequest($this->_request, $user, self::class);
        if (!is_null($message) && !is_null($result)) {
            $this->_message = $message;
            $this->_result = $result;
            return;
        }

        foreach ($user->rotations as $rotation) {
            if (!in_array($rotation->id, $this->_request->rotations, true)) {
                $user->rotations()->detach($rotation->id);
            }
        }

        foreach ($this->_request->rotations as $rotation_id) {
            if (!in_array($rotation_id, $user->rotations->pluck('id')->toArray(), true)) {
                $user->rotations()->attach($rotation_id);
            }
        }

        $roleHelper = resolve(RoleHelper::class);

        // If same role or changing from one staff role to another
        if ($old_role->id === $new_role->id || ($roleHelper->isStaffRole($old_role->id) && $roleHelper->isStaffRole($new_role->id))) {
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
        if (!$roleHelper->isStaffRole($old_role->id) && $roleHelper->isStaffRole($new_role->id)) {
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
        return match ($this->getResult()) {
            self::RESULT_SUCCESS_IGNORED_PASSWORD, self::RESULT_SUCCESS_APPLIED_PASSWORD => redirect()->route('users_list')->with('success', $this->getMessage()),
            default => redirect()->back()->with('error', $this->getMessage()),
        };
    }
}
