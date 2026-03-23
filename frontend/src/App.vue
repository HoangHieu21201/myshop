<template>
  <div v-if="isCheckingAuth" class="vh-100 d-flex flex-column justify-content-center align-items-center bg-light">
    <h1 class="logo-shimmer mb-3">SORA</h1>
    <p class="text-muted fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">
    </p>
  </div>

  <router-view v-else></router-view>
</template>

<script setup>
import { ref, onMounted, provide } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const isCheckingAuth = ref(true);

const currentUser = ref(null);

provide('currentUser', currentUser);

const checkAuthentication = async () => {
  const token = localStorage.getItem('admin_token');
  
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
      
      currentUser.value = result.data;
      
      if (result.data.role) {
         localStorage.setItem('admin_level', result.data.role.level);
      }
    } else {
      throw new Error('Unauthorized');
    }
  } catch (err) {
    console.error('Lỗi xác thực:', err);
    localStorage.removeItem('admin_token');
    localStorage.removeItem('admin_level');
    currentUser.value = null;
    
    // Đá về trang login
    router.push('/admin/login');
  } finally {
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