<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Cart;
use App\Models\ProductVariant;

class UserStoreOrderRequest extends FormRequest
{
    /**
     * Xác định quyền thực hiện request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Các quy tắc xác thực cơ bản cho việc đặt hàng.
     */
    public function rules(): array
    {
        return [
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:20',
            'customer_address' => 'required|string',
            'payment_method'   => 'required|in:cod,vnpay,momo,bank_transfer',
            'coupon_code'      => 'nullable|string|exists:coupons,code',
            'order_note'       => 'nullable|string|max:1000',
        ];
    }

    /**
     * Xác thực nâng cao: Kiểm tra giỏ hàng và tồn kho thực tế ngay trước khi đặt hàng.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = auth('sanctum')->user();
            $sessionId = $this->header('X-Cart-Session-Id');

            // 1. Tìm giỏ hàng dựa trên User hoặc Session (Guest)
            $cart = $user 
                ? Cart::with('items.variant')->where('user_id', $user->id)->first() 
                : Cart::with('items.variant')->where('session_id', $sessionId)->first();

            if (!$cart || $cart->items->isEmpty()) {
                $validator->errors()->add('cart', 'Giỏ hàng của bạn đang trống.');
                return;
            }

            // 2. Kiểm tra tồn kho cho từng biến thể trong giỏ
            foreach ($cart->items as $item) {
                $variant = $item->variant;
                if (!$variant || $variant->stock_quantity < $item->quantity) {
                    $sku = $variant ? $variant->sku : 'Không xác định';
                    $validator->errors()->add('items', "Sản phẩm SKU {$sku} không đủ số lượng trong kho.");
                }
            }
        });
    }

    /**
     * Thông báo lỗi tiếng Việt.
     */
    public function messages(): array
    {
        return [
            'customer_name.required'    => 'Vui lòng nhập họ tên người nhận.',
            'customer_phone.required'   => 'Vui lòng nhập số điện thoại.',
            'customer_address.required' => 'Vui lòng nhập địa chỉ giao hàng.',
            'payment_method.required'   => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in'         => 'Phương thức thanh toán không hợp lệ.',
            'coupon_code.exists'        => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.',
        ];
    }
}