<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Combo;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClientComboController extends Controller
{
    public function index(Request $request)
    {
        $query = Combo::with(['items.product', 'items.variant'])
        ->where('status', 'active');

        if ($request->has('gender') && $request->gender !== 'all' && $request->gender !== '') {
            $query->where('target_gender', $request->gender);
        }

        $combos = $query->orderBy('id', 'desc')->paginate(12);

        return response()->json([
            'success' => true, 
            'data' => $combos
        ]);
    }

    public function show($slug)
    {
        $now = Carbon::now();

        $combo = Combo::with([
            'items.product' => function($q) {
                // FIX BLIND SPOT: Bổ sung Category và Brand để Frontend có thông tin hiển thị sang trọng
                $q->with([
                    'category:id,name',
                    'brand:id,name',
                    'variants' => function($vq) {
                        $vq->where('stock_quantity', '>', 0)
                           ->with(['attributeValues.attribute']);
                    }
                ]);
            },
            'items.variant' => function($vq) {
                // FIX BLIND SPOT: Load kèm Sản phẩm cha để lấy Hình ảnh/Tên gốc nếu biến thể không có
                $vq->with(['attributeValues.attribute', 'product.category', 'product.brand']);
            }
        ])
        ->where('slug', $slug)
        ->where('status', 'active')
        ->firstOrFail();

        // THUẬT TOÁN: Gom nhóm Thuộc tính thành Mảng và Bơm thêm Data Giao diện (Luxury Context)
        foreach ($combo->items as $item) {
            
            // Trường hợp 1: Khách hàng được quyền chọn biến thể
            if ($item->product && $item->product->variants) {
                foreach ($item->product->variants as $variant) {
                    $attrMap = [];
                    if ($variant->attributeValues) {
                        foreach ($variant->attributeValues as $val) {
                            if ($val->attribute) {
                                $attrMap[$val->attribute->name] = $val->value;
                            }
                        }
                    }
                    if (empty($attrMap)) {
                        $attrMap['Phiên bản'] = $variant->sku;
                    }
                    $variant->formatted_attributes = $attrMap;
                    
                    // BỔ SUNG ĐỒNG BỘ: Ép sẵn data hiển thị để Frontend vẽ Product Card dễ dàng
                    $variant->display_name = $item->product->name;
                    $variant->display_image = $variant->image_url ?: $item->product->thumbnail_image;
                    $variant->display_price = $variant->promotional_price ?: $variant->price;
                    $variant->category_name = $item->product->category->name ?? '';
                    
                    unset($variant->attributeValues);
                }
            }
            
            // Trường hợp 2: Biến thể đã được Admin chốt cứng (Cố định)
            if ($item->variant) {
                $attrMap = [];
                if ($item->variant->attributeValues) {
                    foreach ($item->variant->attributeValues as $val) {
                        if ($val->attribute) {
                            $attrMap[$val->attribute->name] = $val->value;
                        }
                    }
                }
                if (empty($attrMap)) {
                    $attrMap['Phiên bản'] = $item->variant->sku;
                }
                $item->variant->formatted_attributes = $attrMap;
                
                // BỔ SUNG ĐỒNG BỘ: Ép sẵn data hiển thị để Frontend vẽ Product Card dễ dàng
                $item->variant->display_name = $item->variant->product->name ?? 'Sản phẩm cao cấp';
                $item->variant->display_image = $item->variant->image_url ?: ($item->variant->product->thumbnail_image ?? null);
                $item->variant->display_price = $item->variant->promotional_price ?: $item->variant->price;
                $item->variant->category_name = $item->variant->product->category->name ?? '';
                
                unset($item->variant->attributeValues);
            }
        }

        return response()->json([
            'success' => true, 
            'data' => $combo
        ]);
    }
}