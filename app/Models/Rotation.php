<?php

namespace App\Models;

use App\Helpers\RotationHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rotation extends Model
{
    public const STATUS_PRESENT = 0;
    public const STATUS_FUTURE = 1;
    public const STATUS_PAST = 2;

    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'start',
        'end',
    ];

    protected $dates = [
        'start',
        'end',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function isPresent(): bool
    {
        return $this->getStatus() === self::STATUS_PRESENT;
    }

    public function getStatus(): int
    {
        if (resolve(RotationHelper::class)->getCurrentRotation()?->id === $this->id) {
            return self::STATUS_PRESENT;
        }

        if ($this->start->isFuture()) {
            return self::STATUS_FUTURE;
        }

        if ($this->end->isPast()) {
            return self::STATUS_PAST;
        }

        return -1;
    }

    public function getStatusHtml(): string
    {
        return match ($this->getStatus()) {
            self::STATUS_PRESENT => '<span class="tag is-success is-medium">Present</span>',
            self::STATUS_FUTURE => '<span class="tag is-warning is-medium">Future</span>',
            self::STATUS_PAST => '<span class="tag is-warning is-medium">Past</span>',
            default => "Unknown Status: {$this->getStatus()}",
        };
    }

    // rotation list in settings - DONE
    // rotation create/edit page - DONE
    // - fix dates autofilling if editing

    // user page
    // - editing/creating: multi-select Rotations - DONE
    // - viewing: view Rotations - DONE

    // user list (and order making user list)
    // - default: show users in RotationHelper->getCurrentRotation(). if current rotation is null, show all and disable dropdown (if they have permission to see dropdown)
    // - extra permission: show only users in x rotation

    // transaction list
    // - default: show transactions in RotationHelper->getCurrentRotation(). if current rotation is null, show all and disable dropdown (if they have permission to see dropdown)
    // - extra permission: show only transactions in x rotation

    // statistics page
    // - update statistics page to use "all rotations" or "x rotation" - DONE

    // misc
    // - if no rotation is in action (ie: one ends on saturday morning and the next starts on sunday afternoon), dont allow staff to login without extra permission
    // - update all tests
}
