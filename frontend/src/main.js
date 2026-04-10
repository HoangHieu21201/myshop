import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js' 

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Cấu hình bắt sóng Real-time
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: 'sorajewelrykey123', // Khớp 100% với backend
    wsHost: '127.0.0.1',
    wsPort: 8080,
    wssPort: 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.mount('#app')