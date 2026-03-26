<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * API: Lấy danh sách sản phẩm trang Shop
     */
    public function index(Request $request, $shop_slug)
    {
        // FIX: Lấy TOÀN BỘ thông tin variants để phục vụ việc chọn Size/Màu, check tồn kho ở Frontend
        $query = Product::with(['category:id,name,slug', 'variants'])
            ->where('status', 'published'); 

        // 1. Lọc theo Danh mục (nếu có chọn) - Lọc bằng slug
        if ($request->has('categories') && $request->categories != '') {
            $categoriesArr = explode(',', $request->categories);
            $query->whereHas('category', function($q) use ($categoriesArr) {
                $q->whereIn('slug', $categoriesArr);
            });
        }
        
        // 2. Lọc theo từ khóa tìm kiếm
        if ($request->has('keyword') && $request->keyword != '') {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        // Sắp xếp
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderBy('base_price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('base_price', 'desc');
                    break;
                case 'recommended':
                default:
                    $query->orderBy('is_featured', 'desc')->orderBy('id', 'desc');
                    break;
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        $products = $query->paginate($request->input('per_page', 12));
        
        // TRANSFORM DATA: Xử lý logic ảnh hover cho mỗi sản phẩm
        $products->getCollection()->transform(function ($product) {
            $product->is_new = $product->created_at >= now()->subDays(30);
            
            // LOGIC LẤY ẢNH HOVER TỪ DATABASE: 
            $product->hover_image = null;
            if ($product->variants && $product->variants->count() > 0) {
                $hoverCandidate = $product->variants->first(function($v) use ($product) {
                    return !empty($v->image_url) && $v->image_url !== $product->thumbnail_image;
                });
                
                // Trả về ảnh hover hợp lệ, nếu không có để null (Frontend sẽ tự fallback)
                $product->hover_image = $hoverCandidate ? $hoverCandidate->image_url : null;
            }
            
            return $product;
        });

        return response()->json(['success' => true, 'data' => $products]);
    }

    /**
     * API: Lấy danh sách danh mục thực tế để hiển thị
     */
    public function categories($shop_slug) {
        $categories = Category::where('status', 'active')
            ->orderBy('sort_order', 'asc')
            ->get(['id', 'name', 'slug', 'thumbnail']); 
            
        return response()->json(['success' => true, 'data' => $categories]);
    }
}