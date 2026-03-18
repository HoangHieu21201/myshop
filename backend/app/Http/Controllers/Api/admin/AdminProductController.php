<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminProductController extends Controller
{
    // 1. LẤY DANH SÁCH (Bao gồm cả Đã xóa)
    public function index()
    {
        $products = Product::withTrashed()
            ->with('category:id,name')
            ->withCount('variants')
            ->withSum('variants as total_stock', 'stock_quantity')
            ->orderBy('id', 'desc')->get();
            
        return response()->json(['success' => true, 'data' => $products]);
    }

    // 2. XEM CHI TIẾT (Cho Quick View & Edit)
    public function show($id)
    {
        $product = Product::with([
            'category:id,name',
            'variants.attributeValues' 
        ])->findOrFail($id);

        $product->variants->transform(function ($variant) {
            $attrMap = [];
            foreach ($variant->attributeValues as $val) {
                $attrMap[$val->attribute_id] = $val->id;
            }
            $variant->attributes = $attrMap; 
            unset($variant->attributeValues); 
            return $variant;
        });

        $product->total_stock = $product->variants->sum('stock_quantity');
        return response()->json(['success' => true, 'data' => $product]);
    }

    // 3. TẠO MỚI SẢN PHẨM & BIẾN THỂ
    public function store(StoreProductRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            
            $file = $request->file('thumbnail_image');
            $fileName = 'prod_' . Str::slug($data['name']) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('products/thumbnails', $fileName, 'public');
            $data['thumbnail_image'] = $path;

            $product = Product::create($data);
            $variantsData = json_decode($request->variants_data, true);
            
            foreach ($variantsData as $index => $vData) {
                $variantImagePath = null;
                $imageKey = 'variant_image_' . $index;
                if ($request->hasFile($imageKey)) {
                    $vFile = $request->file($imageKey);
                    $vFileName = 'var_' . Str::slug($vData['sku']) . '_' . time() . '.' . $vFile->getClientOriginalExtension();
                    $variantImagePath = $vFile->storeAs('products/variants', $vFileName, 'public');
                }

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $vData['sku'],
                    'price' => $vData['price'],
                    'promotional_price' => $vData['promotional_price'] ?: null,
                    'stock_quantity' => $vData['stock_quantity'],
                    'image_url' => $variantImagePath,
                    'is_default' => $index === 0 ? 1 : 0
                ]);

                if (!empty($vData['attributes']) && is_array($vData['attributes'])) {
                    $attributeValueIds = array_values($vData['attributes']); 
                    $variant->attributeValues()->sync($attributeValueIds);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Xuất bản sản phẩm thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // 4. CẬP NHẬT SẢN PHẨM & ĐỒNG BỘ LẠI LƯỚI BIẾN THỂ
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $product = Product::findOrFail($id);
            
            // Validate cơ bản (Bỏ qua qua request riêng để gọn code)
            $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|unique:products,slug,'.$id,
                'base_price' => 'required|numeric|min:0',
                'status' => 'required|in:published,draft,hidden',
                'variants_data' => 'required|json'
            ]);

            $data = $request->only(['category_id', 'name', 'slug', 'base_price', 'status']);

            // Xử lý ảnh chính (Nếu có up ảnh mới)
            if ($request->hasFile('thumbnail_image')) {
                $file = $request->file('thumbnail_image');
                $fileName = 'prod_' . Str::slug($data['name']) . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('products/thumbnails', $fileName, 'public');
                $data['thumbnail_image'] = $path;
                // Xóa ảnh cũ (Tùy chọn)
                if ($product->thumbnail_image) Storage::disk('public')->delete($product->thumbnail_image);
            }

            $product->update($data);

            // XỬ LÝ LƯỚI BIẾN THỂ
            $variantsData = json_decode($request->variants_data, true);
            $incomingVariantIds = array_filter(array_column($variantsData, 'id')); 

            // Xóa các biến thể có trong DB nhưng bị Admin xóa trên giao diện
            ProductVariant::where('product_id', $product->id)
                ->whereNotIn('id', $incomingVariantIds)
                ->delete();

            foreach ($variantsData as $index => $vData) {
                $variantImagePath = isset($vData['current_image']) ? $vData['current_image'] : null;
                $imageKey = 'variant_image_' . $index;
                
                if ($request->hasFile($imageKey)) {
                    $vFile = $request->file($imageKey);
                    $vFileName = 'var_' . Str::slug($vData['sku']) . '_' . time() . '.' . $vFile->getClientOriginalExtension();
                    $variantImagePath = $vFile->storeAs('products/variants', $vFileName, 'public');
                }

                $variantPayload = [
                    'sku' => $vData['sku'],
                    'price' => $vData['price'],
                    'promotional_price' => $vData['promotional_price'] ?: null,
                    'stock_quantity' => $vData['stock_quantity'],
                    'image_url' => $variantImagePath,
                ];

                if (isset($vData['id']) && $vData['id']) {
                    // Cập nhật biến thể cũ
                    $variant = ProductVariant::find($vData['id']);
                    if($variant) $variant->update($variantPayload);
                } else {
                    // Thêm biến thể mới
                    $variantPayload['product_id'] = $product->id;
                    $variantPayload['is_default'] = $index === 0 ? 1 : 0;
                    $variant = ProductVariant::create($variantPayload);
                }

                // Cập nhật lại Bảng Pivot
                if ($variant && !empty($vData['attributes']) && is_array($vData['attributes'])) {
                    $attributeValueIds = array_values($vData['attributes']); 
                    $variant->attributeValues()->sync($attributeValueIds);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Cập nhật sản phẩm thành công']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // 5. XÓA MỀM
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['success' => true, 'message' => 'Sản phẩm đã chuyển vào thùng rác']);
    }

    // 6. KHÔI PHỤC
    public function restore($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();
        return response()->json(['success' => true, 'message' => 'Sản phẩm đã được khôi phục']);
    }
}