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
        return $this->hasOne(User::class, 'id');
    }

    public function editor()
    {
        return $this->hasOne(User::class, 'id');
    }

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category');
    }
}
