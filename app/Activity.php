<?php

namespace App;

use App\Http\Controllers\SettingsController;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{

    protected $fillable = ['attendees'];

    protected $casts = [
        'unlimited_slots' => 'boolean',
        'slots' => 'integer'
    ];

    protected $dates = [
        'start',
        'end'
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

    public function getStatus(): string
    {
        if (Carbon::parse($this->end)->isPast()) {
            return "<span class=\"tag is-danger is-medium\">Over</span>";
        } else if (Carbon::parse($this->start)->isPast()) {
            return "<span class=\"tag is-warning is-medium\">In Progress</span>";
        } else return "<span class=\"tag is-success is-medium\">Waiting</span>";;
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
