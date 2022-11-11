<?php

namespace App\Models;

use App\Helpers\TaxHelper;
use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'category_id',
        'location',
        'description',
        'unlimited_slots',
        'slots',
        'price',
        'pst',
        'start',
        'end',
    ];

    protected $casts = [
        'name' => 'string',
        'location' => 'string',
        'description' => 'string',
        'unlimited_slots' => 'boolean',
        'slots' => 'integer',
        'price' => MoneyIntegerCast::class,
        'pst' => 'boolean',
    ];

    protected $dates = [
        'start',
        'end',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function slotsAvailable(): int
    {
        if ($this->unlimited_slots) {
            return -1;
        }

        return $this->slots - $this->attendants()->count();
    }

    public function hasSlotsAvailable(int $count = 1): bool
    {
        if ($this->unlimited_slots) {
            return true;
        }

        $current_attendees = $this->attendants()->count();
        return ($this->slots - ($current_attendees + $count)) >= 0;
    }

    public function getPriceAfterTax(): Money
    {
        return TaxHelper::calculateFor($this->price, 1, $this->pst);
    }

    public function attendants(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, ActivityRegistration::class, null, 'id', null, 'user_id');
    }

    public function isAttending(User $user): bool
    {
        return $this->attendants->contains($user);
    }

    public function getStatus(): string
    {
        if ($this->end->isPast()) {
            return '<span class="tag is-danger is-medium">Over</span>';
        }

        if ($this->start->isPast()) {
            return '<span class="tag is-warning is-medium">In Progress</span>';
        }

        return '<span class="tag is-success is-medium">Waiting</span>';
    }
}
