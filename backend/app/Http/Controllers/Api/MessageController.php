<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /**
     * API: Nhận tin nhắn (text hoặc file) và Phát sóng
     */
    public function store(Request $request)
    {
        $isAdmin = $request->is('api/admin/*');
        $senderId = $isAdmin ? 1 : Auth::guard('sanctum')->id();

        if (!$senderId) {
            return response()->json(['status' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        // Nếu gửi file
        if ($request->hasFile('file')) {
            $request->validate([
                'file' => 'required|file|max:20480', // max 20MB
                'receiver_id' => 'nullable|integer',
            ]);

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileSize = $this->formatFileSize($file->getSize());
            $mimeType = $file->getMimeType();

            // Xác định loại message
            $messageType = str_starts_with($mimeType, 'image/') ? 'image' : 'file';

            // Lưu file vào storage/public/chat_files
            $path = $file->store('chat_files', 'public');
            $fileUrl = url('storage/' . $path);

            $message = Message::create([
                'sender_id'    => $senderId,
                'receiver_id'  => $request->receiver_id ?? 1,
                'content'      => $originalName, // Nội dung là tên file
                'message_type' => $messageType,
                'file_url'     => $fileUrl,
                'file_name'    => $originalName,
                'file_size'    => $fileSize,
                'is_read'      => false,
            ]);
        } else {
            // Gửi text/emoji
            $request->validate([
                'content'     => 'required|string|max:5000',
                'receiver_id' => 'nullable|integer',
            ]);

            $message = Message::create([
                'sender_id'    => $senderId,
                'receiver_id'  => $request->receiver_id ?? 1,
                'content'      => $request->content,
                'message_type' => 'text',
                'is_read'      => false,
            ]);
        }

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
        $partnerId = $request->query('partner_id');

        if ($partnerId) {
            $messages = Message::where(function($q) use ($userId, $partnerId) {
                $q->where('sender_id', $userId)->where('receiver_id', $partnerId);
            })->orWhere(function($q) use ($userId, $partnerId) {
                $q->where('sender_id', $partnerId)->where('receiver_id', $userId);
            })->orderBy('created_at', 'asc')->get();
        } else {
            $messages = Message::where('sender_id', $userId)
                               ->orWhere('receiver_id', $userId)
                               ->orderBy('created_at', 'asc')->get();
        }
                           
        return response()->json(['status' => true, 'data' => $messages]);
    }

    /**
     * API: Lấy danh sách những người đã nhắn tin (dành cho Admin)
     */
    public function getConversations()
    {
        $adminId = 1;

        $senderIds = Message::where('receiver_id', $adminId)->pluck('sender_id')->toArray();
        $receiverIds = Message::where('sender_id', $adminId)->pluck('receiver_id')->toArray();
        
        $userIds = array_unique(array_merge($senderIds, $receiverIds));
        $userIds = array_diff($userIds, [1]);

        $users = User::whereIn('id', $userIds)->select('id', 'fullName', 'email')->get();

        return response()->json(['status' => true, 'data' => $users]);
    }

    /**
     * API MỚI: Admin xóa toàn bộ cuộc trò chuyện với 1 user cụ thể
     */
    public function deleteConversation(Request $request, $userId)
    {
        $adminId = 1;

        // Lấy tất cả messages có file để xóa file khỏi storage
        $messagesWithFiles = Message::where(function($q) use ($adminId, $userId) {
            $q->where('sender_id', $adminId)->where('receiver_id', $userId);
        })->orWhere(function($q) use ($adminId, $userId) {
            $q->where('sender_id', $userId)->where('receiver_id', $adminId);
        })->whereNotNull('file_url')->get();

        // Xóa file khỏi storage
        foreach ($messagesWithFiles as $msg) {
            if ($msg->file_url) {
                // Lấy path từ URL
                $relativePath = str_replace(url('storage/'), '', $msg->file_url);
                Storage::disk('public')->delete($relativePath);
            }
        }

        // Xóa tất cả tin nhắn
        Message::where(function($q) use ($adminId, $userId) {
            $q->where('sender_id', $adminId)->where('receiver_id', $userId);
        })->orWhere(function($q) use ($adminId, $userId) {
            $q->where('sender_id', $userId)->where('receiver_id', $adminId);
        })->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Đã xóa toàn bộ cuộc trò chuyện thành công.'
        ]);
    }

    /**
     * Helper: Format kích thước file
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
}