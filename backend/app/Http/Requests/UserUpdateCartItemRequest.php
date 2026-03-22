<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\CartItem;

class UserUpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => [
                'required',
                'integer',
                'min:1'
            ],
        ];
    }

    /**
     * Sparring Partner: Kiểm tra tồn kho khi khách tăng số lượng ở trang Giỏ hàng
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Lấy CartItem từ route (Giả định route là /api/cart/{cartItem})
            $cartItem = $this->route('cart_item'); 
            
            if ($cartItem) {
                $variant = $cartItem->variant;
                if ($variant && $this->quantity > $variant->stock_quantity) {
                    $validator->errors()->add('quantity', "Kho không đủ, tối đa bạn có thể mua là {$variant->stock_quantity} chiếc.");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'Số lượng không được để trống.',
            'quantity.min'      => 'Số lượng không được nhỏ hơn 1.',
        ];
    }
}