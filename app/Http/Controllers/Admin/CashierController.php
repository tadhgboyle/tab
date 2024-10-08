<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Helpers\Permission;
use App\Helpers\RotationHelper;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class CashierController extends Controller
{
    public function __invoke(RotationHelper $rotationHelper)
    {
        // TODO: similar handling of rotation selection/invalidity to statistics page
        return view('pages.admin.cashier', [
            'users' => User::query()
                ->unless(hasPermission(Permission::CASHIER_SELF_PURCHASES), function (EloquentBuilder $query) {
                    $query->where('users.id', '!=', auth()->id());
                })
                ->unless(hasPermission(Permission::CASHIER_USERS_OTHER_ROTATIONS), function (EloquentBuilder $query) {
                    $query->whereHas('rotations', function (EloquentBuilder $query) {
                        return $query->where('rotation_id', resolve(RotationHelper::class)->getCurrentRotation()->id);
                    });
                })
                ->select(['id', 'full_name', 'balance'])
                ->with('rotations')
                ->get(),
            'currentRotation' => $rotationHelper->getCurrentRotation()
        ]);
    }
}
