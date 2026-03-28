<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerGallery extends Model
{
    use HasFactory;

    // Khai báo tên bảng (Laravel sẽ tự hiểu là customer_galleries nhưng cứ khai báo cho chắc cú)
    protected $table = 'customer_galleries';

    protected $fillable = [
        'title',
        'image_path',
        'sort_order',
        'is_active',
    ];
}