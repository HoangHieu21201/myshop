<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TierServiceUsage extends Model
{
    use HasFactory;

    public $timestamps = false; 

    protected $fillable = [
        'user_id',
        'order_id',
        'service_type',
        'product_code',
        'used_at',
        'notes',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}