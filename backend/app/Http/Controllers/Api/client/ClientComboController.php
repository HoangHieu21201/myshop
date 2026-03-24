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
                $q->with(['variants' => function($vq) {
                    $vq->where('stock_quantity', '>', 0)
                       // Load kèm theo các Thuộc tính (Size, Màu...) của biến thể này
                       ->with(['attributeValues.attribute']);
                }]);
            },
            'items.variant.attributeValues.attribute' 
        ])
        ->where('slug', $slug)
        ->where('status', 'active')
        ->firstOrFail();

        // THUẬT TOÁN: Gom nhóm Thuộc tính thành Mảng (VD: {'Kích cỡ': '16', 'Màu sắc': 'Đỏ'})
        // Giúp Frontend hiển thị các ô vuông (chips) dễ dàng hơn thay vì phải gọi Nested Object
        foreach ($combo->items as $item) {
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
                    // Nếu sản phẩm không có thuộc tính nào, lấy tạm SKU làm thuộc tính mặc định
                    if (empty($attrMap)) {
                        $attrMap['Phiên bản'] = $variant->sku;
                    }
                    $variant->formatted_attributes = $attrMap;
                    unset($variant->attributeValues);
                }
            }
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
                unset($item->variant->attributeValues);
            }
        }

        return response()->json([
            'success' => true, 
            'data' => $combo
        ]);
    }
}