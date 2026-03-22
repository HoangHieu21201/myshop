<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Xác định người dùng có quyền thực hiện request này không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Các quy tắc xác thực khi cập nhật đơn hàng.
     * Hỗ trợ cả việc Admin cập nhật trạng thái tổng quát và User hủy đơn.
     */
    public function rules(): array
    {
        return [
            // Cập nhật trạng thái đơn hàng
            'status' => 'required|in:pending,confirmed,processing,shipping,delivered,cancelled,returned',
            
            // Cập nhật trạng thái thanh toán
            'payment_status' => 'required|in:unpaid,paid,refunded,failed',
            
            // Ghi chú hoặc lý do thay đổi (VD: lý do hủy đơn)
            'note' => 'nullable|string|max:1000',
            
            // Nếu bạn vẫn muốn dùng logic cũ 'action' và 'cancel_reason' cho Client:
            'action' => 'nullable|in:cancel',
            'cancel_reason' => 'required_if:action,cancel|nullable|string|min:10',
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt.
     */
    public function messages(): array
    {
        return [
            'status.required'        => 'Vui lòng xác định trạng thái đơn hàng.',
            'status.in'              => 'Trạng thái đơn hàng không hợp lệ.',
            'payment_status.required' => 'Vui lòng xác định trạng thái thanh toán.',
            'payment_status.in'      => 'Trạng thái thanh toán không hợp lệ.',
            'cancel_reason.required_if' => 'Vui lòng nhập lý do khi thực hiện hủy đơn.',
            'cancel_reason.min'      => 'Lý do hủy đơn phải có ít nhất 10 ký tự.',
        ];
    }
}