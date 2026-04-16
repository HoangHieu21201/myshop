import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js' 

import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Cấu hình Axios mặc định
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

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
    authorizer: (channel, options) => {
        return {
            authorize: (socketId, callback) => {
                const token = localStorage.getItem('admin_token') || localStorage.getItem('auth_token') || localStorage.getItem('token');
                fetch('http://localhost:8000/api/broadcasting/auth', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({
                        socket_id: socketId,
                        channel_name: channel.name
                    })
                })
                .then(response => response.json())
                .then(data => {
                    callback(false, data);
                })
                .catch(error => {
                    callback(true, error);
                });
            }
        };
    },
});

// Khi Echo kết nối thành công, gán Socket ID vào Axios để sử dụng toOthers()
window.Echo.connector.pusher.connection.bind('connected', () => {
    const socketId = window.Echo.socketId();
    if (socketId) {
        window.axios.defaults.headers.common['X-Socket-Id'] = socketId;
        console.log('Echo connected. Socket ID synced to Axios:', socketId);
    }
});

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.mount('#app')