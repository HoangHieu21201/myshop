<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// LƯU Ý QUAN TRỌNG: 
// Bạn phải đổi tên file từ "NewModel.php" thành "News.php" (chữ N hoa)
// Nếu không Laravel sẽ không tìm thấy class này và văng lỗi 500.

class News extends Model
{
    use HasFactory;

    protected $table = 'news';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'image_url',
        'author_name',
        'category',
        'status',
        'views',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];
}