<?php

namespace App\Models;

use App\Enums\FamilyMembershipRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyMembership extends Model
{
    protected $fillable = [
        'family_id',
        'user_id',
        'role',
    ];

    protected $casts = [
        'role' => FamilyMembershipRole::class,
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
