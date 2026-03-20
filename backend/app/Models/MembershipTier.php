<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_spent',
        'min_orders',
        'discount_percent',
        'yearly_service_quota',
        'icon',
    ];

    protected $casts = [
        'min_spent' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'min_orders' => 'integer',
        'yearly_service_quota' => 'integer',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'tier_id');
    }
}