<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * 1. LẤY DANH SÁCH TẤT CẢ SẢN PHẨM (Có Lọc Nhiều Điều Kiện)
     */
    public function index(Request $request, $shop_slug)
    {
        $query = Product::with(['category:id,name,slug', 'brand:id,name,slug'])
            ->withSum('variants as total_stock', 'stock_quantity')
            ->where('status', 'published'); 

        // 1. Lọc theo Keyword
        if ($request->has('keyword') && $request->keyword != '') {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        // 2. Lọc mảng Category (LOẠI)
        if ($request->has('categories') && $request->categories != '') {
            $categoriesArr = explode(',', $request->categories);
            $query->whereHas('category', function($q) use ($categoriesArr) {
                $q->whereIn('slug', $categoriesArr);
            });
        }

        // 3. Lọc mảng Bộ sưu tập (Collections) / Kim loại / Hình dáng đá...
        // Giả sử bảng products của bạn có cột collection_slug, metal_type (Hoặc phải map qua bảng Attributes)
        // Dưới đây là ví dụ lọc qua cột trực tiếp (bạn có thể tuỳ biến theo DB thực tế)
        
        if ($request->has('collections') && $request->collections != '') {
            $collectionsArr = explode(',', $request->collections);
            // VD: $query->whereIn('collection_slug', $collectionsArr);
        }

        if ($request->has('metals') && $request->metals != '') {
            $metalsArr = explode(',', $request->metals);
            // VD: $query->whereIn('metal_type', $metalsArr);
        }

        // 4. Lọc Availability (Mới, Online...)
        if ($request->has('availability') && $request->availability != '') {
            $availArr = explode(',', $request->availability);
            if (in_array('new', $availArr)) {
                // Ví dụ sản phẩm được tạo trong 30 ngày qua
                $query->where('created_at', '>=', now()->subDays(30)); 
            }
        }

        // 5. Sắp xếp
        $sort = $request->input('sort', 'recommended');
        switch ($sort) {
            case 'price_asc':
                // Sắp xếp theo giá khuyến mãi, nếu không có thì lấy giá gốc
                $query->orderByRaw('COALESCE(promotional_price, base_price) ASC');
                break;
            case 'price_desc':
                $query->orderByRaw('COALESCE(promotional_price, base_price) DESC');
                break;
            case 'recommended':
                $query->orderBy('is_featured', 'desc')->orderBy('id', 'desc');
                break;
            default:
                $query->orderBy('id', 'desc');
                break;
        }

        $products = $query->paginate($request->input('per_page', 12));
        
        // Thêm cờ is_new cho Frontend dựa trên ngày tạo
        $products->getCollection()->transform(function ($product) {
            $product->is_new = $product->created_at >= now()->subDays(30);
            return $product;
        });

        return response()->json(['success' => true, 'data' => $products]);
    }

    // Các hàm shopInfo, featured, categories, show giữ nguyên như cũ...
    public function shopInfo($shop_slug) {
        $shop = ['id' => 1, 'name' => 'SORA', 'slug' => $shop_slug, 'logo' => null, 'status' => 'active'];
        return response()->json(['success' => true, 'data' => $shop]);
    }

    public function categories($shop_slug) {
        $categories = \App\Models\Category::all(); 
        return response()->json(['success' => true, 'data' => $categories]);
    }
}