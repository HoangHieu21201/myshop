<?php
namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ProductVariant;
use App\Models\Combo;

class UserStoreCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            // Cần 1 trong 2: hoặc là variant lẻ, hoặc là combo
            'product_variant_id' => [
                'required_without:combo_id',
                'nullable',
                'exists:product_variants,id,deleted_at,NULL' // Có thể thêm rule kiểm tra is_active nếu DB có
            ],
            'combo_id' => [
                'required_without:product_variant_id',
                'nullable',
                'exists:combos,id,deleted_at,NULL'
            ],
            'combo_selections' => [
                'nullable',
                'array'
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1'
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Kiểm tra tồn kho cho sản phẩm lẻ (Tạm thời check số lượng gửi lên. 
            // Lưu ý: Logic cộng dồn tồn kho chặt chẽ nhất nên được đặt ở Controller hoặc CartService)
            if ($this->filled('product_variant_id')) {
                $variant = ProductVariant::find($this->product_variant_id);
                if ($variant && $this->quantity > $variant->stock_quantity) {
                    $validator->errors()->add('quantity', "Rất tiếc, sản phẩm này chỉ còn {$variant->stock_quantity} chiếc trong kho.");
                }
            }

            // Gợi ý: Nếu là Combo, bạn có thể loop qua combo_selections để check tồn kho của từng variant nhỏ ở đây.
        });
    }

    public function messages(): array
    {
        return [
            'product_variant_id.required_without' => 'Vui lòng chọn sản phẩm hoặc combo.',
            'combo_id.required_without'           => 'Vui lòng chọn sản phẩm hoặc combo.',
            'product_variant_id.exists'           => 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.',
            'combo_id.exists'                     => 'Combo không tồn tại hoặc đã ngừng kinh doanh.',
            'quantity.required'                   => 'Vui lòng nhập số lượng.',
            'quantity.min'                        => 'Số lượng tối thiểu là 1.',
        ];
    }
}