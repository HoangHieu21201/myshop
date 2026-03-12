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

      //   {
      //     path: 'products',
      //     name: 'admin-products',
      //     component: { template: '<div class="p-4">Trang Sản phẩm (Đang xây dựng)</div>' },
      //   },
      //   {
      //     path: 'categories',
      //     name: 'admin-categories',
      //     component: { template: '<div class="p-4">Trang Danh mục (Đang xây dựng)</div>' },
      //   },
      //   {
      //     path: 'orders',
      //     name: 'admin-orders',
      //     component: { template: '<div class="p-4">Trang Đơn hàng (Đang xây dựng)</div>' },
      //   },
      //   {
      //     path: 'users',
      //     name: 'admin-users',
      //     component: { template: '<div class="p-4">Trang Khách hàng (Đang xây dựng)</div>' },
      //   },
      //   {
      //     path: 'staff',
      //     name: 'admin-staff',
      //     component: { template: '<div class="p-4">Trang Nhân viên (Đang xây dựng)</div>' },
      //   },
    ],
  },
]

export default admin
