<?php

namespace App\Models;

use App\Helpers\TaxHelper;
use App\Helpers\SettingsHelper;
use App\Helpers\UserLimitsHelper;
use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
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

    public function getPriceAfterTax(): Money
    {
        return TaxHelper::calculateFor($this->price, 1, true);
    }

    public function getAttendees(): Collection
    {
        return User::findMany($this->getCurrentAttendees());
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

        if ($this->getPriceAfterTax() > $user->balance) {
            return redirect()->back()->with('error', "Could not register $user->full_name for $this->name, they do not have enough balance.");
        }

        if (!UserLimitsHelper::canSpend($user, $this->getPriceAfterTax(), $this->category_id)) {
            return redirect()->back()->with('error', "Could not register $user->full_name for $this->name, they have reached their limit for the {$this->category->name} category.");
        }

        DB::table('activity_transactions')->insert([
            'user_id' => $user->id,
            'cashier_id' => auth()->id(),
            'activity_id' => $this->id,
            'activity_price' => $this->price->getAmount(),
            'category_id' => $this->category_id,
            'activity_gst' => resolve(SettingsHelper::class)->getGst(),
            'activity_pst' => resolve(SettingsHelper::class)->getPst(),
            'total_price' => $this->getPriceAfterTax()->getAmount(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user->balance = $user->balance->subtract($this->getPriceAfterTax());

        return redirect()->back()->with('success', "Successfully registered $user->full_name to $this->name.");
    }
}
