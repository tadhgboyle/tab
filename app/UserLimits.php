<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLimits extends Model
{
    protected $primaryKey = 'limit_id';
    protected $fillable = ['user_id', 'category', 'limit_per', 'duration', 'editor_id'];
    //
}
