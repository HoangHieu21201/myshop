<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminAccountController extends Controller
{
    public function store(StoreAdminRequest $request)
    {
        // 1. Lấy dữ liệu đã validate
        $validatedData = $request->validated();

        // 2. Băm mật khẩu
        $validatedData['password'] = Hash::make($validatedData['password']);

        $validatedData['email_verified_at'] = now();

        $validatedData['role_id'] = 12;
        $validatedData['status'] = 'active';

        $admin = Admin::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Tạo tài khoản quản trị thành công',
            'data'    => $admin
        ], 201);
    }

    public function login(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $admin = \App\Models\Admin::with('role')->where('email', $request->email)->first();

        if (!$admin || !\Illuminate\Support\Facades\Hash::check($request->password, $admin->password)) {
            return response()->json(['message' => 'Email hoặc mật khẩu không chính xác'], 401);
        }

        if ($admin->status !== 'active') {
            return response()->json(['message' => 'Tài khoản của bạn đã bị khóa'], 403);
        }

        // Tạo token Sanctum
        $token = $admin->createToken('admin_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'token' => $token,
            'admin' => $admin
        ]);
    }
}
