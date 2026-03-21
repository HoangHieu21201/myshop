<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:brands,slug',
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'description' => 'nullable|string|max:5000',
            'status'      => 'required|in:active,hidden',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Tên thương hiệu không được để trống.',
            'name.max'         => 'Tên thương hiệu không được vượt quá 255 ký tự.',
            'slug.required'    => 'Đường dẫn (slug) không được để trống.',
            'slug.unique'      => 'Đường dẫn (slug) này đã tồn tại, vui lòng chọn tên khác.',
            'logo.image'       => 'File tải lên phải là hình ảnh.',
            'logo.mimes'       => 'Định dạng ảnh không hợp lệ (Chỉ hỗ trợ jpeg, png, jpg, webp, svg).',
            'logo.max'         => 'Kích thước logo không được vượt quá 2MB.',
            'status.required'  => 'Trạng thái không được để trống.',
            'status.in'        => 'Trạng thái không hợp lệ.',
        ];
    }
}