<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Lấy ID của nhân viên đang được update từ URL (Route API Resource lấy tham số là 'staff')
        $adminId = $this->route('staff'); 

        return [
            'fullname' => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:100', Rule::unique('admins', 'email')->ignore($adminId)],
            'phone'    => ['nullable', 'string', 'max:20'],
            'role_id'  => ['required', 'integer', 'exists:roles,id'],
            'status'   => ['required', 'string', Rule::in(['active', 'locked'])],
            'password' => ['nullable', 'string', 'min:8'], // Mật khẩu không bắt buộc khi update
            'avatar'   => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'address'  => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'fullname.required' => 'Họ và tên là bắt buộc.',
            'email.required'    => 'Email là bắt buộc.',
            'email.unique'      => 'Email này đã tồn tại trong hệ thống.',
            'password.min'      => 'Mật khẩu phải từ 8 ký tự trở lên.',
            'role_id.required'  => 'Chưa chọn chức vụ/phân quyền.',
            'role_id.exists'    => 'Chức vụ không hợp lệ.',
            'status.in'         => 'Trạng thái không hợp lệ.',
            'avatar.image'      => 'File tải lên phải là hình ảnh.',
            'avatar.max'        => 'Dung lượng ảnh tối đa là 2MB.',
        ];
    }
}