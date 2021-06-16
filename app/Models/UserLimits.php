<?php

namespace App\Models;

use App\Casts\CategoryType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserLimits extends Model
{
    public const LIMIT_DAILY = 0;
    public const LIMIT_WEEKLY = 1;

    use HasFactory;

    protected $primaryKey = 'limit_id';

    protected $fillable = [
        'user_id',
        'category_id',
        'limit_per',
        'duration',
    ];

    protected $casts = [
        'limit_per' => 'float',
        'category_id' => CategoryType::class,
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id');
    }

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category');
    }
}
