<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUpdateBrandRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('brand'); 

        return [
            'name'        => 'required|string|max:255',
            'slug'        => ['required', 'string', 'max:255', Rule::unique('brands', 'slug')->ignore($id)->whereNull('deleted_at')],
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:15360', //15mb
            'description' => 'nullable|string|max:5000',
            'status'      => 'required|in:active,hidden',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Tên thương hiệu không được để trống.',
            'name.max'         => 'Tên thương hiệu không được vượt quá 255 ký tự.',
            'slug.required'    => 'Thương hiệu (slug) không được để trống.',
            'slug.unique'      => 'Thương hiệu (slug) này đã bị trùng với thương hiệu khác.',
            'logo.image'       => 'File tải lên phải là hình ảnh.',
            'logo.mimes'       => 'Định dạng ảnh không hợp lệ (Chỉ hỗ trợ jpeg, png, jpg, webp, svg).',
            'logo.max'         => 'Kích thước logo không được vượt quá 15MB.',
            'status.in'        => 'Trạng thái không hợp lệ.',
        ];
    }
}