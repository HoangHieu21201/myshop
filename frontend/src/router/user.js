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
        ]
    }
];

export default user;