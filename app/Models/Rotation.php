<?php

namespace App\Models;

use App\Helpers\RotationHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Rotation extends Model
{
    public const STATUS_PRESENT = 0;
    public const STATUS_FUTURE = 1;
    public const STATUS_PAST = 2;

    use QueryCacheable;
    use HasFactory;
    use SoftDeletes;

    protected $cacheFor = 180;

    protected $dates = [
        'start',
        'end',
    ];

    public function getStatus(): int
    {
        if (RotationHelper::getInstance()->getCurrentRotation() == $this) {
            return self::STATUS_PRESENT;
        }

        if ($this->start->isFuture()) {
            return self::STATUS_FUTURE;
        }

        if ($this->end->isPast()) {
            return self::STATUS_PAST;
        }
    }

    // rotation list in settings - DONE
    // rotation create/edit page - DONE
    // - fix dates autofilling if editing

    // user page
    // - editing: multi-select Rotations
    // - viewing: view Rotations

    // user list (and order making user list)
    // - default: show users in RotationHelper->getCurrentRotation(). if current rotation is null, show all and disable dropdown (if they have permission to see dropdown)
    // - extra permission: show only users in x rotation

    // transaction list
    // - default: show transactions in RotationHelper->getCurrentRotation(). if current rotation is null, show all and disable dropdown (if they have permission to see dropdown)
    // - extra permission: show only transactions in x rotation

    // statistics page
    // - update statistics page to use "all rotations" or "x rotation"

    // misc
    // - if no rotation is in action (ie: one ends on saturday morning and the next starts on sunday afternoon), dont allow staff to login without extra permission
    // - update all tests
}
