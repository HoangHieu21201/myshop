<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ClientHomeController extends Controller
{
    public function index()
    {
        try {
            // =========================================================================
            // BỌC REDIS: Tự động lưu toàn bộ dữ liệu trang chủ vào RAM trong 3600 giây (1 tiếng)
            // =========================================================================
            $data = Cache::remember('client_home_data', 3600, function () {

                $cacheData = [
                    'banners' => [],
                    'coupons' => [],
                    'categories' => [],
                    'products' => [],
                    'combos' => [],
                    'tiers' => []
                ];

                // 1. Lấy Banners
                if (Schema::hasTable('banners')) {
                    $cacheData['banners'] = DB::table('banners')->where('status', 'active')->orderBy('sort_order', 'asc')->get();
                }

                // 2. Lấy Coupons
                if (Schema::hasTable('coupons')) {
                    $cacheData['coupons'] = DB::table('coupons')
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
                    if (Schema::hasColumn('categories', 'sort_order')) {
                        $catQuery->orderBy('sort_order', 'asc');
                    } else {
                        $catQuery->orderBy('id', 'asc');
                    }
                    $cacheData['categories'] = $catQuery->take(6)->get();
                }

                // 4. Lấy Sản phẩm
                if (Schema::hasTable('products')) {
                    $cacheData['products'] = Product::where('status', 'published')
                        ->select('id', 'name', 'slug', 'thumbnail_image', 'base_price', 'promotional_price', 'created_at')
                        ->orderBy('is_featured', 'desc')
                        ->orderBy('id', 'desc')
                        ->take(8)
                        ->get()
                        ->map(function ($product) {
                            $product->is_new = $product->created_at >= Carbon::now()->subDays(15);
                            return $product;
                        });
                }

                // 5. Lấy Combos & Tự động tính giá
                if (Schema::hasTable('combos') && Schema::hasTable('combo_items')) {
                    $cacheData['combos'] = DB::table('combos')
                        ->where('status', 'active')
                        ->orderBy('id', 'desc')
                        ->take(5)
                        ->get()
                        ->map(function ($combo) {
                            $items = DB::table('combo_items')
                                ->join('products', 'combo_items.product_id', '=', 'products.id')
                                ->where('combo_items.combo_id', $combo->id)
                                ->select('products.id', 'products.name', 'products.thumbnail_image', 'products.base_price', 'products.promotional_price', 'combo_items.quantity')
                                ->get();

                            $totalBasePrice = 0;
                            $productsArray = [];

                            foreach ($items as $item) {
                                $priceToUse = $item->promotional_price > 0 ? $item->promotional_price : $item->base_price;
                                $totalBasePrice += ($priceToUse * $item->quantity);

                                $productsArray[] = [
                                    'id' => $item->id,
                                    'name' => $item->name,
                                    'thumbnail_image' => $item->thumbnail_image
                                ];
                            }

                            $comboPromoPrice = $totalBasePrice;
                            if ($combo->discount_value > 0) {
                                if ($combo->discount_type === 'percentage' || $combo->discount_type === 'percent') {
                                    $comboPromoPrice = $totalBasePrice - ($totalBasePrice * ($combo->discount_value / 100));
                                } else {
                                    $comboPromoPrice = $totalBasePrice - $combo->discount_value;
                                }
                            }

                            return [
                                'id' => $combo->id,
                                'slug' => $combo->slug,
                                'name' => $combo->name,
                                'description' => $combo->description,
                                'thumbnail_image' => $combo->thumbnail_image,
                                'base_price' => $totalBasePrice,
                                'promotional_price' => $comboPromoPrice > 0 ? $comboPromoPrice : 0,
                                'products' => $productsArray
                            ];
                        });
                }

                // 6. Lấy Hạng hội viên
                if (Schema::hasTable('membership_tiers')) {
                    $cacheData['tiers'] = DB::table('membership_tiers')
                        ->where('min_spent', '>', 0)
                        ->orderBy('min_spent', 'asc')
                        ->take(3)
                        ->get();
                }

                return $cacheData;
            }); // KẾT THÚC HÀM CACHE::REMEMBER

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cảnh báo Backend: ' . $e->getMessage()
            ], 500);
        }
    }

    public function goldPrices()
    {
        try {
            $goldPrices = Cache::get('sora_gold_prices', []);
            $lastUpdated = Cache::get('sora_gold_last_updated', '');

            return response()->json([
                'success' => true,
                'data' => [
                    'prices' => $goldPrices,
                    'last_updated' => $lastUpdated
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi tải giá vàng: ' . $e->getMessage()
            ], 500);
        }
    }
}
