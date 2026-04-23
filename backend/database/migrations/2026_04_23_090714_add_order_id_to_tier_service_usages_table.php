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
        Schema::table('tier_service_usages', function (Blueprint $table) {
            // Thêm trường order_id để biết lượt dùng này thuộc đơn hàng nào
            // Nullable vì các dịch vụ khác (như vệ sinh, đánh bóng) có thể không cần gắn với đơn hàng
            $table->unsignedBigInteger('order_id')->nullable()->after('user_id');

            // Tạo khoá ngoại (Tuỳ chọn: Nếu cậu muốn dữ liệu chặt chẽ thì mở comment đoạn dưới ra)
            // $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });
    }

    /**
     * Revert migration.
     */
    public function down(): void
    {
        Schema::table('tier_service_usages', function (Blueprint $table) {
            // Nếu có dùng khoá ngoại thì phải xoá khoá ngoại trước khi xoá cột
            // $table->dropForeign(['order_id']); 
            
            // Xoá cột khi rollback
            $table->dropColumn('order_id');
        });
    }
};