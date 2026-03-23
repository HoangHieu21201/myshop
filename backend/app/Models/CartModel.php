<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    // Chỉ định rõ tên bảng (không bắt buộc nhưng nên có cho rõ ràng)
    protected $table = 'carts';

    // Các trường cho phép mass-assignment
    protected $fillable = [
        'user_id',
        'session_id',
    ];

    /**
     * Quan hệ với bảng Users (Giỏ hàng này của ai)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quan hệ với bảng CartItems (Giỏ hàng có những món nào)
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }
}