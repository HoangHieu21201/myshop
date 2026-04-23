<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chạy migration.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Thêm cột tier_discount_amount để lưu số tiền được giảm nhờ đặc quyền hạng
            // Định dạng decimal (15, 2) cho phù hợp với tiền tệ, đặt giá trị mặc định là 0
            // Hàm after() giúp đặt cột này ngay sau cột discount_amount để dễ nhìn trong Database
            $table->decimal('tier_discount_amount', 15, 2)->default(0)->after('discount_amount');
        });
    }

    /**
     * Revert migration.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Xoá cột khi rollback
            $table->dropColumn('tier_discount_amount');
        });
    }
};