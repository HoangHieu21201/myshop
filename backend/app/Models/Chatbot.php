<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chatbot extends Model
{
    /**
     * Tên bảng trong database.
     * Laravel mặc định sẽ tìm bảng 'chatbots', nếu bảng của bạn tên là 'chatbot' (số ít)
     * thì bỏ comment dòng dưới đây:
     */
    // protected $table = 'chatbot';

    /**
     * 1. Cấu hình Khóa Chính (Primary Key)
     * Mặc định Laravel tìm cột 'id', nên ta phải khai báo lại là 'session_id'
     */
    protected $primaryKey = 'session_id';

    /**
     * Vì session_id là varchar (chuỗi) chứ không phải số nguyên (int),
     * ta phải tắt tính năng tự động tăng (auto-increment).
     */
    public $incrementing = false;

    /**
     * Khai báo kiểu dữ liệu của khóa chính là string.
     */
    protected $keyType = 'string';

    /**
     * 2. Khai báo các cột được phép thêm/sửa (Mass Assignment)
     */
    protected $fillable = [
        'session_id',
        'user_id',
        'last_query',
        'current_context',
        'state',
    ];

    /**
     * 3. Ép kiểu dữ liệu (Casting)
     * Đặc biệt quan trọng với cột 'current_context' kiểu JSON.
     * Khi lấy ra nó sẽ tự thành mảng (array), khi lưu vào nó tự thành JSON.
     */
    protected $casts = [
        'current_context' => 'array',
        'user_id' => 'integer',
        // created_at và updated_at đã được Laravel tự động xử lý
    ];

    /**
     * 4. Giá trị mặc định cho các thuộc tính (Optional)
     * Dù DB đã set default 'IDLE', khai báo ở đây giúp code rõ ràng hơn khi new Model
     */

    protected $attributes = [
        'state' => 'IDLE',
    ];

    /**
     * 5. Relationship (Mối quan hệ)
     * Nếu bảng này liên kết với bảng users qua user_id
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}