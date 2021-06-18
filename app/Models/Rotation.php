<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Rotation extends Model
{
    use QueryCacheable;
    use HasFactory;
    use SoftDeletes;

    protected $cacheFor = 180;

    protected $casts = [
        'start' => 'date',
        'end' => 'date',
    ];

    // rotation list in settings
    // rotation create/edit page
    // - make "end" autofill if editing rotation

    // user list (and order making user list)
    // - default: show users in RotationHelper->getCurrentRotation(). if current rotation is null, show all
    // - extra permission: show only users in x rotation

    // transaction list
    // - default: show transactions in RotationHelper->getCurrentRotation(). if current rotation is null, show all
    // - extra permission: show only transactions in x rotation

    // statistics page
    // update statistics page to use "all rotations" or "x rotation"
}
