const user = [
    {
        path: '/',
        component: () => import('../layouts/UserLayout.vue'),
        children: [
            {
                path: '',
                name: 'home',
                component: () => import('../pages/user/index.vue')
            },
            {
                path: 'cart',
                name: 'cart',
                component: () => import('../pages/user/cart/index.vue')
            },
            {
                path: 'order',
                name: 'order',
                component: () => import('../pages/user/order/Index.vue')
            },
            {
                path: 'orderHistory',
                name: 'orderHistory',
                component: () => import('../pages/user/order/OrderDetailModal.vue')
            },
            {
                path: 'login',
                name: 'login',
                component: () => import('../pages/user/auth/Login.vue')
            },
            {
                path: 'register',
                name: 'register',
                component: () => import('../pages/user/auth/Register.vue')
            },
        ]
    }
];

export default user;