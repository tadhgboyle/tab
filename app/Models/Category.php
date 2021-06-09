<?php

namespace App\Models;

use App\Casts\CategoryType;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/** @method static Category find */
class Category extends Model
{
    use QueryCacheable;
    use HasFactory;

    protected $cacheFor = 180;

    protected $fillable = [
        'deleted',
        'name',
        'type',
    ];

    protected $casts = [
        'name' => 'string',
        'type' => CategoryType::class, // $category->type->name (ie: "Products Only") + $category->type->id (ie: 2)
        'deleted' => 'boolean',
    ];
}
