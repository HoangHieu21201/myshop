<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fullName' => ['required', 'string', 'max:150'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'status'   => ['required', Rule::in(['active', 'locked'])],
            'gender'   => ['nullable', 'string', 'max:10'],
            'birthday' => ['nullable', 'date'],
            'password' => ['nullable', 'string', 'min:8'],
            'avatar'   => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120']
        ];
    }

    public function messages(): array
    {
        return [
            'fullName.required' => 'Họ và tên là bắt buộc.',
            'password.min'      => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'status.in'         => 'Trạng thái không hợp lệ.',
            'avatar.image'      => 'File tải lên phải là hình ảnh.',
            'avatar.max'        => 'Dung lượng ảnh tối đa là 5MB.',
        ];
    }
}