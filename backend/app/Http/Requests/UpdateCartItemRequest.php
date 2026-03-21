<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\CartItem;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => 'required|integer|min:1'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$validator->errors()->has('quantity')) {
                // Sử dụng Route Model Binding để lấy item hiện tại (VD: /api/cart/{cartItem})
                $cartItem = $this->route('cartItem');
                
                if ($cartItem) {
                    $requestedQty = $this->input('quantity');
                    $variant = $cartItem->variant;

                    // Check tồn kho live khi khách hàng bấm nút "+" trong Giỏ hàng
                    if ($variant && $variant->stock_quantity < $requestedQty) {
                        $validator->errors()->add(
                            'quantity',
                            'Sản phẩm chỉ còn tối đa ' . $variant->stock_quantity . ' chiếc.'
                        );
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'Vui lòng nhập số lượng.',
            'quantity.min' => 'Số lượng tối thiểu là 1.',
        ];
    }
}