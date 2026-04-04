<template>
  <!-- Thay số điện thoại Zalo của sếp vào đường link này -->
  <a href="https://zalo.me/0706041020" target="_blank" class="zalo-floating-btn" aria-label="Chat Zalo với SORA">
    <!-- Hiệu ứng vòng sóng tỏa ra xung quanh -->
    <div class="zalo-ring"></div>
    <div class="zalo-ring zalo-ring-2"></div>
    
    <!-- Cục nút Zalo chính -->
    <div class="zalo-circle shadow-lg">
      <img src="https://upload.wikimedia.org/wikipedia/commons/9/91/Icon_of_Zalo.svg" alt="Zalo" class="zalo-icon" />
    </div>
    
    <!-- Nhãn nhỏ mời gọi -->
    <span class="zalo-label shadow-sm font-sans fw-medium text-dark bg-white">Chat với Chuyên viên</span>
  </a>
</template>

<script setup>
// Nút Zalo tĩnh, chỉ dùng CSS để tạo hiệu ứng
</script>

<style scoped>
/* KHU VỰC QUAN TRỌNG: CĂN CHỈNH VỊ TRÍ */
.zalo-floating-btn {
  position: fixed;
  /* Đẩy cao lên 100px để nhường chỗ cho AI Chatbox ở dưới cùng (Có thể tăng lên 120px nếu AI của sếp to hơn) */
  bottom: 120px; 
  right: 35px;
  z-index: 9998; /* Nổi lên trên mọi thứ */
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  text-decoration: none;
}

/* Nút tròn chính */
.zalo-circle {
  width: 54px;
  height: 54px;
  background-color: #0068FF;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  z-index: 2;
  transition: transform 0.3s ease;
}

.zalo-icon {
  width: 34px;
  height: 34px;
  object-fit: contain;
}

.zalo-floating-btn:hover .zalo-circle {
  transform: scale(1.1);
  background-color: #0054cc;
}

/* Hiệu ứng sóng lan tỏa (Ripple Animation) */
.zalo-ring {
  position: absolute;
  width: 100%;
  height: 100%;
  background-color: #0068FF;
  border-radius: 50%;
  z-index: 1;
  opacity: 0.6;
  animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
}
.zalo-ring-2 {
  animation-delay: 0.5s;
}

@keyframes pulse-ring {
  0% {
    transform: scale(1);
    opacity: 0.6;
  }
  100% {
    transform: scale(1.8);
    opacity: 0;
  }
}

/* Nhãn mời gọi chat (Hover mới hiện ra) */
.zalo-label {
  position: absolute;
  right: 75px;
  white-space: nowrap;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  opacity: 0;
  visibility: hidden;
  transform: translateX(10px);
  transition: all 0.3s ease;
  border: 1px solid #eaeaea;
}

/* Mũi tên chỉ vào nút Zalo */
.zalo-label::after {
  content: '';
  position: absolute;
  top: 50%;
  right: -5px;
  transform: translateY(-50%);
  border-width: 5px 0 5px 6px;
  border-style: solid;
  border-color: transparent transparent transparent #ffffff;
}

.zalo-floating-btn:hover .zalo-label {
  opacity: 1;
  visibility: visible;
  transform: translateX(0);
}
</style>