<?php

namespace App\Models;

use App\Helpers\SettingsHelper;
use App\Helpers\UserLimitsHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\QueryCache\Traits\QueryCacheable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
    use QueryCacheable;
    use HasFactory;
    use SoftDeletes;

    protected int $cacheFor = 180;

    protected $fillable = [
        'name',
        'category_id',
        'location',
        'description',
        'unlimited_slots',
        'slots',
        'price',
        'start',
        'end',
    ];

    protected $casts = [
        'name' => 'string',
        'location' => 'string',
        'description' => 'string',
        'unlimited_slots' => 'boolean',
        'slots' => 'integer',
        'price' => 'float',
        'pst' => 'boolean',
    ];

    protected $dates = [
        'start',
        'end',
    ];

    public function category(): HasOne
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    public function getCurrentAttendees(): Collection
    {
        return DB::table('activity_transactions')->where('activity_id', $this->id)->pluck('user_id');
    }

    public function slotsAvailable(): int
    {
        if ($this->unlimited_slots) {
            return -1;
        }

        return $this->slots - $this->getCurrentAttendees()->count();
    }

    public function hasSlotsAvailable(int $count = 1): bool
    {
        if ($this->unlimited_slots) {
            return true;
        }

        $current_attendees = $this->getCurrentAttendees()->count();
        return ($this->slots - ($current_attendees + $count)) >= 0;
    }

    public function getPrice(): float
    {
        return $this->price * resolve(SettingsHelper::class)->getGst();
    }

    public function getAttendees(): array
    {
        return $this->getCurrentAttendees()->map(static function (int $userId): User {
            return User::find($userId);
        })->all();
    }

    public function isAttending(User $user): bool
    {
        return $this->getCurrentAttendees()->contains($user->id);
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

    public function registerUser(User $user): RedirectResponse
    {
        if ($this->isAttending($user)) {
            return redirect()->back()->with('error', "Could not register $user->full_name for $this->name, they are already attending this activity.");
        }

        if (!$this->hasSlotsAvailable()) {
            return redirect()->back()->with('error', "Could not register $user->full_name for $this->name, this activity is out of slots.");
        }

        if ($this->getPrice() > $user->balance) {
            return redirect()->back()->with('error', "Could not register $user->full_name for $this->name, they do not have enough balance.");
        }

        if (!UserLimitsHelper::canSpend($user, $this->getPrice(), $this->category_id)) {
            return redirect()->back()->with('error', "Could not register $user->full_name for $this->name, they have reached their limit for the {$this->category->name} category.");
        }

        DB::table('activity_transactions')->insert([
            'user_id' => $user->id,
            'cashier_id' => auth()->id(),
            'activity_id' => $this->id,
            'activity_price' => $this->price,
            'activity_gst' => resolve(SettingsHelper::class)->getGst(),
            'total_price' => $this->getPrice(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user->decrement('balance', $this->getPrice());

        return redirect()->back()->with('success', "Successfully registered $user->full_name to $this->name.");
    }
}
