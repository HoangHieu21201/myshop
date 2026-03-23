<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminStoreAdminRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAccountController extends Controller
{
    public function store(AdminStoreAdminRequest $request)
    {
        // 1. Lấy dữ liệu đã validate
        $validatedData = $request->validated();

        // 2. Băm mật khẩu
        $validatedData['password'] = Hash::make($validatedData['password']);
        $validatedData['email_verified_at'] = now();

        // Gán role mặc định (Bạn có thể cân nhắc lấy từ request thay vì hardcode 12)
        $validatedData['role_id'] = 12;
        $validatedData['status'] = 'active';

        $admin = Admin::create($validatedData);

        // [FIX: Eager Load] Nạp sẵn role cho admin mới tạo để Frontend có ngay data chuẩn
        $admin->load('role');

        return response()->json([
            'success' => true,
            'message' => 'Tạo tài khoản quản trị thành công',
            'data'    => $admin
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // [FIX] Eager-load role ngay từ bước đăng nhập
        $admin = Admin::with('role')->where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json(['success' => false, 'message' => 'Email hoặc mật khẩu không chính xác'], 401);
        }

        if ($admin->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Tài khoản của bạn đã bị khóa'], 403);
        }

        $abilities = $admin->role ? ['level:' . $admin->role->level] : ['level:5'];
        $token = $admin->createToken('admin_token', $abilities)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'token'   => $token,
            'admin'   => $admin 
        ]);
    }

    public function me(Request $request)
    {
        $admin = $request->user()->load('role');
        return response()->json([
            'success' => true,
            'data'    => $admin
        ]);
    }
}