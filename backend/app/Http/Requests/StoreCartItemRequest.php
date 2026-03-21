<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ProductVariant;

class StoreCartItemRequest extends FormRequest
{
    /**
     * Xác định người dùng có quyền thực hiện request này không.
     */
    public function authorize(): bool
    {
        // Cho phép cả Guest (không đăng nhập) và User thực hiện thêm vào giỏ hàng
        return true;
    }

    /**
     * Các rules validation cơ bản.
     */
    public function rules(): array
    {
        return [
            // Đảm bảo biến thể tồn tại và chưa bị xóa mềm (deleted_at = NULL)
            'product_variant_id' => [
                'required',
                'integer',
                'exists:product_variants,id,deleted_at,NULL'
            ],
            // Số lượng phải lớn hơn 0
            'quantity' => 'required|integer|min:1'
        ];
    }

    /**
     * Hook "Sparring Partner": Validate nâng cao (Blind spot về Tồn kho)
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Chỉ chạy check tồn kho nếu các rules cơ bản ở trên đã hợp lệ
            if (!$validator->errors()->has('product_variant_id') && !$validator->errors()->has('quantity')) {
                $variantId = $this->input('product_variant_id');
                $requestedQty = $this->input('quantity');

                $variant = ProductVariant::find($variantId);

                // Kiểm tra nếu khách đòi mua số lượng lớn hơn kho đang có
                if ($variant && $variant->stock_quantity < $requestedQty) {
                    $validator->errors()->add(
                        'quantity',
                        'Rất tiếc, kho chỉ còn ' . $variant->stock_quantity . ' sản phẩm cho phân loại này.'
                    );
                }
            }
        });
    }

    /**
     * Tùy chỉnh thông báo lỗi sang tiếng Việt.
     */
    public function messages(): array
    {
        return [
            'product_variant_id.required' => 'Vui lòng chọn một biến thể sản phẩm.',
            'product_variant_id.exists' => 'Sản phẩm này không tồn tại hoặc đã ngừng kinh doanh.',
            'quantity.required' => 'Vui lòng nhập số lượng.',
            'quantity.min' => 'Số lượng tối thiểu là 1.',
        ];
    }
}