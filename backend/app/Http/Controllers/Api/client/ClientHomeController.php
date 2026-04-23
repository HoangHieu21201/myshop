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
            // KHÔNG DÙNG CACHE NỮA - LẤY TRỰC TIẾP TỪ DATABASE ĐỂ TRÁNH LỖI KẸT DỮ LIỆU
            $data = [
                'banners' => [],
                'coupons' => [],
                'categories' => [],
                'products' => [],
                'combos' => [],
                'tiers' => [],
                'galleries' => [],
                'news' => [] // ĐÃ THÊM MẢNG NEWS
            ];

            // 1. Lấy Banners
            if (Schema::hasTable('banners')) {
                $query = DB::table('banners')->where('status', 'active');
                if (Schema::hasColumn('banners', 'deleted_at')) {
                    $query->whereNull('deleted_at');
                }
                $data['banners'] = $query->orderBy('sort_order', 'asc')->get();
            }

            // 2. Lấy Coupons
            if (Schema::hasTable('coupons')) {
                $query = DB::table('coupons')
                    ->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>=', now());
                    });
                if (Schema::hasColumn('coupons', 'deleted_at')) {
                    $query->whereNull('deleted_at');
                }
                
                $data['coupons'] = $query->get()->map(function ($coupon) {
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
                $data['categories'] = $catQuery->take(6)->get();
            }

            // 4. Lấy Sản phẩm
            if (Schema::hasTable('products')) {
                $data['products'] = Product::where('status', 'published')
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

            // 5. Lấy Combos
            if (Schema::hasTable('combos') && Schema::hasTable('combo_items')) {
                $query = DB::table('combos')->where('status', 'active');

                if (Schema::hasColumn('combos', 'deleted_at')) {
                    $query->whereNull('deleted_at');
                }

                $yesterday = Carbon::now()->subDay();
                $query->where(function($q) use ($yesterday) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', $yesterday);
                });

                $data['combos'] = $query->orderBy('id', 'desc')
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
                $query = DB::table('membership_tiers')->where('min_spent', '>', 0);
                if (Schema::hasColumn('membership_tiers', 'deleted_at')) {
                    $query->whereNull('deleted_at');
                }
                $data['tiers'] = $query->orderBy('min_spent', 'asc')->take(3)->get();
            }

            // 7. LẤY CHÂN DUNG SORA
            try {
                $data['galleries'] = \App\Models\CustomerGallery::where('is_active', 1)
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('created_at', 'desc')
                    ->get(['image_path']);
            } catch (\Exception $e) {
                $data['galleries'] = []; 
            }

            // 8. Lấy tin tức
            if (Schema::hasTable('news')) {
                try {
                    $data['news'] = DB::table('news')
                        ->where('status', 'published')
                        ->orderBy('created_at', 'desc')
                        ->take(3)
                        ->get(['id', 'title', 'slug', 'excerpt', 'content', 'image_url', 'category']);
                } catch (\Exception $e) {
                    $data['news'] = [];
                }
            }

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