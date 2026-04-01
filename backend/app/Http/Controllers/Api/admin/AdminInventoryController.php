<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Combo;
use App\Http\Requests\AdminUpdateVariantStockRequest;
use App\Http\Requests\AdminUpdateComboLimitRequest;

class AdminInventoryController extends Controller
{
    /**
     * API Lấy danh sách Biến thể (Kèm thông tin Sản phẩm & Thuộc tính)
     */
    public function getVariants()
    {
        // Chỉ lấy các biến thể mà Sản phẩm gốc chưa bị xóa
        $variants = ProductVariant::whereHas('product')->with([
            'product:id,name,slug,category_id,base_price,status,thumbnail_image',
            'product.category:id,name',
            'attributeValues.attribute:id,name'
        ])->orderBy('id', 'desc')->get();

        // Map lại dữ liệu Attributes thành dạng {"Màu Sắc": "Đỏ", "Size": "10"} cho dễ dùng ở Frontend
        $variants->transform(function ($variant) {
            $attrMap = [];
            foreach ($variant->attributeValues as $val) {
                if ($val->attribute) {
                    $attrMap[$val->attribute->name] = $val->value;
                }
            }
            $variant->formatted_attributes = $attrMap;
            unset($variant->attributeValues);
            return $variant;
        });

        return response()->json(['success' => true, 'data' => $variants]);
    }

    /**
     * API Tối ưu: Chỉ cập nhật tồn kho, được bảo vệ bằng Request chặt chẽ
     */
    public function updateVariantStock(AdminUpdateVariantStockRequest $request, $id)
    {
        $variant = ProductVariant::findOrFail($id);
        $variant->update(['stock_quantity' => $request->stock_quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật tồn kho thành công'
        ]);
    }

    /**
     * API Tối ưu: Cập nhật giới hạn bán của Combo, được bảo vệ bằng Request
     */
    public function updateComboLimit(AdminUpdateComboLimitRequest $request, $id)
    {
        $combo = Combo::findOrFail($id);
        $combo->update(['usage_limit' => $request->usage_limit]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật giới hạn Combo thành công'
        ]);
    }
}