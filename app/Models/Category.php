<?php

namespace App\Models;

use App\Casts\CategoryType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
    ];

    protected $casts = [
        'name' => 'string',
        'type' => CategoryType::class, // $category->type->name (ie: "Products Only") + $category->type->id (ie: 2)
    ];
}
