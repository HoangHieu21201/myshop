<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;

class ClientHeaderController extends Controller
{
    /**
     * Lấy dữ liệu cho Mega Menu (Danh mục + Sản phẩm nổi bật)
     */
    public function getMegaMenuData()
    {
        try {
            // 1. Lấy danh mục gốc (parent_id = null) và đang active
            // Dựa trên Category.php: lấy id, name, slug (Bỏ cột icon vì DB không có)
            $categories = Category::whereNull('parent_id')
                ->where('status', 'active')
                ->orderBy('sort_order', 'asc') // Sắp xếp theo thứ tự bạn đã config trong Admin
                ->select('id', 'name', 'slug')
                ->get();

            // 2. Gắn sản phẩm nổi bật vào từng danh mục
            $categories->map(function ($category) {
                // Lấy 4 sản phẩm thuộc danh mục này
                // Ưu tiên sản phẩm nổi bật (is_featured = 1) và mới nhất
                $category->top_products = Product::where('category_id', $category->id)
                    ->where('status', 'published') // Trạng thái đang bán
                    ->orderBy('is_featured', 'desc')
                    ->orderBy('id', 'desc')
                    ->take(4)
                    ->select('id', 'name', 'slug', 'thumbnail_image', 'base_price', 'promotional_price')
                    ->get();
                
                return $category;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'categories' => $categories,
                    // Cấu hình thông tin Topbar (Tạm fix cứng, sau này nối DB bảng Settings)
                    'config' => [
                        'phone' => '12345678910',
                        'email' => 'SORA@GMAIL.COM',
                        'facebook' => '#',
                        'instagram' => '#',
                        'twitter' => '#'
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Lỗi lấy dữ liệu Header: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xử lý Smart Search trên Header
     */
    public function search(Request $request)
    {
        $keyword = $request->query('keyword');

        if (!$keyword) {
            return response()->json(['success' => true, 'data' => ['products' => [], 'categories' => []]]);
        }

        // 1. Tìm Danh mục
        $categories = Category::where('name', 'LIKE', "%{$keyword}%")
            ->where('status', 'active')
            ->select('id', 'name', 'slug')
            ->take(3)
            ->get();

        // 2. Tìm Sản phẩm
        $productsQuery = Product::where('name', 'LIKE', "%{$keyword}%")
            ->where('status', 'published');

        // Logic Category Fallback
        if ($productsQuery->count() === 0 && $categories->count() > 0) {
            $products = Product::where('category_id', $categories->first()->id)
                ->where('status', 'published')
                ->take(5)
                ->select('id', 'name', 'slug', 'thumbnail_image', 'base_price', 'promotional_price')
                ->get();
            $isFallback = true;
        } else {
            $products = $productsQuery->take(5)
                ->select('id', 'name', 'slug', 'thumbnail_image', 'base_price', 'promotional_price')
                ->get();
            $isFallback = false;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'products' => $products,
                'is_category_fallback' => $isFallback
            ]
        ]);
    }
}