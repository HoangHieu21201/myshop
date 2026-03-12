<template>
  <footer class="app-footer bg-white border-top py-3 text-sm overflow-hidden d-flex justify-content-between align-items-center px-3" style="min-height: 50px;">
    
    <!-- Bên trái (Nội dung chính - Marquee) -->
    <div class="marquee-container flex-grow-1 position-relative" style="height: 20px;">
      <div class="marquee-content position-absolute">
        <!-- Nội dung lặp lại để tạo hiệu ứng chạy vô tận -->
        <span class="marquee-item text-success mx-4">
          MyShop <span class="text-dark">WEBSITE CÔNG NGHỆ THỜI TRANG</span> <span class="text-primary fw-bold">Version 1.0</span>
        </span>
        <span class="marquee-item text-success mx-4">
          MyShop <span class="text-dark">WEBSITE CÔNG NGHỆ THỜI TRANG</span> <span class="text-primary fw-bold">Version 1.0</span>
        </span>
        <span class="marquee-item text-success mx-4">
          MyShop <span class="text-dark">WEBSITE CÔNG NGHỆ THỜI TRANG</span> <span class="text-primary fw-bold">Version 1.0</span>
        </span>
      </div>
    </div>

    <!-- Bên phải (ẩn trên mobile) -->
    <div class="ms-3 d-none d-sm-inline opacity-75 fw-semibold text-secondary whitespace-nowrap">
      Hi Admin 👋
    </div>
    
  </footer>
</template>

<script setup>
// Footer thuần giao diện, không cần logic JS
</script>

<style scoped>
.app-footer {
  background-color: #f8f9fa !important;
  color: #495057;
  z-index: 10;
}

.marquee-container {
  overflow: hidden;
  white-space: nowrap;
}

.marquee-content {
  display: inline-block;
  animation: marquee 25s linear infinite;
}

.marquee-item {
  display: inline-block;
  font-weight: 500;
  letter-spacing: 0.5px;
}

/* Tạm dừng hiệu ứng khi di chuột vào (UX tốt hơn) */
.marquee-container:hover .marquee-content {
  animation-play-state: paused;
}

/* Hiệu ứng chạy từ phải qua trái */
@keyframes marquee {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); } 
  /* Dịch chuyển -50% vì nội dung được lặp lại bên trong để tạo vòng lặp vô tận */
}
</style>