<?php

namespace App\Services\Users;

use App\Services\Service;
use App\Models\Role;
use App\Models\User;
use App\Models\Category;
use App\Models\UserLimits;
use App\Helpers\RoleHelper;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class UserEditService extends Service
{
    use UserService;

    public const RESULT_CANT_MANAGE_THAT_ROLE = 0;
    public const RESULT_NEGATIVE_LIMIT = 1;
    public const RESULT_CONFIRM_PASSWORD = 2;
    public const RESULT_ENTER_PASSWORD = 3;
    public const RESULT_SUCCESS = 4;

    private Request $_request;

    public function __construct(Request $request) {

        $this->_request = $request;
        $this->_user = User::find($this->_request->id);

        if (!in_array($this->_request->role_id, auth()->user()->role->getRolesAvailable()->pluck('id'))) {
            $this->_result = self::RESULT_CANT_MANAGE_THAT_ROLE;
            $this->_message = 'You cannot manage users with that role.';
            return;
        }

        $password = null;
        $old_role = $this->_user->role->name;

        $new_role = Role::find($this->_request->role_id)->name;
        $staff_roles = RoleHelper::getInstance()->getStaffRoles()->pluck('name')->toArray();

        // Update their category limits
        foreach ($this->_request->limit as $category_id => $limit) {
            $duration = $this->_request->duration[$category_id] ?: 0;
            if (empty($limit)) {
                $limit = -1;
            } else {
                if ($limit < -1) {
                    $this->_result = self::RESULT_NEGATIVE_LIMIT;
                    $this->_message = 'Limit must be above -1 for ' . Category::find($category_id)->name . '. (-1 means no limit)';
                    return;
                }
            }
            UserLimits::updateOrCreate(
                ['user_id' => $this->_request->id, 'category_id' => $category_id],
                ['limit_per' => $limit, 'duration' => $duration, 'editor_id' => auth()->id()]
            );
        }

        // TODO: This next part is fucking terrifying. Probably can find a better solution.
        // If same role or changing from one staff role to another
        if (($old_role == $new_role) || (in_array($old_role, $staff_roles) && in_array($new_role, $staff_roles))) {
            $this->_user->update($this->_request->all(['full_name', 'user_name', 'balance', 'role_id']));

            $this->_result = self::RESULT_SUCCESS;
            $this->_message = 'Updated user ' . $this->_request->full_name . '.';
            return;
        }
        // If old role is camper and new role is staff
        else {
            if (!in_array($old_role, $staff_roles) && in_array($new_role, $staff_roles)) {
                if (!empty($this->_request->password)) {
                    if ($this->_request->password == $this->_request->password_confirmation) {
                        $password = bcrypt($this->_request->password);
                    } else {
                        $this->_result = self::RESULT_CONFIRM_PASSWORD;
                        $this->_message = 'Please confirm the password.';
                        return;
                    }
                } else {
                    $this->_result = self::RESULT_ENTER_PASSWORD;
                    $this->_message = 'Please enter a password.';
                    return;
                }
            }
            // If new role is camper
            else {
                $password = null;
            }
        }

        $this->_user->update([
            'full_name' => $this->_request->full_name,
            'username' => $this->_request->username,
            'balance' => $this->_request->balance,
            'role_id' => $this->_request->role_id,
            'password' => $password
        ]);

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = 'Updated user ' . $this->_request->full_name . '.';

    }

    public function redirect(): RedirectResponse
    {
        switch ($this->getResult()) {
            case self::RESULT_SUCCESS:
                return redirect()->route('users_list')->with('success', $this->getMessage());
            default:
                return redirect()->back()->withInput()->with('error', $this->getMessage());
        }
    }
}
