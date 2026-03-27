<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favourite extends Model
{
    use HasFactory;

    // Chỉ định rõ tên bảng trong CSDL
    protected $table = 'favourites';

    // Các trường được phép thêm/sửa dữ liệu hàng loạt
    protected $fillable = [
        'user_id',
        'product_id',
    ];

    /**
     * Mối quan hệ: Lượt yêu thích này của User nào
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Mối quan hệ: Lượt yêu thích này thuộc về Product nào
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}