<?php

namespace App;

use Carbon\Carbon;
use App\Helpers\SettingsHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Activity extends Model
{
    use QueryCacheable;

    protected $cacheFor = 180;

    protected $casts = [
        'name' => 'string',
        'location' => 'string',
        'description' => 'string',
        'unlimited_slots' => 'boolean',
        'slots' => 'integer',
        'price' => 'float',
        'pst' => 'boolean',
        'deleted' => 'boolean',
    ];

    protected $dates = [
        'start',
        'end',
    ];

    protected $fillable = [
        'deleted',
    ];

    private ?Collection $_current_attendees = null;

    // reusable function so we only query once
    private function getCurrentAttendees(): Collection
    {
        if ($this->_current_attendees == null) {
            $this->_current_attendees = DB::table('activity_transactions')->where('activity_id', $this->id)->get('user_id');
        }

        return $this->_current_attendees;
    }

    public function slotsAvailable(): int
    {
        if ($this->unlimited_slots) {
            return -1;
        }

        $current_attendees = $this->getCurrentAttendees()->count();
        return $this->slots - $current_attendees;
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
        return $this->price * SettingsHelper::getInstance()->getGst();
    }

    public function getAttendees(): array
    {
        $users = [];
        foreach ($this->getCurrentAttendees() as $attendee) {
            $users[] = User::find($attendee->user_id);
        }

        return $users;
    }

    public function isAttending(User $user): bool
    {
        return $this->getCurrentAttendees()->contains('user_id', $user->id);
    }

    public function getStatus(): string
    {
        if (Carbon::parse($this->end)->isPast()) {
            return '<span class="tag is-danger is-medium">Over</span>';
        } else {
            if (Carbon::parse($this->start)->isPast()) {
                return '<span class="tag is-warning is-medium">In Progress</span>';
            } else {
                return '<span class="tag is-success is-medium">Waiting</span>';
            }
        }
    }

    public function registerUser(User $user): bool
    {
        if ($this->isAttending($user)) {
            return false;
        }

        if (!$this->hasSlotsAvailable()) {
            return false;
        }

        $balance = ($user->balance - $this->getPrice());
        if (!($user->balance >= $balance)) {
            return false;
        }

        $user->update(['balance' => $balance]);
        DB::table('activity_transactions')->insert([
            'user_id' => $user->id,
            'cashier_id' => auth()->id(),
            'activity_id' => $this->id,
            'activity_price' => $this->price,
            'activity_gst' => SettingsHelper::getInstance()->getGst(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return true;
    }
}
