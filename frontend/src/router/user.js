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
                component: () => import('../pages/user/cart/Index.vue')
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
        ]
    }
];

export default user;