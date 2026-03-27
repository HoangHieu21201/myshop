const user = [
  {
    path: '/',
    component: () => import('../layouts/UserLayout.vue'),
    children: [
      {
        path: '',
        name: 'home',
        component: () => import('../pages/user/index.vue'),
      },
      
      {
        path: 'cart',
        name: 'cart',
        component: () => import('../pages/user/cart/index.vue'),
      },
      {
        path: 'order',
        name: 'order',
        component: () => import('../pages/user/order/Index.vue'),
      },
      {
        path: 'orderHistory',
        name: 'orderHistory',
        component: () => import('../pages/user/order/OrderDetailModal.vue'),
      },
      {
        path: 'login',
        name: 'login',
        component: () => import('../pages/user/auth/Login.vue'),
      },
      {
        path: 'register',
        name: 'register',
        component: () => import('../pages/user/auth/Register.vue'),
      },
      {
        path: 'shop',
        name: 'shop',
        component: () => import('../pages/user/shop/Index.vue'),
      },
      // Đã sửa 'product' thành 'productdetail' để khớp với router.push bên file Compare.vue
      {
        path: 'shop/:shop_slug/productdetail/:slug',
        name: 'productDetail',
        component: () => import('../pages/user/productdetail/Index.vue'),
      },
      {
        path: 'shop/:shop_slug/compare',
        name: 'compare',
        component: () => import('../pages/user/compare/Index.vue'), 
      },
      {
        path: 'combos',
        name: 'client-combos',
        component: () => import('../pages/user/combo/Index.vue'),
      },
      {
        path: 'combos/:slug',
        name: 'client-combo-detail',
        component: () => import('../pages/user/combo/Detail.vue'),
      },
      {
        path: 'favourite',
        name: 'favourite',
        component: () => import('../pages/user/favourite/index.vue')
      },
      {
        path: 'about',
        name: 'about',
        component: () => import('../pages/user/about/index.vue')
      },
      {
        path: 'contact',
        name: 'contact',
        component: () => import('../pages/user/contact/index.vue')
      },
      {
        path: 'services',
        name: 'services',
        component: () => import('../pages/user/services/index.vue')
      },
    ],
  },
]

export default user