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
        ]
    }
];

export default user;