<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class AdminContactController extends Controller
{
    /**
     * Lấy danh sách liên hệ (Ưu tiên những tin nhắn "Chờ xử lý" lên đầu)
     */
    public function index()
    {
        $contacts = Contact::orderByRaw("FIELD(status, 'pending', 'resolved')")
            ->orderBy('created_at', 'desc')
            ->paginate(15); // Phân trang 15 tin nhắn / trang

        return response()->json([
            'status' => true,
            'data' => $contacts
        ]);
    }

    /**
     * Cập nhật trạng thái tin nhắn (Từ pending -> resolved)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,resolved'
        ]);

        $contact = Contact::find($id);
        if (!$contact) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy liên hệ'], 404);
        }

        $contact->status = $request->status;
        $contact->save();

        return response()->json([
            'status' => true,
            'message' => 'Đã cập nhật trạng thái thành công!'
        ]);
    }

    /**
     * Xóa tin nhắn (Nếu là spam/rác)
     */
    public function destroy($id)
    {
        $contact = Contact::find($id);
        if (!$contact) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy liên hệ'], 404);
        }

        $contact->delete();

        return response()->json([
            'status' => true,
            'message' => 'Đã xóa tin nhắn liên hệ!'
        ]);
    }
}