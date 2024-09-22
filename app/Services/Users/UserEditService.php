<?php

namespace App\Services\Users;

use App\Models\User;
use App\Services\HttpService;
use App\Http\Requests\UserRequest;
use Illuminate\Http\RedirectResponse;
use App\Services\Users\UserLimits\UserLimitUpsertService;

class UserEditService extends HttpService
{
    use UserService;

    public const RESULT_CANT_MANAGE_THAT_ROLE = 'CANT_MANAGE_THAT_ROLE';
    public const RESULT_SUCCESS = 'SUCCESS';

    public function __construct(UserRequest $request, User $user)
    {
        $this->_user = $user;

        if (!auth()->user()->role->getRolesAvailable()->pluck('id')->contains($request->role_id)) {
            $this->_result = self::RESULT_CANT_MANAGE_THAT_ROLE;
            $this->_message = 'You cannot manage users with that role.';
            return;
        }

        // Update their category limits
        $userLimitsUpsertService = new UserLimitUpsertService($user, $request);
        if (!in_array($userLimitsUpsertService->getResult(), [UserLimitUpsertService::RESULT_SUCCESS, UserLimitUpsertService::RESULT_SUCCESS_NULL_DATA])) {
            $this->_result = $userLimitsUpsertService->getResult();
            $this->_message = $userLimitsUpsertService->getMessage();
            return;
        }

        $user->rotations()->sync($request->rotations);

        $fields = [
            'full_name' => $request->full_name,
            'username' => $request->username,
            'balance' => $request->balance,
            'role_id' => $request->role_id,
        ];

        if ($request->password) {
            $fields['password'] = bcrypt($request->password);
        }

        $user->update($fields);

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = "Updated user {$request->full_name}";
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS => redirect()->route('users_view', $this->_user)->with('success', $this->getMessage()),
            default => redirect()->back()->with('error', $this->getMessage()),
        };
    }
}
