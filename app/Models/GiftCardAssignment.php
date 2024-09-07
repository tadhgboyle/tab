<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GiftCardAssignment extends Model
{
    use SoftDeletes;

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

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
