<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TierHistory extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'old_tier_id',
        'new_tier_id',
        'reason',
        'created_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function oldTier()
    {
        return $this->belongsTo(MembershipTier::class, 'old_tier_id');
    }

    public function newTier()
    {
        return $this->belongsTo(MembershipTier::class, 'new_tier_id');
    }
}