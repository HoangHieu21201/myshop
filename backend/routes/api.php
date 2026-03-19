<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ============================================
// IMPORT ADMIN CONTROLLERS
// ============================================
use App\Http\Controllers\Api\admin\AdminCouponController;
use App\Http\Controllers\Api\admin\AdminAccountController;
use App\Http\Controllers\Api\admin\AdminForgotPasswordController;
use App\Http\Controllers\Api\admin\AdminProfileController;
use App\Http\Controllers\Api\admin\AdminStaffController;
use App\Http\Controllers\Api\admin\AdminUserController;
use App\Http\Controllers\Api\admin\AdminUserAddressController;
use App\Http\Controllers\Api\admin\AdminRoleController;
use App\Http\Controllers\Api\admin\AdminModulePermissionController;
use App\Http\Controllers\Api\admin\AdminCategoryController;
use App\Http\Controllers\Api\admin\AdminProductController;
use App\Http\Controllers\Api\admin\AdminAttributeController;
use App\Http\Controllers\Api\admin\AdminAttributeValueController;
use App\Http\Controllers\Api\admin\AdminBrandController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ============================================
// ADMIN API ROUTES
// ============================================
Route::prefix('admin')->group(function () {

    // API Auth Admin (Không cần token)
    Route::post('/login', [AdminAccountController::class, 'login']);
    Route::post('/register', [AdminAccountController::class, 'store']);
    Route::post('/forgot-password', [AdminForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('/reset-password', [AdminForgotPasswordController::class, 'resetPassword']);

    // Các API yêu cầu Admin phải đăng nhập
    Route::middleware('auth:sanctum')->group(function () {

        // API lấy thông tin Admin hiện tại 
        Route::get('/me', [AdminAccountController::class, 'me']);

        // Quản lý hồ sơ cá nhân
        Route::post('/profile', [AdminProfileController::class, 'updateProfile']);
        Route::put('/profile/password', [AdminProfileController::class, 'updatePassword']);

        // Quản lý Nhân sự / Tài khoản Nội bộ (Mã module: admin_staff)
        Route::middleware(['check.module:admin_staff'])->group(function () {
            Route::apiResource('staff', AdminStaffController::class);
            Route::post('staff/{id}/restore', [AdminStaffController::class, 'restore']);
        });

        // Nhóm bảo mật cho "Quản lý Tài khoản (User)" (Mã: admin_users)
        Route::middleware(['check.module:admin_users'])->group(function () {
            Route::apiResource('users', AdminUserController::class);
            Route::post('users/{id}/restore', [AdminUserController::class, 'restore']);

            // ROUTES CHO ĐỊA CHỈ KHÁCH HÀNG
            Route::post('users/{id}/addresses', [AdminUserAddressController::class, 'store']);
            Route::put('addresses/{id}', [AdminUserAddressController::class, 'update']);
            Route::delete('addresses/{id}', [AdminUserAddressController::class, 'destroy']);
            Route::put('addresses/{id}/default', [AdminUserAddressController::class, 'setDefault']);
        });

        // Nhóm bảo mật cho "Quản lý Role & Cấp độ" (Mã: admin_roles)
        Route::middleware(['check.module:admin_roles'])->group(function () {
            Route::apiResource('roles', AdminRoleController::class);
            Route::get('modules', [AdminModulePermissionController::class, 'index']);
            Route::post('modules/sync', [AdminModulePermissionController::class, 'sync']);
            Route::put('modules/{id}/level', [AdminModulePermissionController::class, 'updateLevel']);
        });

        // Nhóm bảo mật cho "Quản lý Danh mục sản phẩm" (Mã: admin_categories)
        Route::middleware(['check.module:admin_categories'])->group(function () {
            Route::get('categories/tree', [AdminCategoryController::class, 'getTree']);
            Route::apiResource('categories', AdminCategoryController::class);
            Route::post('categories/{id}/restore', [AdminCategoryController::class, 'restore']);
            Route::post('categories/reorder', [AdminCategoryController::class, 'reorder']);
        });

        // Nhóm bảo mật cho "Quản lý Sản phẩm" (Mã: admin_products)
        Route::middleware(['check.module:admin_products'])->group(function () {
            Route::apiResource('products', AdminProductController::class);
            Route::post('products/{id}/restore', [AdminProductController::class, 'restore']);
            Route::apiResource('products', AdminProductController::class);
            Route::apiResource('attributes', AdminAttributeController::class)->except(['show']);
            Route::post('attribute-values', [AdminAttributeValueController::class, 'store']);
        });

        // Nhóm bảo mật cho "Quản lý Thương hiệu" (Mã: admin_brands)
        Route::middleware(['check.module:admin_brands'])->group(function () {
            Route::apiResource('brands', AdminBrandController::class);
            Route::post('brands/{id}/restore', [AdminBrandController::class, 'restore']);
            Route::post('brands/reorder', [AdminBrandController::class, 'reorder']);
        });

        // Nhóm bảo mật cho "Quản lý Coupon" (Mã: admin_coupons)
        Route::middleware(['check.module:admin_coupons'])->group(function () {
            Route::get('/coupons', [AdminCouponController::class, 'index']);
            Route::get('/coupon/{id}', [AdminCouponController::class, 'show']);
            Route::post('/coupon', [AdminCouponController::class, 'store']);
            Route::patch('/coupon/{id}', [AdminCouponController::class, 'update']);
            Route::delete('/coupon/{id}', [AdminCouponController::class, 'destroy']);
        });
    });
});
