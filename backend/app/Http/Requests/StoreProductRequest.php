<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules() {
        return [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug',
            'base_price' => 'required|numeric|min:0',
            'status' => 'required|in:published,draft,hidden',
            'thumbnail_image' => 'required|image|max:2048',
            
            // Validation cho mảng Biến thể (Được gửi dưới dạng JSON string)
            'variants_data' => 'required|json'
        ];
    }
}