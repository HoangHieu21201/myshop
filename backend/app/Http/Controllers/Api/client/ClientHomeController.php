<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ClientHomeController extends Controller
{
    /**
     * API gom toàn bộ dữ liệu cho Trang chủ
     */
    public function index()
    {
        // Khởi tạo mảng rỗng mặc định để Frontend không bị lỗi undefined
        $data = [
            'banners' => [],
            'coupons' => [],
            'categories' => [],
            'products' => [],
            'tiers' => []
        ];

        try {
            // 1. Lấy Banners (Chỉ truy vấn nếu bảng banners đã được tạo)
            if (Schema::hasTable('banners')) {
                $data['banners'] = DB::table('banners')->where('status', 'active')->orderBy('sort_order', 'asc')->get();
            }

            // 2. Lấy Coupons (Chỉ truy vấn nếu bảng coupons đã được tạo)
            if (Schema::hasTable('coupons')) {
                $data['coupons'] = DB::table('coupons')
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>=', now());
                    })
                    ->get()
                    ->map(function ($coupon) {
                        return [
                            'id' => $coupon->id,
                            'code' => $coupon->code,
                            'discount_type' => $coupon->type == 'percentage' ? 'percent' : 'fixed',
                            'discount_value' => $coupon->value,
                            'min_order_value' => $coupon->min_spend ?? 0,
                        ];
                    });
            }

            // 3. Lấy Danh mục
            if (Schema::hasTable('categories')) {
                $catQuery = Category::where('status', 'active')->select('id', 'name', 'slug', 'thumbnail as image');

                // Kiểm tra xem đã migrate cột sort_order chưa để tránh lỗi
                if (Schema::hasColumn('categories', 'sort_order')) {
                    $catQuery->orderBy('sort_order', 'asc');
                } else {
                    $catQuery->orderBy('id', 'asc');
                }

                $data['categories'] = $catQuery->take(6)->get();
            }

            // 4. Lấy Sản phẩm & Tự động tính "Sản phẩm mới"
            if (Schema::hasTable('products')) {
                $data['products'] = Product::where('status', 'published')
                    ->select('id', 'name', 'slug', 'thumbnail_image', 'base_price', 'promotional_price', 'created_at')
                    ->orderBy('is_featured', 'desc') // Ưu tiên hàng nổi bật
                    ->orderBy('id', 'desc')
                    ->take(8)
                    ->get()
                    ->map(function ($product) {
                        $product->is_new = $product->created_at >= Carbon::now()->subDays(15);
                        return $product;
                    });
            }

            // 5. Lấy Hạng hội viên (Chỉ truy vấn nếu bảng đã tạo)
            if (Schema::hasTable('membership_tiers')) {
                $data['tiers'] = DB::table('membership_tiers')
                    ->where('min_spent', '>', 0)
                    ->orderBy('min_spent', 'asc')
                    ->take(3)
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            // NẾU CÓ LỖI (Ví dụ sai tên cột), VẪN TRẢ VỀ 200 KÈM DATA RỖNG ĐỂ MÀN HÌNH FRONTEND KHÔNG BỊ SẬP
            return response()->json([
                'success' => false,
                'message' => 'Cảnh báo Backend: ' . $e->getMessage(),
                'data' => $data
            ], 200);
        }
    }
}
