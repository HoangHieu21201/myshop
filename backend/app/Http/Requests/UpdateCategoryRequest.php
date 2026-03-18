<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->name) {
            $this->merge([
                'slug' => Str::slug($this->name)
            ]);
        }
    }

    public function rules(): array
    {
        $categoryId = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($categoryId)->whereNull('deleted_at')],
            'parent_id' => [
                'nullable', 
                'integer', 
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($categoryId) {
                    if ($value == $categoryId) {
                        $fail('Danh mục không thể tự làm cha của chính nó.');
                    }
                },
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'hidden'])],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            
            'attributes_schema' => ['nullable', 'string'], 
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên danh mục là bắt buộc.',
            'slug.unique' => 'Tên danh mục này đã tồn tại (Trùng Slug).',
        ];
    }
}