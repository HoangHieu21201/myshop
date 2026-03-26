<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'order_code',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'customer_email',
        'order_note',
        'sub_total',
        'discount_amount',
        'shipping_fee',
        'total_amount',
        'coupon_id',
        'coupon_code',
        'payment_method',
        'payment_status',
        'status'
    ];

    protected function casts(): array
    {
        return [
            'sub_total' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    // Quan hệ 1 Hóa đơn có nhiều Chi tiết (Món hàng)
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    // Quan hệ 1 Hóa đơn có nhiều Lịch sử cập nhật
    public function histories()
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id')->orderBy('created_at', 'desc');
    }

    // Khách hàng sở hữu đơn này (Nếu có đăng nhập)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
