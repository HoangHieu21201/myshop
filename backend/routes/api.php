<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// ============================================
// ADMIN API ROUTES
// ============================================
Route::prefix('admin')->group(function () {

    // API Auth Admin (Không cần token)
    Route::post('/login', [\App\Http\Controllers\Api\admin\AdminAccountController::class, 'login']);
    Route::post('/register', [\App\Http\Controllers\Api\admin\AdminAccountController::class, 'store']);
    Route::post('/forgot-password', [\App\Http\Controllers\Api\admin\AdminForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('/reset-password', [\App\Http\Controllers\Api\admin\AdminForgotPasswordController::class, 'resetPassword']);

    // Các API yêu cầu Admin phải đăng nhập
    Route::middleware('auth:sanctum')->group(function () {

        //  API lấy thông tin Admin hiện tại 
        Route::get('/me', [\App\Http\Controllers\Api\admin\AdminAccountController::class, 'me']);

        // Quản lý hồ sơ cá nhân
        Route::post('/profile', [\App\Http\Controllers\Api\admin\AdminProfileController::class, 'updateProfile']);
        Route::put('/profile/password', [\App\Http\Controllers\Api\admin\AdminProfileController::class, 'updatePassword']);

        // Quản lý Nhân sự / Tài khoản Nội bộ (Mã module: admin_staff)
        Route::middleware(['check.module:admin_staff'])->group(function () {
            Route::apiResource('staff', \App\Http\Controllers\Api\admin\AdminStaffController::class);

            Route::post('staff/{id}/restore', [\App\Http\Controllers\Api\admin\AdminStaffController::class, 'restore']);
        });

        // Nhóm bảo mật cho "Quản lý Tài khoản (User)" (Mã: admin_users)
        Route::middleware(['check.module:admin_users'])->group(function () {
            Route::apiResource('users', \App\Http\Controllers\Api\admin\AdminUserController::class);
            Route::post('users/{id}/restore', [\App\Http\Controllers\Api\admin\AdminUserController::class, 'restore']);

            // ROUTES CHO ĐỊA CHỈ KHÁCH HÀNG
            Route::post('users/{id}/addresses', [\App\Http\Controllers\Api\admin\AdminUserAddressController::class, 'store']);
            Route::put('addresses/{id}', [\App\Http\Controllers\Api\admin\AdminUserAddressController::class, 'update']);
            Route::delete('addresses/{id}', [\App\Http\Controllers\Api\admin\AdminUserAddressController::class, 'destroy']);
            Route::put('addresses/{id}/default', [\App\Http\Controllers\Api\admin\AdminUserAddressController::class, 'setDefault']);
        });

        // Nhóm bảo mật cho "Quản lý Role & Cấp độ" (Mã: admin_roles)
        Route::middleware(['check.module:admin_roles'])->group(function () {
            Route::apiResource('roles', \App\Http\Controllers\Api\admin\AdminRoleController::class);

            Route::get('modules', [\App\Http\Controllers\Api\admin\AdminModulePermissionController::class, 'index']);
            Route::post('modules/sync', [\App\Http\Controllers\Api\admin\AdminModulePermissionController::class, 'sync']);
            Route::put('modules/{id}/level', [\App\Http\Controllers\Api\admin\AdminModulePermissionController::class, 'updateLevel']);
        });

        // Nhóm bảo mật cho "Quản lý Danh mục sản phẩm" (Mã: admin_categories)
        Route::middleware(['check.module:admin_categories'])->group(function () {
            Route::get('categories/tree', [\App\Http\Controllers\Api\admin\AdminCategoryController::class, 'getTree']);

            Route::apiResource('categories', \App\Http\Controllers\Api\admin\AdminCategoryController::class);
            Route::post('categories/{id}/restore', [\App\Http\Controllers\Api\admin\AdminCategoryController::class, 'restore']);

            Route::post('categories/reorder', [\App\Http\Controllers\Api\admin\AdminCategoryController::class, 'reorder']);
        });


        // Nhóm bảo mật cho "Quản lý Sản phẩm" (Mã: admin_products)
        Route::middleware(['check.module:admin_products'])->group(function () {
            Route::apiResource('products', \App\Http\Controllers\Api\admin\AdminProductController::class);

            Route::post('products/{id}/restore', [\App\Http\Controllers\Api\admin\AdminProductController::class, 'restore']);

            Route::apiResource('products', \App\Http\Controllers\Api\admin\AdminProductController::class);
            Route::apiResource('attributes', \App\Http\Controllers\Api\admin\AdminAttributeController::class)->except(['show']);
            Route::post('attribute-values', [\App\Http\Controllers\Api\admin\AdminAttributeValueController::class, 'store']);
        });
    });
});
