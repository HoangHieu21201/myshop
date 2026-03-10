const UserLayoutRoutes = {
  path: "/",
  component: () => import("../layouts/UserLayout.vue"),
  children: [
  

  ],
};

const users = [UserLayoutRoutes];

export default users;