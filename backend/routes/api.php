<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers Admin
use App\Http\Controllers\Api\Client\ShopController;
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
use App\Http\Controllers\Api\admin\AdminComboController;

// Controllers Client
use App\Http\Controllers\Api\client\ProductDetailController;
use App\Http\Controllers\Api\client\ClientCartController;
use App\Http\Controllers\Api\client\ClientOrderController;
use App\Http\Controllers\Api\Client\ClientHeaderController;
use App\Http\Controllers\Api\client\ClientHomeController;
use App\Http\Controllers\Api\client\ClientCompareController; // Đã thêm Use Controller
use App\Http\Controllers\Api\Auth\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// CLIENT API ROUTES
Route::prefix('client')->group(function () {

    Route::get('header-data', [ClientHeaderController::class, 'getMegaMenuData']);
    Route::get('search', [ClientHeaderController::class, 'search']);
    Route::get('/home-data', [ClientHomeController::class, 'index']);

    // MODULE GIỎ HÀNG (Cart)
    Route::controller(ClientCartController::class)->prefix('cart')->group(function () {
        Route::post('/add-combo', 'addCombo'); 
        Route::post('/merge', 'mergeCart');     
        Route::post('/clear', 'clear');         

        Route::get('/', 'index');               
        Route::post('/', 'store');              

        // CÁC ROUTE ĐỘNG ĐẶT Ở DƯỚI CÙNG
        Route::put('/{cartItem}', 'update');    
        Route::delete('/{cartItem}', 'destroy'); 
    });

    // MODULE ĐƠN HÀNG (Orders)
    Route::controller(ClientOrderController::class)->prefix('orders')->group(function () {
        Route::get('/', 'index');               
        Route::post('/', 'store');              
        Route::get('/{order_code}', 'show');    
        Route::put('/{order_code}', 'update');  
    });

    Route::controller(\App\Http\Controllers\Api\Client\ClientComboController::class)->prefix('combos')->group(function () {
        Route::get('/', 'index');
        Route::get('/{slug}', 'show');
    });

    // Trang chủ
    Route::get('/home-data', [ClientHomeController::class, 'index']);
});

// ROUTE SHOP CLIENT
Route::prefix('shop/{shop_slug}')->group(function () {
    Route::get('/info', [ShopController::class, 'shopInfo']); 
    Route::get('/products', [ShopController::class, 'index']);
    Route::get('/products/featured', [ShopController::class, 'featured']);
    
    Route::get('/products/{slug}', [ProductDetailController::class, 'show']);
    
    // Đã chuyển Route Compare vào đúng Group
    Route::post('/compare', [ClientCompareController::class, 'getCompareData']);
});
Route::get('shop/{shop_slug}/categories', [App\Http\Controllers\Api\Client\ShopController::class, 'categories']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ADMIN API ROUTES
Route::prefix('admin')->group(function () {

    Route::controller(AdminAccountController::class)->group(function () {
        Route::post('login', 'login');
        Route::post('register', 'store');
    });

    Route::controller(AdminForgotPasswordController::class)->group(function () {
        Route::post('forgot-password', 'sendResetLinkEmail');
        Route::post('reset-password', 'resetPassword');
    });

    Route::middleware('auth:sanctum')->group(function () {

        // Lấy thông tin admin hiện tại
        Route::get('me', [AdminAccountController::class, 'me']);
    
        Route::controller(AdminProfileController::class)->group(function () {
            Route::post('profile', 'updateProfile');
            Route::put('profile/password', 'updatePassword');
        });

        // Quản lý Nhân viên (Mã: admin_staff)
        Route::middleware(['check.module:admin_staff'])->group(function () {
            Route::apiResource('staff', AdminStaffController::class);
            Route::post('staff/{id}/restore', [AdminStaffController::class, 'restore']);
        });

        // Quản lý Người dùng (Mã: admin_users)
        Route::middleware(['check.module:admin_users'])->group(function () {
            Route::apiResource('users', AdminUserController::class);
            Route::post('users/{id}/restore', [AdminUserController::class, 'restore']);
            
            Route::controller(AdminUserAddressController::class)->group(function () {
                Route::post('users/{id}/addresses', 'store');
                Route::put('addresses/{id}', 'update');
                Route::delete('addresses/{id}', 'destroy');
                Route::put('addresses/{id}/default', 'setDefault');
            });
        });

        // Quản lý Vai trò & Phân quyền (Mã: admin_roles)
        Route::middleware(['check.module:admin_roles'])->group(function () {
            Route::apiResource('roles', AdminRoleController::class);
            Route::post('roles/{id}/restore', [AdminRoleController::class, 'restore']);
            
            // Quản lý phân quyền module trong vai trò
            Route::controller(AdminModulePermissionController::class)->group(function () {
                Route::get('modules', 'index');
                Route::post('modules/sync', 'sync');
                Route::put('modules/{id}/level', 'updateLevel');
            });

            // Quản lý cấp độ thành viên (Mã: admin_membership_tiers)
            Route::controller(AdminMembershipTierController::class)->prefix('tiers')->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::get('/{id}', 'show');
                Route::post('/{id}', 'update');
                Route::delete('/{id}', 'destroy');
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

        // Quản lý Banners (Mã: admin_banners)
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

        // Quản lý Mã giảm giá (Mã: admin_coupons)
        Route::middleware(['check.module:admin_coupons'])->group(function () {
            Route::controller(AdminCouponController::class)->group(function () {
                Route::get('coupons', 'index');
                Route::get('coupons/{id}', 'show');
                Route::post('coupons', 'store');
                Route::patch('coupons/{id}', 'update');
                Route::delete('coupons/{id}', 'destroy');
            });
        });

         // Quản lý Combo (Mã: admin_combos)
        Route::middleware(['check.module:admin_combos'])->group(function () {
            Route::controller(AdminComboController::class)->prefix('combos')->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::get('/{combo}', 'show');
                Route::put('/{combo}', 'update');
                Route::patch('/{combo}/status', 'updateStatus');
                Route::delete('/{combo}', 'destroy');
                Route::post('/{combo}/restore', 'restore');
            });
        });
    });
});