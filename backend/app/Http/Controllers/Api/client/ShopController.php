<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Attribute; 
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * API: Lấy danh sách sản phẩm trang Shop
     */
    public function index(Request $request, $shop_slug)
    {
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

        // 3. Lọc theo Màu sắc (từ URL Params: ?color=red,blue)
        if ($request->has('color') && $request->color != '') {
            $colorsArr = explode(',', $request->color);
            $query->whereHas('variants.attributeValues', function($q) use ($colorsArr) {
                $q->whereIn('value', $colorsArr)
                  ->whereHas('attribute', function($attrQ) {
                      $attrQ->whereIn('name', ['Màu sắc', 'Color', 'Màu', 'color']);
                  });
            });
        }

        // 4. Lọc theo Thuộc tính Biến thể khác (Kích thước, Chất liệu...)
        if ($request->has('attribute_values') && $request->attribute_values != '') {
            $attrValues = explode(',', $request->attribute_values);
            $query->whereHas('variants.attributeValues', function($q) use ($attrValues) {
                $q->whereIn('value', $attrValues);
            });
        }

        // 5. Sắp xếp 
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'new': 
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'price_asc':
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
            
            $product->hover_image = null;
            if ($product->variants && $product->variants->count() > 0) {
                $hoverCandidate = $product->variants->first(function($v) use ($product) {
                    return !empty($v->image_url) && $v->image_url !== $product->thumbnail_image;
                });
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

    /**
     * API: Lấy danh sách màu sắc duy nhất từ các biến thể
     */
    public function colors($shop_slug) {
        // Truy vấn các biến thể của sản phẩm đang được publish
        $variants = ProductVariant::whereHas('product', function($q) {
            $q->where('status', 'published');
        })->with(['attributeValues' => function($q) {
            // Chỉ lấy các giá trị thuộc tính là màu sắc
            $q->whereHas('attribute', function($q2) {
                $q2->whereIn('name', ['Màu sắc', 'Color', 'Màu', 'color']);
            });
        }])->get();

        $colors = collect();
        
        // Lấy tất cả giá trị màu
        foreach ($variants as $variant) {
            foreach ($variant->attributeValues as $attrValue) {
                $colors->push($attrValue->value);
            }
        }

        // Loại bỏ trùng lặp và reset keys
        $uniqueColors = $colors->unique()->values();

        return response()->json(['success' => true, 'data' => $uniqueColors]);
    }

    /**
     * API: Lấy danh sách toàn bộ Thuộc tính (Attributes) & Giá trị để làm bộ lọc
     */
    public function attributes($shop_slug) {
        // Lấy các attributes có data values
        $attributes = Attribute::with(['values' => function($q) {
            // Chỉ lấy các giá trị duy nhất
            $q->select('id', 'attribute_id', 'value')->distinct('value');
        }])->has('values')->get();

        return response()->json(['success' => true, 'data' => $attributes]);
    }
}