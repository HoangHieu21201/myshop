<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $table = 'cart_items';

    protected $fillable = [
        'cart_id',
        'product_variant_id',
        'quantity',
    ];

    // Giải quyết N+1 Query: Tự động load variant khi gọi CartItem
    protected $with = ['variant'];

    /**
     * Món hàng này thuộc về giỏ hàng nào
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    /**
     * Chi tiết biến thể sản phẩm của món hàng này
     */
    public function variant(): BelongsTo
    {
        // Liên kết tới bảng product_variants
        // Sparring Partner: Dùng withTrashed() và withDefault() để tránh sập web khi biến thể bị xóa
        return $this->belongsTo(ProductVariant::class, 'product_variant_id')
                    ->withTrashed()
                    ->withDefault([
                        'sku' => 'Sản phẩm đã ngừng kinh doanh',
                        'price' => 0,
                        'image_url' => null
                    ]);
    }

    /**
     * Accessor: Tự động tính tổng tiền của riêng món này (quantity * price)
     * Sử dụng trên Vue 3 bằng cách gọi item.subtotal
     */
    public function getSubtotalAttribute()
    {
        return $this->quantity * ($this->variant->price ?? 0);
    }
}