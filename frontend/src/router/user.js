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
            
        ]
    }
];

export default user;