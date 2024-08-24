<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftCardAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'assigner_id',
    ];

    public function giftCard()
    {
        return $this->belongsTo(GiftCard::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigner_id');
    }
}
