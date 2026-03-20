const admin = [
  {
    path: '/admin/login',
    name: 'admin-login',
    component: () => import('../pages/admin/auth/Login.vue'),
  },
  {
    path: '/admin/register',
    name: 'admin-register',
    component: () => import('../pages/admin/auth/Register.vue'),
  },

  {
    path: '/admin',
    component: () => import('../layouts/AdminLayout.vue'),
    children: [
      {
        path: '',
        name: 'admin-dashboard',
        component: () => import('../pages/admin/index.vue'),
      },
      {
        path: '/admin/profile',
        name: 'admin-profile',
        component: () => import('../pages/admin/account/Profile.vue'),
      },
      {
        path: '/admin/forgot-password',
        name: 'admin-forgot-password',
        component: () => import('../pages/admin/auth/ForgotPassword.vue'),
      },
      {
        path: 'reset-password',
        name: 'admin-reset-password',
        component: () => import('../pages/admin/auth/ResetPassword.vue'),
      },
      {
        path: 'roles',
        name: 'admin-roles',
        component: () => import('../pages/admin/role/Index.vue'),
        meta: { moduleCode: 'admin_roles' },
      },
      // crud staff
      {
        path: 'staff',
        name: 'admin-staff-index',
        component: () => import('../pages/admin/account/staff/Index.vue'),
        meta: { moduleCode: 'admin_staff' },
      },
      {
        path: 'staff/create',
        name: 'admin-staff-create',
        component: () => import('../pages/admin/account/staff/Create.vue'),
        meta: { moduleCode: 'admin_staff' },
      },
      {
        path: 'staff/:id/edit',
        name: 'admin-staff-edit',
        component: () => import('../pages/admin/account/staff/Edit.vue'),
        meta: { moduleCode: 'admin_staff' },
      },
      {
        // ROUTE QUẢN LÝ KHÁCH HÀNG (USERS)
        path: 'users',
        name: 'admin-users',
        meta: { moduleCode: 'admin_users' },
        component: () => import('../pages/admin/account/user/Index.vue'),
      },
      {
        path: 'account/users/create',
        name: 'admin-user-create',
        meta: { moduleCode: 'admin_users' },
        component: () => import('../pages/admin/account/user/Create.vue'),
      },
      {
        path: 'account/users/edit/:id',
        name: 'admin-user-edit',
        meta: { moduleCode: 'admin_users' },
        component: () => import('../pages/admin/account/user/Edit.vue'),
      },
      // ROUTE QUẢN LÝ DANH MỤC (CATEGORIES)
      {
        path: 'categories',
        name: 'admin-categories',
        meta: { moduleCode: 'admin_categories' },
        component: () => import('../pages/admin/category/Index.vue'),
      },
      {
        path: 'categories/create',
        name: 'admin-category-create',
        meta: { moduleCode: 'admin_categories' },
        component: () => import('../pages/admin/category/Create.vue'),
      },
      {
        path: 'categories/edit/:id',
        name: 'admin-category-edit',
        meta: { moduleCode: 'admin_categories' },
        component: () => import('../pages/admin/category/Edit.vue'),
      },
      // ROUTE QUẢN LÝ SẢN PHẨM (PRODUCTS)
      {
        path: 'products',
        name: 'admin-products',
        component: () => import('../pages/admin/product/Index.vue'),
        meta: { moduleCode: 'admin_products' },
      },
      {
        path: 'products/create',
        name: 'admin-products-create',
        component: () => import('../pages/admin/product/Create.vue'),
        meta: { moduleCode: 'admin_products' },
      },
      {
        path: 'products/:id/edit',
        name: 'admin-products-edit',
        component: () => import('../pages/admin/product/Edit.vue'),
        meta: { moduleCode: 'admin_products' },
      },
      // ROUTE QUẢN LÝ THƯƠNG HIỆU (BRANDS)
      {
        path: 'brands',
        name: 'admin-brands',
        component: () => import('../pages/admin/brand/Index.vue'),
        meta: { moduleCode: 'admin_brands' },
      },
      {
        path: 'brands/create',
        name: 'admin-brands-create',
        component: () => import('../pages/admin/brand/Create.vue'),
        meta: { moduleCode: 'admin_brands' },
      },
      {
        path: 'brands/:id/edit',
        name: 'admin-brands-edit',
        component: () => import('../pages/admin/brand/Edit.vue'),
        meta: { moduleCode: 'admin_brands' },
      },
      
      // ROUTE QUẢN LÝ BANNER (BANNERS) - THÊM MỚI
      {
        path: 'banners',
        name: 'admin-banners',
        component: () => import('../pages/admin/banner/Index.vue'),
        meta: { moduleCode: 'admin_banners' },
      },
      {
        path: 'banners/create',
        name: 'admin-banners-create',
        component: () => import('../pages/admin/banner/Create.vue'),
        meta: { moduleCode: 'admin_banners' },
      },
      {
        path: 'banners/:id/edit',
        name: 'admin-banners-edit',
        component: () => import('../pages/admin/banner/Edit.vue'),
        meta: { moduleCode: 'admin_banners' },
      },

      // ROUTE QUẢN LÝ ĐƠN HÀNG (ORDERS)
      {
        path: 'orders',
        name: 'admin-orders',
        component: () => import('../pages/admin/order/Index.vue'),
        meta: { moduleCode: 'admin_orders' },
      },
      {
        path: 'orders/returns',
        name: 'admin-orders-returns',
        component: () => import('../pages/admin/order/Returns.vue'),
        meta: { moduleCode: 'admin_orders' },
      }


    ],
  },
]

export default admin
