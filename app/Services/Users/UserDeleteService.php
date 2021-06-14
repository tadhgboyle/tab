<?php

namespace App\Services\Users;

use App\Models\User;
use App\Services\Service;
use Illuminate\Http\RedirectResponse;

class UserDeleteService extends Service
{
    use UserService;

    public const RESULT_SUCCESS = 0;

    public function __construct(User | int $user)
    {
        if ($user instanceof User) {
            $this->_user = $user;
        } else {
            $user = User::find($user);

            if ($user == null) {
                return redirect()->route('users_list')->with('error', 'No user found with that ID.')->send();
            }

            $this->_user = $user;
        }

        $user->update(['deleted' => true]);

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = "Deleted user {$user->full_name}";
    }

    public function redirect(): RedirectResponse
    {
        return redirect()->route('users_list')->with('success', $this->getMessage());
    }
}
