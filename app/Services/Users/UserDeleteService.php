<?php

namespace App\Services\Users;

use App\Models\User;
use App\Services\Service;
use Illuminate\Http\RedirectResponse;

class UserDeleteService extends Service
{
    use UserService;

    public const RESULT_SUCCESS = 0;
    public const RESULT_NOT_EXIST = 1;

    public function __construct(int $user_id)
    {
        $user = User::find($user_id);

        if ($user === null) {
            $this->_result = self::RESULT_NOT_EXIST;
            $this->_message = 'No user found with that ID.';
            return;
        }

        $this->_user = $user;

        $user->delete();

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = "Deleted user $user->full_name";
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS => redirect()->route('users_list')->with('success', $this->getMessage()),
            self::RESULT_NOT_EXIST => redirect()->route('users_list')->with('error', $this->getMessage()),
        };
    }
}
