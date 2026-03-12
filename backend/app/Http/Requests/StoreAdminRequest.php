<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fullname' => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:100', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8'],
            'role_id'  => ['required', 'integer', 'exists:roles,id'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'status'   => ['required', 'string', Rule::in(['active', 'locked'])],
            'avatar'   => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'], // 5MB
            'address'  => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'fullname.required' => 'Họ và tên là bắt buộc.',
            'email.required'    => 'Email là bắt buộc.',
            'email.unique'      => 'Email này đã tồn tại trong hệ thống.',
            'password.required' => 'Mật khẩu là bắt buộc.',
            'password.min'      => 'Mật khẩu phải từ 8 ký tự trở lên.',
            'role_id.required'  => 'Chưa chọn chức vụ/phân quyền.',
            'role_id.exists'    => 'Chức vụ không hợp lệ.',
            'status.in'         => 'Trạng thái không hợp lệ.',
            'avatar.image'      => 'File tải lên phải là hình ảnh.',
            'avatar.max'        => 'Dung lượng ảnh tối đa là 5MB.',
        ];
    }
}