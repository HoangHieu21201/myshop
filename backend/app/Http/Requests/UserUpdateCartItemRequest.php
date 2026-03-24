<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\CartItem;
use App\Models\ProductVariant;

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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $cartItemParam = $this->route('cart_item');
            $cartItem = $cartItemParam instanceof CartItem
                ? $cartItemParam
                : CartItem::find($cartItemParam);

            if (!$cartItem) {
                $validator->errors()->add('quantity', 'Không tìm thấy thông tin sản phẩm trong giỏ hàng.');
                return;
            }

            $requestedQuantity = (int) $this->quantity;

            if ($cartItem->product_variant_id) {
                $variant = $cartItem->variant;
                if (!$variant) {
                    $validator->errors()->add('quantity', 'Sản phẩm này đã không còn tồn tại trên hệ thống.');
                    return;
                }

                if ($requestedQuantity > $variant->stock_quantity) {
                    $validator->errors()->add('quantity', "Kho không đủ, tối đa bạn có thể mua là {$variant->stock_quantity} chiếc.");
                }
            }

            if ($cartItem->combo_id && is_array($cartItem->combo_selections)) {
                $variantIds = array_column($cartItem->combo_selections, 'selected_variant_id');
                $variantsInCombo = ProductVariant::whereIn('id', $variantIds)->get();

                foreach ($variantsInCombo as $v) {
                    if ($requestedQuantity > $v->stock_quantity) {
                        $validator->errors()->add('quantity', "Kho không đủ, một trong các sản phẩm thuộc combo chỉ còn tối đa {$v->stock_quantity} chiếc.");
                        break;
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'Số lượng không được để trống.',
            'quantity.integer'  => 'Số lượng phải là định dạng số.',
            'quantity.min'      => 'Số lượng không được nhỏ hơn 1.',
        ];
    }
}
