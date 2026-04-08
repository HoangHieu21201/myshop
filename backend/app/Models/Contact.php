<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    // Chỉ định tên bảng trong cơ sở dữ liệu (tùy chọn nhưng nên có cho chắc chắn)
    protected $table = 'contacts';

    // Các trường được phép thêm/sửa dữ liệu hàng loạt (Mass Assignment)
    protected $fillable = [
        'fullname',
        'phone',
        'email',
        'message',
        'status',
    ];
}