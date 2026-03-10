const adminLayoutRoutes = [
  {
    path: "/admin",
    component: () => import("../layouts/AdminLayout.vue"),
    meta: { requiresAuthAdmin: true },
    
  },
];

const allAdminRoutes = [...adminLayoutRoutes, ...adminAuthRoutes];

export default allAdminRoutes;
