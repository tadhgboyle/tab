<?php

namespace App\Models;

use App\Enums\RotationStatus;
use App\Helpers\RotationHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rotation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'start',
        'end',
    ];

    protected $casts = [
        'start' => 'date',
        'end' => 'date',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function isPresent(): bool
    {
        return $this->getStatus() === RotationStatus::Present;
    }

    public function isFuture(): bool
    {
        return $this->getStatus() === RotationStatus::Future;
    }

    public function getStatus(): RotationStatus
    {
        if (resolve(RotationHelper::class)->getCurrentRotation()?->id === $this->id) {
            return RotationStatus::Present;
        }

        if ($this->start->isFuture()) {
            return RotationStatus::Future;
        }

        return RotationStatus::Past;
    }

    // cashier page:
    // -- dropdown selector if they have perm, otherwise current/alert if current is null

    // order list
    // - default: show orders in RotationHelper->getCurrentRotation(). if current rotation is null, show all and disable dropdown (if they have permission to see dropdown)
    // - extra permission: show only orders in x rotation

    // misc
    // - if no rotation is in action (ie: one ends on saturday morning and the next starts on sunday afternoon), dont allow staff to login without extra permission
    // - update all tests
}
