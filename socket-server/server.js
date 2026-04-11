const express = require('express');
const app = express();
const http = require('http').createServer(app);
const cors = require('cors');

// Cấu hình Socket.io cho phép Frontend kết nối
const io = require('socket.io')(http, {
    cors: {
        origin: "*", // Cho phép mọi domain kết nối tới
        methods: ["GET", "POST"]
    }
});

// Cho phép Node.js đọc dữ liệu JSON từ Laravel gửi sang
app.use(express.json());
app.use(cors());

// -----------------------------------------------------
// API NÀY ĐỂ LARAVEL GỌI SANG MỖI KHI CÓ ĐƠN HÀNG MỚI
// -----------------------------------------------------
app.post('/api/emit-order', (req, res) => {
    const orderData = req.body;
    
    console.log("🔔 Nhận được lệnh từ Laravel, đang báo cho Admin...", orderData.orderCode);
    io.emit('new_order_received', orderData);
    res.json({ success: true, message: "Đã phát sóng thành công!" });
});

// -----------------------------------------------------
// [ĐÃ THÊM MỚI] KÊNH DÙNG CHUNG CHO MỌI CHỨC NĂNG CỦA ADMIN (THÊM/SỬA/XÓA)
// -----------------------------------------------------
app.post('/api/admin-refresh', (req, res) => {
    const data = req.body;
    console.log("🔄 Lệnh làm mới dữ liệu Admin, Module:", data.module);
    
    // Phát sóng sự kiện 'refresh_admin_data' xuống toàn bộ Frontend (Vue.js) đang mở
    io.emit('refresh_admin_data', data);
    
    res.json({ success: true });
});

// Lắng nghe xem có Admin nào đang mở trang web không
io.on('connection', (socket) => {
    console.log('🟢 Một thiết bị Admin vừa kết nối Socket: ' + socket.id);
    
    socket.on('disconnect', () => {
        console.log('🔴 Admin đã ngắt kết nối: ' + socket.id);
    });
});

// Khởi động server ở cổng 3000
http.listen(3000, () => {
    console.log('🚀 TRẠM PHÁT SÓNG NODE.JS ĐANG CHẠY Ở CỔNG: 3000');
});