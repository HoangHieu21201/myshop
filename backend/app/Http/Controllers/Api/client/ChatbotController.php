<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $userMessage = $request->message;
        $apiKey = env('GEMINI_API_KEY');

        // BÍ QUYẾT: ÉP NHÂN CÁCH CHO AI Ở ĐÂY
        $systemInstruction = "Bạn là SORA Assistant, chuyên viên tư vấn kim hoàn cao cấp của thương hiệu Trang sức SORA. 
        Giọng điệu của bạn phải lịch sự, tinh tế, sang trọng. Luôn xưng hô là 'Tôi' và gọi khách hàng là 'Quý khách'. 
        Thông tin cửa hàng: SORA chỉ bán vàng 18K, bạch kim và kim cương thiên nhiên cao cấp. Cửa hàng tọa lạc tại thành phố Buôn Ma Thuột, tỉnh Đắk Lắk.
        Quy tắc: Không trả lời các câu hỏi ngoài lề không liên quan đến trang sức, mua sắm hoặc quà tặng. Luôn trả lời ngắn gọn, súc tích (dưới 100 chữ), định dạng HTML nhẹ nhàng (dùng thẻ <br> để xuống dòng, <b> để in đậm) để hiển thị đẹp trên khung chat.
        Nếu khách hỏi giá cụ thể hoặc yêu cầu xem hàng, hãy mời khách đến trực tiếp showroom tại Buôn Ma Thuột để trải nghiệm vẻ đẹp thực tế của sản phẩm.";

        // Gọi API đến Google Gemini 1.5 Flash
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
            'system_instruction' => [
                'parts' => ['text' => $systemInstruction]
            ],
            'contents' => [
                ['parts' => [['text' => $userMessage]]]
            ],
            'generationConfig' => [
                'temperature' => 0.7, // Độ sáng tạo vừa phải, giữ tính chuyên nghiệp
                'maxOutputTokens' => 250 // Giới hạn độ dài để bot không nói dông dài
            ]
        ]);

        // BÍ QUYẾT TRỊ GẠCH ĐỎ: Ép kiểu cho trình soạn thảo hiểu
        /** @var \Illuminate\Http\Client\Response $response */
        if ($response->successful()) {
            $data = $response->json();
            $botReply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Xin lỗi Quý khách, hiện tại hệ thống đang bận. Quý khách vui lòng liên hệ hotline nhé.';
            
            return response()->json([
                'success' => true,
                'reply' => $botReply
            ]);
        }

        return response()->json([
            'success' => false,
            'reply' => 'SORA Assistant đang nghỉ ngơi chút xíu. Quý khách vui lòng thử lại sau giây lát nhé!'
        ], 500);
    }
}