<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class CheckModulePermission
{
    /**
     * So sánh Level của Admin với Required_Level của Trang
     */
    public function handle(Request $request, Closure $next, $moduleCode): Response
    {
        $admin = $request->user();

        // 1. Nếu chưa có role_id thì từ chối luôn
        if (!$admin || !$admin->role_id) {
            return response()->json(['success' => false, 'message' => 'Lỗi xác thực: Không tìm thấy chức vụ của bạn.'], 401);
        }

        // 2. Lấy thông tin Role của admin hiện tại để biết Admin đang ở Cấp độ mấy
        $role = DB::table('roles')->where('id', $admin->role_id)->first();
        if (!$role) {
            return response()->json(['success' => false, 'message' => 'Lỗi xác thực: Chức vụ của bạn không hợp lệ.'], 401);
        }
        $adminLevel = (int) $role->level;

        // 3. Super Admin (Level = 1) luôn luôn được phép truy cập tất cả
        if ($adminLevel === 1) {
            return $next($request);
        }

        // 4. Tìm kiếm trang (Module) này trong Database xem nó yêu cầu cấp mấy
        $module = DB::table('module_permissions')->where('module_code', $moduleCode)->first();

        // Nếu trang này chưa được cấu hình (chưa Sync), từ chối để bảo mật
        if (!$module) {
            return response()->json(['success' => false, 'message' => "Chức năng [$moduleCode] chưa được hệ thống cấp phép. Vui lòng báo Super Admin đồng bộ hệ thống."], 403);
        }

        $requiredLevel = (int) $module->required_level;

        // 5. Công thức vàng: Số cấp của Admin phải NHỎ HƠN HOẶC BẰNG số cấp yêu cầu (1 là to nhất)
        if ($adminLevel > $requiredLevel) {
            return response()->json(['success' => false, 'message' => 'Truy cập bị từ chối: Cấp độ của bạn không đủ để thực hiện thao tác này.'], 403);
        }

        return $next($request);
    }
}