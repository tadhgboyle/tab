<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rotation extends Model
{
    use HasFactory;
    use SoftDeletes;

    // rotation list in settings
    // rotation create/edit page

    // user list 
    // - show only users in x rotation (and permission)

    // transaction list
    // - show only transactions in x rotation (and permission)
    // - update statistics page to use "all rotations" or "x rotation"
}
