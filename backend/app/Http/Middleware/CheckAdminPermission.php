<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminPermission
{
    public function handle(Request $request, Closure $next, $permissionId): Response
    {
        $admin = $request->user();

        // Kiểm tra xem role của admin này có chứa permission_id yêu cầu không
        $hasPermission = DB::table('role_permissions')
            ->where('role_id', $admin->role_id)
            ->where('permission_id', $permissionId)
            ->exists();

        // Nếu không có quyền VÀ không phải Super Admin (giả sử role_id = 1 luôn là Super Admin tối cao nhất)
        if (!$hasPermission && $admin->role_id != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập chức năng này (Yêu cầu quyền cấp ' . $permissionId . ').'
            ], 403);
        }

        return $next($request);
    }
}