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
        // FIX: Bổ sung "variants.attributeValues.attribute" để lấy MỌI THUỘC TÍNH (Size, Màu...) cho Modal
        $query = Product::with([
            'category:id,name,slug', 
            'variants.attributeValues.attribute'
        ])->where('status', 'published'); 

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

        // 3. Sắp xếp (Đã sửa lại logic sắp xếp chuẩn xác)
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'new': // Thêm case xử lý sản phẩm "Mới nhất"
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'price_asc':
                    // Sắp xếp theo giá bán thực tế (có KM thì lấy KM, không thì lấy giá gốc)
                    $query->orderByRaw('COALESCE(promotional_price, base_price) ASC');
                    break;
                case 'price_desc':
                    $query->orderByRaw('COALESCE(promotional_price, base_price) DESC');
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