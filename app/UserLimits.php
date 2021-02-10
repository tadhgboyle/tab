<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLimits extends Model
{

    protected $primaryKey = 'limit_id';

    protected $fillable = [
        'user_id',
        'category',
        'limit_per',
        'duration',
        'editor_id'
    ];

    protected $casts = [
        'limit_per' => 'float'
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function category()
    {
        //TODO: Categories have to become their own models... hasOne(Category::class)
    }
}
