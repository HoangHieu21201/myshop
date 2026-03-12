<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateAdminProfileRequest;
use App\Http\Requests\UpdateAdminPasswordRequest;

class AdminProfileController extends Controller
{
    public function updateProfile(UpdateAdminProfileRequest $request)
    {
        $admin = $request->user(); 
        // Cập nhật thông tin chữ
        $admin->fullname = $request->fullname;
        $admin->phone = $request->phone;
        $admin->address = $request->address;

        if ($request->has('remove_avatar') && $request->remove_avatar == 'true') {
            if ($admin->avatar_url) {
                Storage::disk('public')->delete($admin->avatar_url); 
                $admin->avatar_url = null;
            }
        }
        if ($request->hasFile('avatar')) {
            if ($admin->avatar_url) {
                Storage::disk('public')->delete($admin->avatar_url);
            }

            $file = $request->file('avatar');
            $filename = 'avatar_admin_' . $admin->id . '.' . $file->getClientOriginalExtension();
            
            $path = $file->storeAs('avatars/admin', $filename, 'public');
            
            $admin->avatar_url = $path;
        }

        $admin->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật hồ sơ thành công',
            'data'    => $admin
        ]);
    }

    public function updatePassword(UpdateAdminPasswordRequest $request)
    {
        $admin = $request->user();

        $admin->password = Hash::make($request->new_password);
        $admin->save();

        $admin->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mật khẩu đã được thay đổi thành công'
        ]);
    }
}