<?php

namespace App\Models;

use App\Casts\CategoryType;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }
}
