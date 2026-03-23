<template>
  <div v-if="isOpen" class="custom-modal-backdrop" @click.self="$emit('close')">
    <div class="custom-modal-content shadow-lg border-0 rounded-0 slide-up">
      <div class="modal-header-luxury p-4 d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-0 fw-bold font-serif text-white">Đơn hàng #{{ order?.order_code }}</h4>
          <span class="text-light opacity-75 small">Ngày đặt: {{ formatDate(order?.created_at) }}</span>
        </div>
        <button @click="$emit('close')" class="btn-close-luxury">✕</button>
      </div>

      <div class="modal-body p-4 overflow-auto" style="max-height: 75vh;">
        <div class="row g-4">
          <!-- Thông tin khách hàng -->
          <div class="col-lg-5">
            <h5 class="fw-bold mb-3 border-bottom pb-2 font-serif text-primary-custom">THÔNG TIN GIAO HÀNG</h5>
            <div class="customer-info-box p-3 bg-light border mb-4">
              <p class="mb-2"><strong class="small text-uppercase text-muted d-block">Người nhận:</strong> {{ order?.customer_name }}</p>
              <p class="mb-2"><strong class="small text-uppercase text-muted d-block">Số điện thoại:</strong> {{ order?.customer_phone }}</p>
              <p class="mb-0"><strong class="small text-uppercase text-muted d-block">Địa chỉ:</strong> {{ order?.customer_address }}</p>
            </div>

            <h5 class="fw-bold mb-3 border-bottom pb-2 font-serif text-primary-custom">TRẠNG THÁI ĐƠN HÀNG</h5>
            <ul class="timeline-luxury small ps-3">
              <li v-for="h in order?.histories" :key="h.id" class="mb-3">
                <div class="fw-bold text-dark">{{ translateStatus(h.new_status) }}</div>
                <div class="text-muted smaller">{{ h.time }}</div>
              </li>
            </ul>
          </div>

          <!-- Danh sách sản phẩm chi tiết -->
          <div class="col-lg-7">
            <h5 class="fw-bold mb-3 border-bottom pb-2 font-serif text-primary-custom">DANH SÁCH SẢN PHẨM</h5>
            <div class="detailed-items pe-2">
              <div v-for="item in order?.items" :key="item.id" class="d-flex gap-3 mb-3 border-bottom pb-3 border-light">
                <div class="product-preview-img border flex-shrink-0" style="width: 80px; height: 80px;">
                  <img :src="getImageUrl(item.variant_image)" @error="handleImageError" class="w-100 h-100 object-fit-cover">
                </div>
                <div class="flex-grow-1">
                  <div class="fw-bold text-dark">{{ item.product_name }}</div>
                  <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="text-muted small">{{ formatPrice(item.price) }} x {{ item.quantity || 1 }}</span>
                    <span class="fw-bold text-primary-custom">{{ formatPrice(item.price * (item.quantity || 1)) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tổng tiền -->
            <div class="bg-light p-3 mt-4 border shadow-sm">
              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Tạm tính:</span>
                <span class="fw-medium">{{ formatPrice(order?.sub_total || order?.total_amount) }}</span>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Phí vận chuyển:</span>
                <span class="text-success fw-bold">Miễn phí</span>
              </div>
              <hr class="my-2">
              <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark text-uppercase">Tổng cộng:</span>
                <h4 class="fw-bold text-primary-custom mb-0 font-serif">{{ formatPrice(order?.total_amount) }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer-luxury p-3 border-top bg-white text-end">
        <button @click="$emit('close')" class="btn btn-dark rounded-0 px-5 fw-bold text-uppercase small">Đóng cửa sổ</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import defaultPlaceholder from '@/assets/images/defaults/placeholder.png';

const props = defineProps({
  isOpen: Boolean,
  order: Object
});

defineEmits(['close']);

const formatPrice = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('vi-VN', {
    year: 'numeric', month: 'long', day: 'numeric'
  });
};

const getImageUrl = (path) => {
  if (!path) return defaultPlaceholder;
  if (path.startsWith('http')) return path;
  return `/storage/${path}`;
};

const handleImageError = (e) => { e.target.src = defaultPlaceholder; };

const translateStatus = (status) => {
  const map = {
    pending: 'Chờ xác nhận',
    delivered: 'Hoàn tất',
    cancelled: 'Đã hủy'
  };
  return map[status] || status;
};
</script>

<style scoped>
.text-primary-custom { color: #9f273b !important; }
.font-serif { font-family: 'Playfair Display', serif; }

.custom-modal-backdrop {
  position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
  background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(5px);
  z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px;
}
.custom-modal-content {
  background: white; width: 100%; max-width: 850px; max-height: 90vh;
  position: relative;
}
.modal-header-luxury { background: #9f273b; color: white; }
.btn-close-luxury { background: none; border: none; color: white; font-size: 1.5rem; opacity: 0.8; transition: 0.3s; }
.btn-close-luxury:hover { opacity: 1; transform: scale(1.1); }

.timeline-luxury { list-style: none; border-left: 2px solid #9f273b; position: relative; }
.timeline-luxury li { position: relative; margin-bottom: 20px; padding-left: 10px; }
.timeline-luxury li::before {
  content: ""; position: absolute; left: -17px; top: 5px;
  width: 10px; height: 10px; border-radius: 50%; background: white; border: 2px solid #9f273b;
}

@keyframes slideUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
.slide-up { animation: slideUp 0.3s ease-out; }

.product-preview-img { overflow: hidden; border-radius: 4px; }
</style>