<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientProfileUpdateRequest extends FormRequest
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
            'fullName' => 'required|string|max:150',
            'phone' => 'nullable|string|max:12',
            'birthday' => 'nullable|date',
            'gender' => 'nullable|in:Nam,Nữ,Khác',
            'avatar' => 'nullable|image|max:5120',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'fullName.required' => 'Vui lòng nhập họ tên.',
            'fullName.max' => 'Họ tên không được vượt quá 150 ký tự.',
            'phone.max' => 'Số điện thoại không được vượt quá 12 ký tự.',
            'birthday.date' => 'Ngày sinh không đúng định dạng.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'avatar.image' => 'File avatar phải là hình ảnh.',
            'avatar.max' => 'Kích thước avatar không được vượt quá 5MB.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate birthday không được lớn hơn ngày hiện tại
            if ($this->has('birthday') && $this->birthday) {
                $birthday = \Carbon\Carbon::parse($this->birthday);
                $today = \Carbon\Carbon::now();
                
                if ($birthday->greaterThan($today)) {
                    $validator->errors()->add('birthday', 'Ngày sinh không được lớn hơn ngày hiện tại!');
                }
                
                // Validate tuổi hợp lý (không quá 150 tuổi, không dưới 1 tuổi)
                $age = $birthday->diffInYears($today);
                if ($age > 150 || $age < 1) {
                    $validator->errors()->add('birthday', 'Ngày sinh không hợp lệ!');
                }
            }

            // Kiểm tra số điện thoại đã tồn tại chưa (trừ user hiện tại và số điện thoại cũ của chính user đó)
            if ($this->has('phone') && $this->phone) {
                $normalizedPhone = $this->normalizeVietnamesePhone($this->phone);
                
                if (!$normalizedPhone) {
                    $validator->errors()->add('phone', 'Số điện thoại không hợp lệ! Vui lòng nhập số điện thoại Việt Nam 10 số bắt đầu bằng số 0 (ví dụ: 0912345678).');
                    return;
                }

                $userId = $this->user()?->id;
                $exists = \App\Models\User::where('phone', $normalizedPhone)
                    ->when($userId, fn($q) => $q->where('id', '!=', $userId))
                    ->exists();
                
                if ($exists) {
                    $validator->errors()->add('phone', 'Số điện thoại này đã được sử dụng bởi tài khoản khác!');
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Chuẩn hóa số điện thoại Việt Nam trước khi validate
        if ($this->has('phone') && $this->phone) {
            $normalizedPhone = $this->normalizeVietnamesePhone($this->phone);
            if (!$normalizedPhone) {
                // Để validator xử lý lỗi này
                return;
            }
            $this->merge(['phone' => $normalizedPhone]);
        }
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