<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'         => 'required|string|max:255',
            'brand_id'      => 'nullable|exists:brands,id',
            'position'      => 'nullable|string|max:50',
            'target_url'    => 'nullable|url|max:500', 
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'status'        => 'required|in:active,hidden',
            'image_desktop' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_mobile'  => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'title.required'         => 'Tên chiến dịch banner không được để trống.',
            'image_desktop.required' => 'Vui lòng chọn ảnh cho màn hình Desktop.',
            'image_mobile.required'  => 'Vui lòng chọn ảnh cho màn hình Mobile.',
            'image_desktop.max'      => 'Ảnh Desktop không được vượt quá 2MB.',
            'image_mobile.max'       => 'Ảnh Mobile không được vượt quá 2MB.',
            'end_date.after_or_equal'=> 'Ngày kết thúc phải diễn ra sau hoặc cùng ngày bắt đầu.',
            'target_url.url'         => 'Đường dẫn đích phải là một URL hợp lệ (VD: https://...).'
        ];
    }
}