<?php

namespace App\Http\Controllers\Api\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Coupon;
use App\Models\Category;
use App\Models\Product;
use App\Models\MembershipTier;
use Carbon\Carbon;

class ClientHomeController extends Controller
{
    /**
     * API gom toàn bộ dữ liệu cho Trang chủ
     */
    public function index()
    {
        try {
            // 1. Lấy Banners
            $banners = Banner::where('status', 'active')->orderBy('sort_order', 'asc')->get();

            // 2. Lấy Coupons (Map lại tên cột cho khớp Frontend)
            $coupons = Coupon::where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>=', now());
                })
                ->get()
                ->map(function ($coupon) {
                    return [
                        'id' => $coupon->id,
                        'code' => $coupon->code,
                        // Nếu DB là 'percentage' thì Frontend sẽ nhận 'percent'
                        'discount_type' => $coupon->type == 'percentage' ? 'percent' : 'fixed', 
                        'discount_value' => $coupon->value,
                        'min_order_value' => $coupon->min_spend, // Map min_spend thành min_order_value
                    ];
                });

            // 3. Lấy Danh mục (Đổi tên cột thumbnail thành image ngay trong câu SQL)
            $categories = Category::where('status', 'active')
                ->select('id', 'name', 'slug', 'thumbnail as image') 
                ->orderBy('sort_order', 'asc')
                ->take(6)
                ->get();

            // 4. Lấy Sản phẩm & Tự động tính "Sản phẩm mới"
            $products = Product::where('status', 'published')
                ->select('id', 'name', 'slug', 'thumbnail_image', 'base_price', 'promotional_price', 'created_at')
                ->orderBy('id', 'desc')
                ->take(8)
                ->get()
                ->map(function ($product) {
                    // Nếu ngày tạo (created_at) nằm trong vòng 15 ngày qua -> Bật badge MỚI
                    $product->is_new = $product->created_at >= Carbon::now()->subDays(15);
                    return $product;
                });

            // 5. Lấy Hạng hội viên (Bỏ qua hạng Bạc 0đ nếu có)
            $tiers = MembershipTier::where('min_spent', '>', 0)
                ->orderBy('min_spent', 'asc')
                ->take(3)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'banners' => $banners,
                    'coupons' => $coupons,
                    'categories' => $categories,
                    'products' => $products,
                    'tiers' => $tiers
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi tải dữ liệu trang chủ: ' . $e->getMessage()
            ], 500);
        }
    }
}