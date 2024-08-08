<?php

namespace App\Services\Users;

use App\Models\Role;
use App\Models\User;
use App\Helpers\RoleHelper;
use App\Services\HttpService;
use App\Http\Requests\UserRequest;
use Illuminate\Http\RedirectResponse;
use App\Services\Users\UserLimits\UserLimitUpsertService;

class UserEditService extends HttpService
{
    use UserService;

    public const RESULT_CANT_MANAGE_THAT_ROLE = 'CANT_MANAGE_THAT_ROLE';
    public const RESULT_INVALID_LIMIT = 'INVALID_LIMIT';
    public const RESULT_SUCCESS_IGNORED_PASSWORD = 'SUCCESS_IGNORED_PASSWORD';
    public const RESULT_SUCCESS_APPLIED_PASSWORD = 'SUCCESS_APPLIED_PASSWORD';

    public function __construct(UserRequest $request, User $user)
    {
        $this->_user = $user;

        if (!auth()->user()->role->getRolesAvailable()->pluck('id')->contains($request->role_id)) {
            $this->_result = self::RESULT_CANT_MANAGE_THAT_ROLE;
            $this->_message = 'You cannot manage users with that role.';
            return;
        }

        $old_role = $user->role;
        $new_role = Role::find($request->role_id);

        // Update their category limits
        $userLimitsUpsertService = new UserLimitUpsertService($user, $request);
        if (!in_array($userLimitsUpsertService->getResult(), [UserLimitUpsertService::RESULT_SUCCESS, UserLimitUpsertService::RESULT_SUCCESS_NULL_DATA])) {
            $this->_result = $userLimitsUpsertService->getResult();
            $this->_message = $userLimitsUpsertService->getMessage();
            return;
        }

        foreach ($user->rotations as $rotation) {
            if (!in_array($rotation->id, $request->rotations, true)) {
                $user->rotations()->detach($rotation->id);
            }
        }

        foreach ($request->rotations as $rotation_id) {
            if (!in_array($rotation_id, $user->rotations->pluck('id')->toArray(), true)) {
                $user->rotations()->attach($rotation_id);
            }
        }

        $roleHelper = resolve(RoleHelper::class);

        // If same role or changing from one staff role to another
        if ($old_role->id === $new_role->id || ($roleHelper->isStaffRole($old_role->id) && $roleHelper->isStaffRole($new_role->id))) {
            $user->update([
                'full_name' => $request->full_name,
                'username' => $request->username,
                'balance' => $request->balance,
                'role_id' => $request->role_id
            ]);

            $this->_result = self::RESULT_SUCCESS_IGNORED_PASSWORD;
            $this->_message = "Updated user {$request->full_name}";
            return;
        }

        // Determine if their password should be created or removed
        if (!$roleHelper->isStaffRole($old_role->id) && $roleHelper->isStaffRole($new_role->id)) {
            $password = bcrypt($request->password);
        } else {
            $password = null;
        }

        $user->update([
            'full_name' => $request->full_name,
            'username' => $request->username,
            'balance' => $request->balance,
            'role_id' => $request->role_id,
            'password' => $password
        ]);

        $this->_result = self::RESULT_SUCCESS_APPLIED_PASSWORD;
        $this->_message = "Updated user {$request->full_name}";
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS_IGNORED_PASSWORD, self::RESULT_SUCCESS_APPLIED_PASSWORD => redirect()->route('users_list')->with('success', $this->getMessage()),
            default => redirect()->back()->with('error', $this->getMessage()),
        };
    }
}
