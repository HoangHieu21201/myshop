<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chạy migration để thêm cột.
     */
    public function up(): void
    {
        Schema::table('membership_tiers', function (Blueprint $table) {
            // Thêm cột lưu giới hạn số lượt dùng phần trăm giảm giá trong năm
            // Đặt mặc định là 0 (hoặc cậu có thể để nullable() nếu không muốn set mặc định)
            // after('discount_percent') để nó nằm ngay sau cột discount_percent cho dễ nhìn trong DB
            $table->integer('yearly_discount_quota')->default(0)->after('discount_percent');
        });
    }

    /**
     * Hoàn tác migration (xóa cột).
     */
    public function down(): void
    {
        Schema::table('membership_tiers', function (Blueprint $table) {
            $table->dropColumn('yearly_discount_quota');
        });
    }
};