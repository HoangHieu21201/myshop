<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::withTrashed()->with(['defaultAddress', 'addresses'])->orderBy('id', 'desc')->get();
        return response()->json(['success' => true, 'data' => $users]);
    }

    // Thêm mới
    public function store(StoreUserRequest $request)
    {
        $data = $request->except(['password', 'avatar', 'shipping_address', 'city', 'district', 'ward']);
        $data['password'] = Hash::make($request->password);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars/users', 'public');
            $data['avatar_url'] = $path;
        }

        // 1. Tạo User
        $user = User::create($data);

        // 2. Tạo địa chỉ mặc định (nếu có nhập)
        if ($request->filled('shipping_address')) {
            $user->addresses()->create([
                'customer_name'    => $user->fullName,
                'customer_phone'   => $user->phone,
                'shipping_address' => $request->shipping_address,
                'city'             => $request->city,
                'district'         => $request->district,
                'ward'             => $request->ward,
                'is_default'       => 1
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Tạo tài khoản khách hàng thành công!', 'data' => $user], 201);
    }

    // Lấy chi tiết (Load luôn cả địa chỉ mặc định và danh sách các địa chỉ khác)
    public function show($id)
    {
        $user = User::withTrashed()->with(['defaultAddress', 'addresses'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $user]);
    }

    // Cập nhật
    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->except(['password', 'avatar', '_method', 'remove_avatar', 'email', 'shipping_address', 'city', 'district', 'ward']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
                Storage::disk('public')->delete($user->avatar_url);
            }
            $path = $request->file('avatar')->store('avatars/users', 'public');
            $data['avatar_url'] = $path;
        } elseif ($request->input('remove_avatar') == 'true') {
            if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
                Storage::disk('public')->delete($user->avatar_url);
            }
            $data['avatar_url'] = null;
        }

        // 1. Cập nhật thông tin User
        $user->update($data);

        // 2. Cập nhật hoặc tạo mới địa chỉ mặc định cho User này
        if ($request->filled('shipping_address')) {
            $user->addresses()->updateOrCreate(
                ['is_default' => 1], 
                [
                    'customer_name'    => $user->fullName,
                    'customer_phone'   => $user->phone,
                    'shipping_address' => $request->shipping_address,
                    'city'             => $request->city,
                    'district'         => $request->district,
                    'ward'             => $request->ward,
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Cập nhật thông tin thành công!']);
    }

    // Xóa (Soft Delete)
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete(); 
        
        return response()->json(['success' => true, 'message' => 'Đã chuyển khách hàng vào thùng rác!']);
    }

    // Khôi phục tài khoản từ Thùng rác (Restore)
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        
        return response()->json(['success' => true, 'message' => 'Đã khôi phục tài khoản thành công!']);
    }
}