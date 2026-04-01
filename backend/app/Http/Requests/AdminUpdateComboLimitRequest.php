<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateComboLimitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usage_limit' => 'nullable|integer|min:0'
        ];
    }

    public function messages(): array
    {
        return [
            'usage_limit.integer' => 'Giới hạn số lượng phải là số nguyên.',
            'usage_limit.min'     => 'Giới hạn số lượng không được nhỏ hơn 0.',
        ];
    }
}