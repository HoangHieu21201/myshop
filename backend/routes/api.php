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
use App\Http\Controllers\Api\admin\AdminOrderController;
use App\Http\Controllers\Api\admin\AdminBannerController;
use App\Http\Controllers\Api\admin\AdminMembershipTierController;

// Import User
use App\Http\Controllers\Api\client\CartController;
use App\Http\Controllers\Api\client\OrderController;
use App\Http\Controllers\Api\Client\ClientHeaderController;
use App\Http\Controllers\Api\client\ClientHomeController;
use App\Http\Controllers\Api\Auth\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::prefix('client')->group(function () {
    
    // Header & Tìm kiếm
    Route::get('header-data', [ClientHeaderController::class, 'getMegaMenuData']);
    Route::get('search', [ClientHeaderController::class, 'search']);

    // MODULE GIỎ HÀNG (Cart)
    // Thỏa mãn: Thêm, Xem, Tăng/Giảm, Xóa 1, Xóa tất cả, Hợp nhất
    Route::controller(CartController::class)->prefix('cart')->group(function () {
        Route::get('/', 'index');               // Xem giỏ hàng
        Route::post('/', 'store');              // Thêm vào giỏ
        Route::put('/{cartItem}', 'update');    // Cập nhật số lượng
        Route::delete('/{cartItem}', 'destroy');// Xóa 1 sản phẩm
        Route::post('/clear', 'clear');         // Xóa toàn bộ giỏ
        Route::post('/merge', 'mergeCart');     // Hợp nhất giỏ khi login
    });

    // MODULE ĐƠN HÀNG (Orders)
    // Thỏa mãn: Đặt hàng, Xem lịch sử, Chi tiết, Hủy đơn
    Route::controller(OrderController::class)->prefix('orders')->group(function () {
        Route::get('/', 'index');               // Danh sách đơn hàng (Lịch sử)
        Route::post('/', 'store');              // Đặt hàng (Checkout)
        Route::get('/{order_code}', 'show');    // Chi tiết đơn hàng
        Route::put('/{order_code}', 'update');  // Hủy đơn hàng
    });

    // Trang chủ
    Route::get('home-data', [ClientHomeController::class, 'index']);
});


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// ============================================
// ADMIN API ROUTES
// ============================================
Route::prefix('admin')->group(function () {

    // API Auth Admin (Không cần token)
    Route::controller(AdminAccountController::class)->group(function () {
        Route::post('login', 'login');
        Route::post('register', 'store');
    });

    Route::controller(AdminForgotPasswordController::class)->group(function () {
        Route::post('forgot-password', 'sendResetLinkEmail');
        Route::post('reset-password', 'resetPassword');
    });

    // CÁC API YÊU CẦU ĐĂNG NHẬP (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {

        // API lấy thông tin Admin hiện tại 
        Route::get('me', [AdminAccountController::class, 'me']);

        // Quản lý hồ sơ cá nhân
        Route::controller(AdminProfileController::class)->group(function () {
            Route::post('profile', 'updateProfile');
            Route::put('profile/password', 'updatePassword');
        });

        // --------------------------------------------------------
        // CÁC MODULE QUẢN TRỊ (Phân quyền theo module_code)
        // --------------------------------------------------------

        // Quản lý Nhân sự (Mã: admin_staff)
        Route::middleware(['check.module:admin_staff'])->group(function () {
            Route::apiResource('staff', AdminStaffController::class);
            Route::post('staff/{id}/restore', [AdminStaffController::class, 'restore']);
        });

        // Quản lý Khách hàng (Mã: admin_users)
        Route::middleware(['check.module:admin_users'])->group(function () {
            Route::apiResource('users', AdminUserController::class);
            Route::post('users/{id}/restore', [AdminUserController::class, 'restore']);

            // Địa chỉ Khách hàng
            Route::controller(AdminUserAddressController::class)->group(function () {
                Route::post('users/{id}/addresses', 'store');
                Route::put('addresses/{id}', 'update');
                Route::delete('addresses/{id}', 'destroy');
                Route::put('addresses/{id}/default', 'setDefault');
            });
        });

        // Quản lý Role & Cấp độ (Mã: admin_roles)
        Route::middleware(['check.module:admin_roles'])->group(function () {
            Route::apiResource('roles', AdminRoleController::class);
            Route::controller(AdminModulePermissionController::class)->group(function () {
                Route::get('modules', 'index');
                Route::post('modules/sync', 'sync');
                Route::put('modules/{id}/level', 'updateLevel');
            });
        });

        // Quản lý Danh mục (Mã: admin_categories)
        Route::middleware(['check.module:admin_categories'])->group(function () {
            Route::get('categories/tree', [AdminCategoryController::class, 'getTree']);
            Route::post('categories/{id}/restore', [AdminCategoryController::class, 'restore']);
            Route::post('categories/reorder', [AdminCategoryController::class, 'reorder']);
            Route::apiResource('categories', AdminCategoryController::class);
        });

        // Quản lý Sản phẩm (Mã: admin_products)
        Route::middleware(['check.module:admin_products'])->group(function () {
            Route::apiResource('products', AdminProductController::class);
            Route::post('products/{id}/restore', [AdminProductController::class, 'restore']);

            Route::apiResource('attributes', AdminAttributeController::class)->except(['show']);
            Route::post('attribute-values', [AdminAttributeValueController::class, 'store']);
        });

        // Quản lý Thương hiệu (Mã: admin_brands)
        Route::middleware(['check.module:admin_brands'])->group(function () {
            Route::apiResource('brands', AdminBrandController::class);
            Route::post('brands/{id}/restore', [AdminBrandController::class, 'restore']);
            Route::post('brands/reorder', [AdminBrandController::class, 'reorder']);
        });

        // Quản lý Banner (Mã: admin_banners)
        Route::middleware(['check.module:admin_banners'])->group(function () {
            Route::apiResource('banners', AdminBannerController::class);
            Route::post('banners/{id}/restore', [AdminBannerController::class, 'restore']);
            Route::post('banners/reorder', [AdminBannerController::class, 'reorder']);
        });

        // Quản lý Đơn hàng (Mã: admin_orders)
        Route::middleware(['check.module:admin_orders'])->group(function () {
            Route::controller(AdminOrderController::class)->group(function () {
                Route::get('orders', 'index');
                Route::get('orders/{id}', 'show');
                Route::put('orders/{id}/status', 'updateStatus');
                Route::delete('orders/{id}', 'destroy');
            });
        });

        // Quản lý Coupon (Mã: admin_coupons)
        Route::middleware(['check.module:admin_coupons'])->group(function () {
            Route::controller(AdminCouponController::class)->group(function () {
                Route::get('coupons', 'index');
                Route::get('coupons/{id}', 'show');
                Route::post('coupons', 'store');
                Route::patch('coupons/{id}', 'update');
                Route::delete('coupons/{id}', 'destroy');
            });
        });

        // Quản lý Hạng thành viên (Mã: admin_roles)
        Route::middleware(['check.module:admin_roles'])->group(function () {
            Route::controller(AdminMembershipTierController::class)->prefix('tiers')->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::get('/{id}', 'show');
                Route::post('/{id}', 'update');
                Route::delete('/{id}', 'destroy');
            });
        });
    });
});
