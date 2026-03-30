import { createRouter, createWebHistory } from 'vue-router';
import user from './user'; 
import admin from './admin'; 

const routes = [...user, ...admin]; 

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { top: 0, behavior: 'smooth' };
        }
    }
});

export default router;