<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;

class AdminRoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('level', 'asc')->get();
        return response()->json(['success' => true, 'data' => $roles]);
    }

    // Tạo mới Role
    public function store(StoreRoleRequest $request)
    {
        try {
            $role = Role::create($request->only(['value', 'label', 'badgeClass', 'level']));
            return response()->json(['success' => true, 'message' => 'Tạo Role thành công', 'data' => $role], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    // Cập nhật Role
    public function update(UpdateRoleRequest $request, $id)
    {
        $role = Role::findOrFail($id);
        
        if ($role->id == 1 && $request->level != 1) {
            return response()->json(['success' => false, 'message' => 'Không thể hạ cấp quyền của Super Admin gốc!'], 403);
        }

        try {
            $role->update($request->only(['value', 'label', 'badgeClass', 'level']));
            return response()->json(['success' => true, 'message' => 'Cập nhật Role thành công', 'data' => $role]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        
        if ($role->id == 1) {
            return response()->json(['success' => false, 'message' => 'Tuyệt đối không thể xóa quyền Super Admin!'], 403);
        }

        $role->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa Role thành công']);
    }
}