<?php

namespace App\Models;

use App\Casts\CategoryType;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'limit',
        'duration',
    ];

    protected $casts = [
        'limit' => MoneyIntegerCast::class,
        'category_id' => CategoryType::class,
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function category(): HasOne
    {
        return $this->hasOne(Category::class);
    }
}
