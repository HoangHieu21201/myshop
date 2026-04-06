<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail; // BẮT BUỘC IMPORT ĐỂ GỬI MAIL

class AdminContactController extends Controller
{
    /**
     * Lấy danh sách liên hệ
     */
    public function index()
    {
        $contacts = Contact::orderByRaw("FIELD(status, 'pending', 'resolved')")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $contacts
        ]);
    }

    /**
     * Cập nhật trạng thái
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,resolved']);

        $contact = Contact::find($id);
        if (!$contact) return response()->json(['status' => false, 'message' => 'Không tìm thấy liên hệ'], 404);

        $contact->status = $request->status;
        $contact->save();

        return response()->json(['status' => true, 'message' => 'Đã cập nhật trạng thái thành công!']);
    }

    /**
     * Xóa tin nhắn
     */
    public function destroy($id)
    {
        $contact = Contact::find($id);
        if (!$contact) return response()->json(['status' => false, 'message' => 'Không tìm thấy liên hệ'], 404);

        $contact->delete();
        return response()->json(['status' => true, 'message' => 'Đã xóa tin nhắn liên hệ!']);
    }

    /**
     * ==========================================
     * TÍNH NĂNG MỚI: TRẢ LỜI EMAIL CHO KHÁCH
     * ==========================================
     */
    public function replyEmail(Request $request, $id)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $contact = Contact::find($id);
        if (!$contact) return response()->json(['status' => false, 'message' => 'Không tìm thấy liên hệ'], 404);

        try {
            $data = [
                'customerName' => $contact->fullname,
                'replyMessage' => $request->message,
                'originalMessage' => $contact->message
            ];

            // Hàm gửi Email bằng Laravel Mail
            Mail::send([], [], function ($message) use ($contact, $request, $data) {
                $message->to($contact->email)
                        ->subject($request->subject)
                        ->html("
                            <div style='font-family: \"Segoe UI\", Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #eaeaea; border-radius: 8px; overflow: hidden;'>
                                <div style='background-color: #9f273b; padding: 25px; text-align: center;'>
                                    <h2 style='color: white; margin: 0; font-weight: 400; letter-spacing: 3px;'>SORA JEWELRY</h2>
                                </div>
                                <div style='padding: 30px;'>
                                    <p style='font-size: 16px;'>Kính gửi <strong>{$data['customerName']}</strong>,</p>
                                    <p>Cảm ơn quý khách đã liên hệ với SORA. Chuyên viên của chúng tôi xin phản hồi yêu cầu của quý khách như sau:</p>
                                    
                                    <div style='background-color: #faf9f8; padding: 20px; border-left: 4px solid #e7ce7d; margin: 25px 0; border-radius: 0 4px 4px 0;'>
                                        <p style='margin: 0;'>" . nl2br(htmlspecialchars($data['replyMessage'])) . "</p>
                                    </div>
                                    
                                    <p style='color: #666; font-size: 13px; margin-top: 30px;'>
                                        <strong>Nội dung quý khách đã gửi trước đó:</strong><br>
                                        <i style='color: #888;'>\"{$data['originalMessage']}\"</i>
                                    </p>
                                    <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
                                    <p style='font-size: 14px; color: #555; text-align: center;'>
                                        Trân trọng,<br>
                                        <strong style='color: #9f273b;'>Đội ngũ Chăm sóc khách hàng SORA</strong><br>
                                        <small>Hotline: 090 123 4567 | Website: sorajewelry.vn</small>
                                    </p>
                                </div>
                            </div>
                        ");
            });

            // Gửi xong thì tự động chuyển trạng thái thành "Đã xử lý"
            $contact->status = 'resolved';
            $contact->save();

            return response()->json([
                'status' => true,
                'message' => 'Đã gửi email phản hồi thành công!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Lỗi gửi mail SORA: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gửi mail thất bại. Vui lòng kiểm tra lại cấu hình .env'
            ], 500);
        }
    }
}