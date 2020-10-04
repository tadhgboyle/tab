<?php

namespace App;

use App\Http\Controllers\SettingsController;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $current_attendees = DB::table('activity_transactions')->where('activity_id', $this->id)->get('user_id')->count();
        return ($this->slots - $current_attendees);
    }

    public function hasSlotsAvailable(int $count = 1): bool 
    {
        if ($this->unlimited_slots) return true;
        $current_attendees = DB::table('activity_transactions')->where('activity_id', $this->id)->get('user_id')->count();
        return ($this->slots - ($current_attendees + $count)) >= 0;
    }

    public function getPrice(): float 
    {
        return ($this->price * SettingsController::getGst());
    }

    public function getAttendees(): array
    {
        $attendees = DB::table('activity_transactions')->where('activity_id', $this->id)->get('user_id');
        $users = array();
        foreach($attendees as $attendee) {
            $users[] = User::find($attendee->user_id);
        }
        return $users;
    }

    public function isAttending(User $user): bool 
    {
        return DB::table('activity_transactions')->where('activity_id', $this->id)->get()->contains('user_id', $user->id);
    }

    public function getStatus(): string 
    {
        if (Carbon::parse($this->end)->isPast()) {
            return "<span class=\"tag is-danger is-medium\">Over</span>";
        } else if (Carbon::parse($this->start)->isPast()) {
            return "<span class=\"tag is-warning is-medium\">In Progress</span>";
        } else return "<span class=\"tag is-success is-medium\">Waiting</span>";
    }

    public function registerUser(User $user): bool 
    {
        if ($this->isAttending($user)) return false;
        if ($this->hasSlotsAvailable()) {
            $balance = ($user->balance - $this->getPrice());
            if ($user->balance >= $balance) {
                $user->update(['balance' => $balance]);
                DB::table('activity_transactions')->insert([
                    'user_id' => $user->id,
                    'cashier_id' => Auth::id(),
                    'activity_id' => $this->id,
                    'activity_price' => $this->price,
                    'activity_gst' => SettingsController::getGst(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
                return true;
            }
        }
        return false;
    }
}