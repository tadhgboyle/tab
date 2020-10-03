<?php

namespace App;

use App\Http\Controllers\SettingsController;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $casts = [
        'unlimited_slots' => 'boolean',
        'slots' => 'integer',
        'pst' => 'boolean'
    ];

    public function slotsAvailable(): int 
    {
        if ($this->unlimited_slots) return -1;
        $current_attendees = count(json_decode($this->attendees, true));
        return ($this->slots - $current_attendees);
    }

    public function hasSlotsAvailable(int $count = 1): bool 
    {
        if ($this->unlimited_slots) return true;
        $current_attendees = count(json_decode($this->attendees, true));
        return ($this->slots - ($current_attendees + $count)) >= 0;
    }

    public function getPrice(): float 
    {
        $price = $this->price;
        if ($this->pst) {
            $price = $price * SettingsController::getPst();
        }
        $price = $price * SettingsController::getGst();
        return $price;
    }

    public function getAttendees(): array
    {
        $users = array();
        $attendees = json_decode($this->attendees, true);
        foreach($attendees as $attendee) {
            $users[] = User::find($attendee);
        }
        return $users;
    }

    public function registerUser(User $user): bool 
    {
        if ($this->hasSlotsAvailable()) {
            $balance = ($user->balance - $this->getPrice());
            if ($balance >= 0) {
                $current_attendees = json_decode($this->attendees, true);
                $current_attendees[] = $user->id;
                $this->update(['attendees' => json_encode($current_attendees)]);
                $user->update(['balance' => $balance]);
                return true;
            }
        }
        return false;
    }
}
