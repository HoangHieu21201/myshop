<template>
  <!-- MÀN HÌNH CHỜ: Bật lên khi đang F5 hoặc đang kiểm tra Token -->
  <div v-if="isCheckingAuth" class="vh-100 d-flex flex-column justify-content-center align-items-center bg-light">
    <h1 class="logo-shimmer mb-3">SORA</h1>
    <p class="text-muted fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">
      Đang xác thực phiên đăng nhập...
    </p>
  </div>

  <!-- ROUTER VIEW: Chỉ render khi đã check xong (dù thành công hay thất bại) -->
  <router-view v-else></router-view>
</template>

<script setup>
import { ref, onMounted, provide } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

// Trạng thái khóa UI toàn cục
const isCheckingAuth = ref(true);

// Trạng thái lưu trữ user dùng chung cho toàn bộ app
const currentUser = ref(null);

// CUNG CẤP DỮ LIỆU: Bất kỳ file nào (Sidebar, Header) cũng có thể gọi `inject('currentUser')` để lấy thông tin này
provide('currentUser', currentUser);

const checkAuthentication = async () => {
  const token = localStorage.getItem('admin_token');
  
  // Nếu không có token, thả khóa UI để Router tự động đá về trang Login (thông qua Navigation Guards)
  if (!token) {
    isCheckingAuth.value = false;
    return;
  }

  try {
    const res = await fetch('http://127.0.0.1:8000/api/admin/me', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    });

    if (res.ok) {
      const result = await res.json();
      
      // Lưu data lấy được (đã có role và level) vào biến toàn cục
      currentUser.value = result.data;
      
      // (Tùy chọn) Lưu lại level vào localStorage dự phòng cho các logic routing cơ bản
      if (result.data.role) {
         localStorage.setItem('admin_level', result.data.role.level);
      }
    } else {
      // Token hết hạn hoặc server trả về 401/403 -> Đăng xuất ngay lập tức
      throw new Error('Unauthorized');
    }
  } catch (err) {
    console.error('Lỗi xác thực:', err);
    // Xóa sạch dấu vết
    localStorage.removeItem('admin_token');
    localStorage.removeItem('admin_level');
    currentUser.value = null;
    
    // Đá về trang login
    router.push('/admin/login');
  } finally {
    // Luôn luôn phải mở khóa UI dù lỗi hay không
    isCheckingAuth.value = false;
  }
};

onMounted(() => {
  checkAuthentication();
});
</script>

<style>
/* CSS Reset cơ bản & CSS dùng chung toàn cục */
body {
  margin: 0;
  padding: 0;
  background-color: #f8f9fa;
}

/* Hiệu ứng Shimmer từ ThinkHub mang ra dùng chung */
.logo-shimmer {
  font-size: 3.5rem;
  font-weight: 900;
  letter-spacing: -1.5px;
  background: linear-gradient(120deg, #9f273b 30%, #080808 50%, #edb2b2 70%);
  background-size: 200% auto;
  color: transparent;
  -webkit-background-clip: text;
  background-clip: text;
  animation: shine 1.5s linear infinite;
}

@keyframes shine {
  to { background-position: 200% center; }
}

.tracking-widest {
  letter-spacing: 0.25em;
}
</style>