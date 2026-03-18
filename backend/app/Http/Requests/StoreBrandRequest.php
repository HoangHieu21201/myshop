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
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:brands,slug|max:255',
            'logo' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'status' => 'required|in:active,hidden',
        ];
    }
}
