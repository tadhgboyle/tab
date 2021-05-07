<?php

namespace App\Models;

use App\Casts\CategoryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLimits extends Model
{
    use HasFactory;

    protected $primaryKey = 'limit_id';

    protected $fillable = [
        'user_id',
        'category_id',
        'limit_per',
        'duration',
        'editor_id',
    ];

    protected $casts = [
        'limit_per' => 'float',
        'category_id' => CategoryType::class,
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
