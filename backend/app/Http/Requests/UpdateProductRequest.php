<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            'category_id'       => 'required|exists:categories,id',
            'brand_id'          => 'nullable|exists:brands,id',
            'name'              => 'required|string|max:255',
            'slug'              => 'required|string|max:255|unique:products,slug,' . $productId,
            'base_price'        => 'required|numeric|min:0',
            'promotional_price' => 'nullable|numeric|min:0|lte:base_price',
            'description'       => 'nullable|string',
            'thumbnail_image'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'specifications'    => 'nullable|json',
            'is_featured'       => 'nullable|boolean',
            'status'            => 'required|in:published,draft,hidden',

            'variants_data'     => 'required|json',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Vui lòng chọn danh mục sản phẩm.',
            'name.required' => 'Tên sản phẩm không được để trống.',
            'slug.unique' => 'Đường dẫn (slug) này đã tồn tại trên một sản phẩm khác.',
            'thumbnail_image.max' => 'Kích thước ảnh tối đa là 2MB.',
            'variants_data.required' => 'Dữ liệu biến thể không được để trống.',
        ];
    }
}
