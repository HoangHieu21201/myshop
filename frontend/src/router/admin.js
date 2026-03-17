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
      // ROUTE QUẢN LÝ DANH MỤC
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
        path: 'categories/:id/edit',
        name: 'admin-category-edit',
        component: () => import('../pages/admin/category/Edit.vue'),
        meta: { moduleCode: 'admin_categories' },
      },
      // ROUTE QUẢN LÝ PHÒNG
      {
        path: 'rooms',
        name: 'admin-rooms',
        component: () => import('@/pages/admin/room/Index.vue'),
        meta: { moduleCode: 'admin_rooms' },
      },
      {
        path: 'rooms/create',
        name: 'admin-rooms-create',
        component: () => import('@/pages/admin/room/Create.vue'),
        meta: { moduleCode: 'admin_rooms' },
      },
      {
        path: 'rooms/:id/edit',
        name: 'admin-rooms-edit',
        component: () => import('@/pages/admin/room/Edit.vue'),
        meta: { moduleCode: 'admin_rooms' },
      },
    ],
  },
]

export default admin
