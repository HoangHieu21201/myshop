<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ProductVariant;

class StoreCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Cho phép cả Guest và User
    }

    public function rules(): array
    {
        return [
            'product_variant_id' => [
                'required',
                'exists:product_variants,id,deleted_at,NULL'
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1'
            ],
        ];
    }

    /**
     * Sparring Partner: Chặn lỗi đặt quá số lượng tồn kho ngay tại tầng Request
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $variant = ProductVariant::find($this->product_variant_id);
            if ($variant && $this->quantity > $variant->stock_quantity) {
                $validator->errors()->add('quantity', "Rất tiếc, sản phẩm này chỉ còn {$variant->stock_quantity} chiếc trong kho.");
            }
        });
    }

    public function messages(): array
    {
        return [
            'product_variant_id.required' => 'Vui lòng chọn phân loại sản phẩm.',
            'product_variant_id.exists'   => 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.',
            'quantity.required'           => 'Vui lòng nhập số lượng.',
            'quantity.min'                => 'Số lượng tối thiểu là 1.',
        ];
    }
}