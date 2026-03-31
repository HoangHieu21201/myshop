<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('admin_reply')) {
            $reply = trim($this->admin_reply);
            $reply = strip_tags($reply);

            $this->merge([
                'admin_reply' => $reply === '' ? null : $reply,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['pending', 'approved', 'hidden']),
            ],
            'admin_reply' => [
                'nullable',
                'string',
                'max:1500',
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $status = $this->input('status');
            $reply = $this->input('admin_reply');

            if ($status === 'hidden' && !empty($reply)) {
                $validator->errors()->add(
                    'admin_reply',
                    'Bạn đang viết phản hồi nhưng lại để trạng thái "Ẩn". Khách hàng sẽ không thấy được phản hồi này!'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'status.required'    => 'Vui lòng chọn trạng thái kiểm duyệt.',
            'status.in'          => 'Trạng thái không hợp lệ. Chỉ chấp nhận: Chờ duyệt, Đã duyệt, hoặc Đã ẩn.',
            'admin_reply.string' => 'Nội dung phản hồi không đúng định dạng.',
            'admin_reply.max'    => 'Phản hồi quá dài! Vui lòng viết ngắn gọn dưới :max ký tự.',
        ];
    }

    public function attributes(): array
    {
        return [
            'status'      => 'Trạng thái',
            'admin_reply' => 'Phản hồi của cửa hàng',
        ];
    }
}
