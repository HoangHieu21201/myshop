<?php

namespace App\Http\Controllers\Api\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ClientProfileController extends Controller
{
    /**
     * Lấy thông tin user
     */
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = Auth::guard('sanctum')->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        
        $userData = $user->toArray();
        if ($user->avatar_url && !str_starts_with($user->avatar_url, 'http')) {
            $userData['avatar_url'] = url('storage/' . $user->avatar_url);
        }

        return response()->json(['status' => true, 'data' => $userData]);
    }

    /**
     * Cập nhật thông tin & Avatar
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::guard('sanctum')->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Vui lòng đăng nhập'], 401);

        $request->validate([
            'fullName' => 'required|string|max:150',
            'phone' => 'nullable|string|max:20',
            'birthday' => 'nullable|date',
            'gender' => 'nullable|in:Nam,Nữ,Khác',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
        ]);

        $dataUpdate = [
            'fullName' => $request->fullName,
            'phone' => $request->phone,
            'birthday' => $request->birthday,
            'gender' => $request->gender,
        ];

        if ($request->hasFile('avatar')) {
            if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
                Storage::disk('public')->delete($user->avatar_url);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $dataUpdate['avatar_url'] = $path;
        }

        $user->update($dataUpdate);

        $userData = $user->fresh()->toArray();
        if ($user->avatar_url && !str_starts_with($user->avatar_url, 'http')) {
            $userData['avatar_url'] = url('storage/' . $user->avatar_url);
        }

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật hồ sơ thành công!',
            'data' => $userData
        ]);
    }

    /**
     * Đổi mật khẩu
     */
    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::guard('sanctum')->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Vui lòng đăng nhập'], 401);

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed', 
        ]);

        // ĐÃ FIX LỖI 500: Bắt trường hợp password trong database bị rỗng (NULL)
        if (empty($user->password) || !Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Mật khẩu hiện tại không chính xác (Hoặc tài khoản chưa cài mật khẩu)!'
            ], 400);
        }

        // Lưu mật khẩu mới
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Đổi mật khẩu thành công!'
        ]);
    }
}