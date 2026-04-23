<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('message_type')->default('text')->after('content'); // text | file | image
            $table->string('file_url')->nullable()->after('message_type');    // URL file/image
            $table->string('file_name')->nullable()->after('file_url');       // Tên file gốc
            $table->string('file_size')->nullable()->after('file_name');      // Kích thước file
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['message_type', 'file_url', 'file_name', 'file_size']);
        });
    }
};
