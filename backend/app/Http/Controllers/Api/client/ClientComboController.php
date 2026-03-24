<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Combo;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClientComboController extends Controller
{
    /**
     * 1. LẤY DANH SÁCH COMBO CHO TRANG CHỦ / TRANG INDEX
     * Đã dỡ bỏ các rào cản thời gian/số lượng để hiển thị mượt mà.
     */
    public function index(Request $request)
    {
        $query = Combo::with([
            'items.product', 
            'items.variant'
        ])
        ->where('status', 'active'); // Chỉ cần Active là được lên sóng

        // Chỉ filter giới tính nếu có truyền lên và khác 'all'
        if ($request->has('gender') && $request->gender !== 'all' && $request->gender !== '') {
            $query->where('target_gender', $request->gender);
        }

        // Lấy dữ liệu và phân trang (Paginate)
        $combos = $query->orderBy('id', 'desc')->paginate(12);

        return response()->json([
            'success' => true, 
            'data' => $combos
        ]);
    }

    /**
     * 2. LẤY CHI TIẾT 1 COMBO KHI KHÁCH CLICK VÀO
     */
    public function show($slug)
    {
        $now = Carbon::now();

        $combo = Combo::with([
            'items.product' => function($q) {
                $q->with(['variants' => function($vq) {
                    $vq->where('stock_quantity', '>', 0)
                       ->select('id', 'product_id', 'sku', 'price', 'promotional_price', 'stock_quantity', 'image_url');
                }]);
            },
            'items.variant' 
        ])
        ->where('slug', $slug)
        ->where('status', 'active')
        ->firstOrFail();

        // ==========================================
        // XỬ LÝ LOGIC "CAN_BUY" TẠI TRANG CHI TIẾT
        // ==========================================
        $isExpired = ($combo->end_date && $now->greaterThan($combo->end_date));
        $isNotStarted = ($combo->start_date && $now->lessThan($combo->start_date));
        
        // Đảm bảo chỉ chặn khi usage_limit thực sự là số và <= 0 (Loại trừ trường hợp null/chuỗi rỗng)
        $isSoldOut = (is_numeric($combo->usage_limit) && $combo->usage_limit <= 0);

        // Trả cờ can_buy về cho Vue.js xử lý khóa nút Mua Hàng nếu cần
        $combo->can_buy = !($isExpired || $isNotStarted || $isSoldOut);

        return response()->json([
            'success' => true, 
            'data' => $combo
        ]);
    }
}