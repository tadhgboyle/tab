<?php

namespace App\Models;

use App\Enums\FamilyMemberRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyMember extends Model
{
    // TODO softdeletes for Family timeline

    protected $fillable = [
        'family_id',
        'user_id',
        'role',
    ];

    protected $casts = [
        'role' => FamilyMemberRole::class,
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
