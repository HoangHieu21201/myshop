<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fullName' => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'status'   => ['required', Rule::in(['active', 'locked'])],
            'gender'   => ['nullable', 'string', 'max:10'],
            'birthday' => ['nullable', 'date'],
            'avatar'   => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'] // Nâng giới hạn lên 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'fullName.required' => 'Họ và tên là bắt buộc.',
            'email.required'    => 'Email là bắt buộc.',
            'email.unique'      => 'Email này đã được sử dụng.',
            'password.required' => 'Mật khẩu là bắt buộc.',
            'password.min'      => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'status.in'         => 'Trạng thái không hợp lệ.',
            'avatar.image'      => 'File tải lên phải là hình ảnh.',
            'avatar.max'        => 'Dung lượng ảnh tối đa là 5MB.',
        ];
    }
}