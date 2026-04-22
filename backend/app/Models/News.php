<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Import SoftDeletes

class News extends Model
{
    use HasFactory, SoftDeletes; // 2. Thêm SoftDeletes vào đây

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