<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable(); // Mô tả ngắn
            $table->longText('content'); // Nội dung bài viết
            $table->string('image_url')->nullable(); // Ảnh đại diện
            $table->string('author_name')->nullable(); // Tên tác giả
            $table->string('category')->nullable(); // Danh mục
            $table->enum('status', ['pending', 'published', 'draft'])->default('pending'); // Trạng thái
            $table->integer('views')->default(0); // Lượt xem (cho bài viết phổ biến)
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};