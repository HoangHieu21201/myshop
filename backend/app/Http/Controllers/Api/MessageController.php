<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * API: Nhận tin nhắn và Phát sóng
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
            'receiver_id' => 'nullable|integer' 
        ]);

        $isAdmin = $request->is('api/admin/*');
        $senderId = $isAdmin ? 1 : Auth::guard('sanctum')->id();

        if (!$senderId) {
            return response()->json(['status' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $message = Message::create([
            'sender_id' => $senderId,
            'receiver_id' => $request->receiver_id ?? 1, // Mặc định gửi cho Admin (ID = 1)
            'content' => $request->content,
            'is_read' => false
        ]);

        // Phát sóng cho người nhận
        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['status' => true, 'data' => $message]);
    }
    
    /**
     * API: Lấy lịch sử chat
     * Nếu Admin gọi: Phải truyền thêm partner_id (ID của khách)
     */
    public function history(Request $request)
    {
        $isAdmin = $request->is('api/admin/*');
        $userId = $isAdmin ? 1 : Auth::guard('sanctum')->id();
        $partnerId = $request->query('partner_id'); // Lấy ID của người đang chat cùng

        if ($partnerId) {
            // Nếu là Admin đang xem tin nhắn của 1 khách cụ thể, hoặc ngược lại
            $messages = Message::where(function($q) use ($userId, $partnerId) {
                $q->where('sender_id', $userId)->where('receiver_id', $partnerId);
            })->orWhere(function($q) use ($userId, $partnerId) {
                $q->where('sender_id', $partnerId)->where('receiver_id', $userId);
            })->orderBy('created_at', 'asc')->get();
        } else {
            // Lấy toàn bộ của chính mình
            $messages = Message::where('sender_id', $userId)
                               ->orWhere('receiver_id', $userId)
                               ->orderBy('created_at', 'asc')->get();
        }
                           
        return response()->json(['status' => true, 'data' => $messages]);
    }

    /**
     * API MỚI DÀNH CHO ADMIN: Lấy danh sách những người đã nhắn tin
     */
    public function getConversations()
    {
        // Hệ thống mốc Admin là ID 1
        $adminId = 1;

        // Tìm tất cả ID của khách đã nhắn tin cho Admin hoặc nhận tin từ Admin
        $senderIds = Message::where('receiver_id', $adminId)->pluck('sender_id')->toArray();
        $receiverIds = Message::where('sender_id', $adminId)->pluck('receiver_id')->toArray();
        
        $userIds = array_unique(array_merge($senderIds, $receiverIds));

        // Xóa ID Admin khỏi danh sách
        $userIds = array_diff($userIds, [1]);

        $users = User::whereIn('id', $userIds)->select('id', 'fullName', 'email')->get();

        return response()->json(['status' => true, 'data' => $users]);
    }
}