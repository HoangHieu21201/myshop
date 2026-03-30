<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            
            // Các cột liên kết Sản phẩm / Combo (Mặc định chuẩn mới của Laravel - UNSIGNED)
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('combo_id')->nullable();
            
            // --- BẮT ĐẦU VÁ LỖI INCOMPATIBLE FOREIGN KEY ---
            // Bảng users của bạn đang dùng BIGINT có dấu
            $table->bigInteger('user_id')->comment('BIGINT có dấu (Khớp với bảng users)'); 
            
            // Bảng orders của bạn dùng UNSIGNED BIGINT (Chuẩn mới)
            $table->unsignedBigInteger('order_id')->comment('UNSIGNED BIGINT (Khớp với bảng orders)');
            // --- KẾT THÚC VÁ LỖI ---
            
            // Nội dung đánh giá
            $table->tinyInteger('rating')->comment('Số sao từ 1 đến 5');
            $table->text('comment')->nullable()->comment('Nội dung đánh giá');
            $table->json('images')->nullable()->comment('Mảng chứa link ảnh khách up');
            
            // Quản lý hiển thị
            $table->enum('status', ['pending', 'approved', 'hidden'])->default('approved');
            
            // Admin phản hồi
            $table->text('admin_reply')->nullable()->comment('Phản hồi từ shop');
            
            $table->timestamps();

            // Thiết lập Khóa ngoại (Foreign Keys)
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('combo_id')->references('id')->on('combos')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');

            // Ràng buộc Unique: 1 user chỉ review 1 sản phẩm 1 lần trên 1 đơn hàng
            $table->unique(['user_id', 'product_id', 'order_id'], 'rev_prod_unq');
            $table->unique(['user_id', 'combo_id', 'order_id'], 'rev_combo_unq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
