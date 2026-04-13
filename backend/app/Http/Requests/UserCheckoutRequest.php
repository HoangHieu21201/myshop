<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            // Nếu có ID địa chỉ thì phải tồn tại trong DB của bảng user_addresses
            'user_address_id'  => 'nullable|exists:user_addresses,id',
            
            // Các trường này chỉ bắt buộc khi KHÔNG dùng địa chỉ có sẵn (user_address_id bị null)
            'customer_name'    => 'required_without:user_address_id|string|max:255|nullable',
            'customer_phone'   => 'required_without:user_address_id|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:9|max:15|nullable',
            'customer_address' => 'required_without:user_address_id|string|max:1000|nullable',
            
            // Email và các thông tin khác luôn bắt buộc vì cần gửi hóa đơn
            'customer_email'   => 'required|email|max:255',
            'order_note'       => 'nullable|string|max:1000',
            'payment_method'   => 'required|in:cod,vnpay,momo,bank_transfer',
            'coupon_code'      => 'nullable|string|exists:coupons,code',
            'shipping_fee'     => 'required|integer|min:0|max:200000', // phí ship phải là số nguyên, ≥ 0
        ];
    }

    public function messages(): array
    {
        return [
            'user_address_id.exists'            => 'Địa chỉ đã chọn không tồn tại trên hệ thống.',
            
            'customer_name.required_without'    => 'Vui lòng nhập họ tên người nhận hàng.',
            'customer_name.max'                 => 'Họ tên không được vượt quá 255 ký tự.',
            
            'customer_phone.required_without'   => 'Vui lòng nhập số điện thoại.',
            'customer_phone.regex'              => 'Số điện thoại không hợp lệ.',
            'customer_phone.min'                => 'Số điện thoại phải có ít nhất 9 số.',
            
            'customer_email.required'           => 'Vui lòng nhập địa chỉ email để nhận hóa đơn.',
            'customer_email.email'              => 'Email không đúng định dạng.',
            
            'customer_address.required_without' => 'Vui lòng nhập địa chỉ giao hàng cụ thể.',
            
            'payment_method.required'           => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in'                 => 'Phương thức thanh toán không được hỗ trợ.',
            // ==================== THÊM MỚI ====================
            'shipping_fee.required' => 'Phí vận chuyển không được để trống.',
            'shipping_fee.integer'  => 'Phí vận chuyển phải là số nguyên.',
            'shipping_fee.min'      => 'Phí vận chuyển không được âm.',
            'shipping_fee.max'      => 'Phí vận chuyển không được vượt quá 200.000đ.',
        ];
    }
}