<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientAddressUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:150',
            'customer_phone' => 'required|string|max:12',
            'city' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'ward' => 'required|string|max:100',
            'shipping_address' => 'required|string|max:255',
            'is_default' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'Vui lòng nhập tên người nhận.',
            'customer_name.max' => 'Tên người nhận không được vượt quá 150 ký tự.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại.',
            'customer_phone.max' => 'Số điện thoại không được vượt quá 12 ký tự.',
            'city.required' => 'Vui lòng chọn tỉnh/thành phố.',
            'district.required' => 'Vui lòng chọn quận/huyện.',
            'ward.required' => 'Vui lòng chọn phường/xã.',
            'shipping_address.required' => 'Vui lòng nhập địa chỉ chi tiết.',
            'shipping_address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('customer_phone') && $this->customer_phone) {
                $normalizedPhone = $this->normalizeVietnamesePhone($this->customer_phone);
                if (!$normalizedPhone) {
                    $validator->errors()->add('customer_phone', 'Số điện thoại không hợp lệ! Vui lòng nhập số điện thoại Việt Nam 10 số bắt đầu bằng số 0 (ví dụ: 0912345678).');
                } else {
                    $this->merge(['customer_phone' => $normalizedPhone]);
                }
            }
        });
    }

    /**
     * Chuẩn hóa số điện thoại Việt Nam (10 số)
     * Bắt buộc phải bắt đầu bằng số 0
     */
    private function normalizeVietnamesePhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Loại bỏ tất cả ký tự không phải số
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Kiểm tra độ dài phải là 10 số
        if (strlen($phone) !== 10) {
            return null;
        }

        // Phải bắt đầu bằng số 0
        if (!str_starts_with($phone, '0')) {
            return null;
        }

        // Các đầu số Việt Nam hợp lệ (sau số 0)
        $validPrefixes = ['32', '33', '34', '35', '36', '37', '38', '39', '52', '56', '58', '59', '70', '76', '77', '78', '79', '82', '83', '84', '85', '86', '87', '88', '89', '90', '91', '92', '93', '94', '96', '97', '98', '99'];

        $prefix = substr($phone, 1, 2); // Lấy 2 số sau số 0
        if (!in_array($prefix, $validPrefixes)) {
            return null;
        }

        return $phone;
    }
}