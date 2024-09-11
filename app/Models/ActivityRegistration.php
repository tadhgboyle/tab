<?php

namespace App\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// TODO support gift cards, some sort of abstract order service maybe to de-dupe?
class ActivityRegistration extends Model
{
    use HasFactory;

    protected $casts = [
        'activity_price' => MoneyIntegerCast::class,
        'activity_gst' => 'float',
        'activity_pst' => 'float',
        'total_price' => MoneyIntegerCast::class,
        'returned' => 'boolean',
    ];

    protected $fillable = [
        'activity_id',
        'user_id',
        'cashier_id',
        'category_id',
        'activity_price',
        'activity_gst',
        'activity_pst',
        'total_price',
        'rotation_id',
        'returned',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function rotation(): BelongsTo
    {
        return $this->belongsTo(Rotation::class);
    }
}
