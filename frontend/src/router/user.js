const userLayoutRoutes = {
  path: "/",
  component: () => import("../layouts/UserLayout.vue"),
  children: [
  

  ],
};

const users = [...userAuthRoutes, userLayoutRoutes];

export default users;