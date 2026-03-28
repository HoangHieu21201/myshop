<?php

namespace App\Http\Controllers\Api\client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ClientCompareController extends Controller
{
    /**
     * Nhận mảng các ID sản phẩm và trả về thông tin chi tiết để so sánh
     */
    public function getCompareData(Request $request, $shop_slug)
    {
        $productIds = $request->input('product_ids', []);

        if (empty($productIds)) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        try {
            // Lấy dữ liệu sản phẩm cùng với brand và category
            $products = Product::with([
                'brand:id,name',
                'category:id,name'
            ])
            ->whereIn('id', $productIds)
            ->where('status', 'published')
            ->get();

            // Sắp xếp lại mảng theo đúng thứ tự ID mà user đã chọn
            $sortedProducts = collect($productIds)->map(function ($id) use ($products) {
                return $products->firstWhere('id', $id);
            })->filter()->values();

            // Format dữ liệu trả về
            $formattedData = $sortedProducts->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'thumbnail_image' => $product->thumbnail_image ? asset('storage/' . $product->thumbnail_image) : null,
                    'base_price' => (float) $product->base_price,
                    'promotional_price' => $product->promotional_price ? (float) $product->promotional_price : null,
                    'brand_name' => $product->brand ? $product->brand->name : 'Không có',
                    'category_name' => $product->category ? $product->category->name : 'Không có',
                    'description' => $product->description,
                    // Giả sử specifications lưu dạng JSON, decode nó ra
                    'specifications' => is_string($product->specifications) ? json_decode($product->specifications, true) : $product->specifications,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy dữ liệu so sánh',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}