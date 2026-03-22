<template>
  <div class="cart-wrapper pb-5" style="min-height: 100vh; font-family: 'Lato', sans-serif;">

    <main class="container mt-5">
      <div class="d-flex flex-wrap align-items-center justify-content-between mb-5 gap-3">
        <div>
          <h2 class="fs-1 text-dark mb-0" style="font-family: 'Playfair Display', serif;">Giỏ hàng của bạn</h2>
          <span class="text-secondary fw-light border-bottom border-danger-custom pb-1">
            {{ totalItems }} Sản phẩm trong danh sách
          </span>
        </div>
        
        <!-- Nút Xóa toàn bộ giỏ hàng -->
        <button v-if="cartItems.length > 0" 
                @click="clearCart" 
                class="btn btn-outline-danger btn-sm rounded-pill text-uppercase fw-bold px-3">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 256 256" class="me-1"><path d="M216,48H176V40a24,24,0,0,0-24-24H104A24,24,0,0,0,80,40v8H40a8,8,0,0,0,0,16h8V208a16,16,0,0,0,16,16H192a16,16,0,0,0,16-16V64h8a8,8,0,0,0,0-16ZM96,40a8,8,0,0,1,8-8h48a8,8,0,0,1,8,8v8H96Zm96,168H64V64H192ZM112,104v64a8,8,0,0,1-16,0V104a8,8,0,0,1,16,0Zm48,0v64a8,8,0,0,1-16,0V104a8,8,0,0,1,16,0Z"></path></svg>
          Làm trống giỏ
        </button>
      </div>

      <!-- Trạng thái Loading -->
      <div v-if="isLoading" class="d-flex justify-content-center align-items-center py-5">
        <div class="spinner-border" style="color: #9f273b; width: 3rem; height: 3rem;" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <!-- Giỏ hàng trống -->
      <div v-else-if="cartItems.length === 0" class="text-center py-5 bg-white shadow-sm rounded-0 border-top border-4 border-danger-custom">
        <div class="py-5">
          <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="#dee2e6" viewBox="0 0 256 256" class="mb-3"><path d="M216,40H40A16,16,0,0,0,24,56V200a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V56A16,16,0,0,0,216,40Zm0,160H40V56H216V200ZM176,88a48,48,0,0,1-96,0,8,8,0,0,1,16,0,32,32,0,0,0,64,0,8,8,0,0,1,16,0Z"></path></svg>
          <p class="fs-5 text-secondary mb-4" style="font-family: 'Playfair Display', serif;">Giỏ hàng của bạn đang trống.</p>
          <button @click="router.push('/products')" class="btn btn-primary-custom rounded-pill px-5 py-3 text-uppercase text-white shadow-sm fw-bold">
            Tiếp tục mua sắm
          </button>
        </div>
      </div>

      <!-- Nội dung giỏ hàng -->
      <div v-else class="row g-5">
        
        <!-- Cột trái: Danh sách sản phẩm -->
        <div class="col-lg-8">
          <!-- Header danh sách -->
          <div class="row d-none d-md-flex border-bottom pb-3 text-secondary small text-uppercase mb-4 fw-bold" style="letter-spacing: 0.05em;">
            <div class="col-6">Sản phẩm</div>
            <div class="col-2 text-center">Đơn giá</div>
            <div class="col-2 text-center">Số lượng</div>
            <div class="col-2 text-end">Tổng cộng</div>
          </div>

          <!-- Lặp qua từng sản phẩm -->
          <div v-for="item in cartItems" :key="item.id" class="row align-items-center border-bottom py-4 position-relative cart-item-row">
            
            <!-- Ảnh & Info -->
            <div class="col-12 col-md-6 d-flex align-items-center gap-4">
              <div class="position-relative bg-light rounded shadow-sm border" style="width: 100px; height: 100px; flex-shrink: 0; overflow: hidden;">
                <div v-if="item.isUpdating" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center" style="z-index: 10;">
                  <div class="spinner-border spinner-border-sm" style="color: #9f273b;" role="status"></div>
                </div>
                <img :src="getImageUrl(item.variant?.image_url)" 
                     @error="handleImageError"
                     :alt="item.product_name" 
                     class="w-100 h-100 object-fit-cover">
              </div>
              
              <div class="flex-grow-1">
                <h3 class="fs-5 text-dark mb-1 fw-bold" style="font-family: 'Playfair Display', serif;">{{ item.product_name }}</h3>
                <p class="small text-secondary text-uppercase mb-2" style="letter-spacing: 0.05em;">SKU: {{ item.variant?.sku }}</p>
                
                <!-- Thuộc tính -->
                <div class="d-flex flex-wrap gap-2 mb-2">
                  <span v-for="(val, key) in item.variant?.attributes" :key="key" 
                        class="badge bg-white text-secondary border fw-normal px-2 py-1 rounded-sm">
                    {{ key }}: <span class="text-dark fw-medium">{{ val }}</span>
                  </span>
                </div>
                
                <!-- Mobile only: Giá và Xóa -->
                <div class="d-flex d-md-none justify-content-between align-items-center mt-3">
                  <span class="fw-bold text-primary-custom">{{ formatPrice(item.variant?.price) }}</span>
                  <button @click="removeItem(item.id)" class="btn btn-link text-danger p-0 text-decoration-none d-flex align-items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 256 256"><path d="M216,48H176V40a24,24,0,0,0-24-24H104A24,24,0,0,0,80,40v8H40a8,8,0,0,0,0,16h8V208a16,16,0,0,0,16,16H192a16,16,0,0,0,16-16V64h8a8,8,0,0,0,0-16ZM96,40a8,8,0,0,1,8-8h48a8,8,0,0,1,8,8v8H96Zm96,168H64V64H192ZM112,104v64a8,8,0,0,1-16,0V104a8,8,0,0,1,16,0Zm48,0v64a8,8,0,0,1-16,0V104a8,8,0,0,1,16,0Z"></path></svg>
                    Xóa
                  </button>
                </div>
              </div>
            </div>

            <!-- Đơn giá (Desktop) -->
            <div class="col-2 d-none d-md-block text-center text-secondary fw-medium">
              {{ formatPrice(item.variant?.price) }}
            </div>

            <!-- Input Số lượng -->
            <div class="col-12 col-md-2 mt-4 mt-md-0 d-flex justify-content-center">
              <div class="quantity-picker shadow-sm">
                <button @click="updateQuantity(item, -1)" 
                        :disabled="item.quantity <= 1 || item.isUpdating"
                        class="qty-btn minus">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 256 256"><path d="M224,128a8,8,0,0,1-8,8H40a8,8,0,0,1,0-16H216A8,8,0,0,1,224,128Z"></path></svg>
                </button>
                <div class="qty-input">{{ item.quantity }}</div>
                <button @click="updateQuantity(item, 1)" 
                        :disabled="item.quantity >= (item.variant?.stock_quantity || 0) || item.isUpdating"
                        class="qty-btn plus">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 256 256"><path d="M224,128a8,8,0,0,1-8,8H136v80a8,8,0,0,1-16,0V136H40a8,8,0,0,1,0-16h80V40a8,8,0,0,1,16,0v80h80A8,8,0,0,1,224,128Z"></path></svg>
                </button>
              </div>
            </div>

            <!-- Tổng và Xóa (Desktop) -->
            <div class="col-2 d-none d-md-flex justify-content-end align-items-center gap-3">
              <span class="fs-5 fw-bold text-primary-custom" style="font-family: 'Playfair Display', serif;">
                {{ formatPrice(item.quantity * (item.variant?.price || 0)) }}
              </span>
              <button @click="removeItem(item.id)" :disabled="item.isUpdating" class="btn-remove-item" title="Xóa sản phẩm">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 256 256"><path d="M216,48H176V40a24,24,0,0,0-24-24H104A24,24,0,0,0,80,40v8H40a8,8,0,0,0,0,16h8V208a16,16,0,0,0,16,16H192a16,16,0,0,0,16-16V64h8a8,8,0,0,0,0-16ZM96,40a8,8,0,0,1,8-8h48a8,8,0,0,1,8,8v8H96Zm96,168H64V64H192ZM112,104v64a8,8,0,0,1-16,0V104a8,8,0,0,1,16,0Zm48,0v64a8,8,0,0,1-16,0V104a8,8,0,0,1,16,0Z"></path></svg>
              </button>
            </div>
          </div>
          
          <div class="mt-5">
            <label class="form-label small text-secondary fw-bold text-uppercase">Ghi chú đơn hàng</label>
            <textarea class="form-control rounded-0 shadow-none border-2" rows="3" placeholder="Ví dụ: Đóng gói quà tặng, giao giờ hành chính..."></textarea>
          </div>
        </div>

        <!-- Cột phải: Summary -->
        <div class="col-lg-4">
          <div class="card border-0 shadow rounded-0 p-4 sticky-top summary-card" style="top: 100px;">
            <h3 class="fs-4 text-white mb-4 pb-3 border-bottom border-light border-opacity-25" style="font-family: 'Playfair Display', serif;">Tổng quan</h3>
            
            <div class="mb-4">
              <div class="d-flex justify-content-between mb-3 text-light text-opacity-75">
                <span>Tạm tính</span>
                <span class="fw-medium text-white">{{ formatPrice(summary.subtotal) }}</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-3 text-light text-opacity-75">
                <span>Phí vận chuyển</span>
                <span class="badge bg-gold text-dark fw-bold px-2 py-1 rounded-pill">MIỄN PHÍ</span>
              </div>
            </div>

            <div class="border-top border-light border-opacity-25 pt-4 mb-4">
              <div class="d-flex justify-content-between align-items-end mb-1">
                <span class="text-white text-uppercase small" style="letter-spacing: 1px;">Tổng cộng</span>
                <span class="fs-2 fw-bold text-gold" style="font-family: 'Playfair Display', serif;">{{ formatPrice(summary.subtotal) }}</span>
              </div>
              <p class="text-end small text-light text-opacity-50 mb-0">Đã bao gồm VAT</p>
            </div>

            <button class="btn btn-gold w-100 rounded-pill py-3 fw-bold text-uppercase shadow-lg btn-checkout d-flex align-items-center justify-content-center gap-2">
              Thanh toán an toàn
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256"><path d="M208,80H176V56a48,48,0,0,0-96,0V80H48A16,16,0,0,0,32,96V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V96A16,16,0,0,0,208,80ZM96,56a32,32,0,0,1,64,0V80H96ZM208,208H48V96H208V208Zm-80-56a12,12,0,1,1-12-12A12,12,0,0,1,128,152Z"></path></svg>
            </button>

            <!-- Trust badges -->
            <div class="mt-4 pt-4 border-top border-light border-opacity-10 d-flex justify-content-center gap-4 text-gold opacity-75">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256" title="Bảo hành trọn đời"><path d="M208,40H48A16,16,0,0,0,32,56V200a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V56A16,16,0,0,0,208,40Zm0,160H48V56H208V200ZM128,120a8,8,0,0,1-8,8H104a8,8,0,0,1,0-16h16A8,8,0,0,1,128,120Zm0,32a8,8,0,0,1-8,8H104a8,8,0,0,1,0-16h16A8,8,0,0,1,128,152Zm56-32a8,8,0,0,1-8,8h-16a8,8,0,0,1,0-16h16A8,8,0,0,1,184,120Zm0,32a8,8,0,0,1-8,8h-16a8,8,0,0,1,0-16h16A8,8,0,0,1,184,152Z"></path></svg>
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256" title="Vận chuyển bảo mật"><path d="M245.54,118.06,219,82.63A16,16,0,0,0,206.13,75H176V64a16,16,0,0,0-16-16H16A16,16,0,0,0,0,64V184a16,16,0,0,0,16,16,32,32,0,0,0,64,0h96a32,32,0,0,0,64,0,16,16,0,0,0,16-16V128A15.91,15.91,0,0,0,245.54,118.06ZM16,64H160V171.18A31.7,31.7,0,0,0,144,168a32,32,0,0,0-64,0,31.7,31.7,0,0,0-16,3.18V64ZM48,208a16,16,0,1,1,16-16A16,16,0,0,1,48,208Zm128-24H93.31a32,32,0,0,0-58.62,0H24V184a32.12,32.12,0,0,0,32.11,32.11A32,32,0,0,0,16,184.47V184H176Zm32,24a16,16,0,1,1,16-16A16,16,0,0,1,208,208Zm32-24H222.69a32,32,0,0,0-58.62,0H176V91h30.13L234.38,128,240,128Z"></path></svg>
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256" title="Đổi trả 7 ngày"><path d="M224,128a96,96,0,0,1-94.71,96H128a8,8,0,0,1,0-16h1.29A80,80,0,1,0,52.23,73.43l13.43,12.1A8,8,0,0,1,60.29,99l-40,4.44A8,8,0,0,1,12,96l4.44-40A8,8,0,0,1,30.34,57.1l12,10.82A96,96,0,0,1,224,128Z"></path></svg>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import defaultPlaceholder from '@/assets/images/defaults/placeholder.png';

const router = useRouter();
const isLoading = ref(true);
const cartItems = ref([]);

/**
 * Lấy Session ID từ LocalStorage cho Guest
 */
const getSessionId = () => {
  let sid = localStorage.getItem('X-Cart-Session-Id');
  if (!sid) {
    sid = 'session_' + Math.random().toString(36).substr(2, 9);
    localStorage.setItem('X-Cart-Session-Id', sid);
  }
  return sid;
};

/**
 * Format tiền tệ VNĐ
 */
const formatPrice = (value) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(value || 0);
};

/**
 * Tổng số lượng sản phẩm
 */
const totalItems = computed(() => {
  return cartItems.value.reduce((total, item) => total + item.quantity, 0);
});

/**
 * Tính toán Tạm tính / Tổng tiền
 */
const summary = computed(() => {
  const subtotal = cartItems.value.reduce((sum, item) => sum + (item.quantity * (item.variant?.price || 0)), 0);
  return { subtotal };
});

/**
 * Xử lý đường dẫn ảnh
 */
const getImageUrl = (path) => {
  if (!path) return defaultPlaceholder;
  if (path.startsWith('http')) return path;
  return `/storage/${path}`;
};

const handleImageError = (e) => {
  e.target.src = defaultPlaceholder;
};

/**
 * API: Tải danh sách giỏ hàng từ Database
 */
const fetchCart = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/client/cart', {
      headers: { 'X-Cart-Session-Id': getSessionId() }
    });
    if (response.data && response.data.success) {
      cartItems.value = (response.data.data || []).map(item => ({
        ...item,
        isUpdating: false
      }));
    }
  } catch (error) {
    console.error('Lỗi khi tải giỏ hàng:', error);
  } finally {
    isLoading.value = false;
  }
};

/**
 * API: Cập nhật số lượng (Tăng/giảm)
 */
const updateQuantity = async (item, change) => {
  const newQty = item.quantity + change;
  // Giới hạn số lượng từ 1 đến tồn kho hiện có
  if (newQty < 1 || newQty > (item.variant?.stock_quantity || 0)) return; 
  
  item.isUpdating = true;
  try {
    const response = await axios.put(`/api/client/cart/${item.id}`, 
      { quantity: newQty }, 
      { headers: { 'X-Cart-Session-Id': getSessionId() } }
    );
    if (response.data.success) {
      item.quantity = newQty;
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Không thể cập nhật số lượng. Có thể sản phẩm đã hết hàng trong kho.');
  } finally {
    item.isUpdating = false;
  }
};

/**
 * API: Xóa 1 sản phẩm khỏi giỏ hàng
 */
const removeItem = async (itemId) => {
  if(!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) return;
  
  const index = cartItems.value.findIndex(i => i.id === itemId);
  if (index === -1) return;

  cartItems.value[index].isUpdating = true;
  try {
    const response = await axios.delete(`/api/client/cart/${itemId}`, {
      headers: { 'X-Cart-Session-Id': getSessionId() }
    });
    if (response.data.success) {
      cartItems.value.splice(index, 1);
    }
  } catch (error) {
    alert('Có lỗi xảy ra khi xóa sản phẩm.');
    cartItems.value[index].isUpdating = false;
  }
};

/**
 * API: Xóa toàn bộ giỏ hàng
 */
const clearCart = async () => {
  if (!confirm('Bạn có chắc chắn muốn xóa toàn bộ sản phẩm trong giỏ hàng?')) return;
  
  isLoading.value = true;
  try {
    const response = await axios.post('/api/client/cart/clear', {}, {
      headers: { 'X-Cart-Session-Id': getSessionId() }
    });
    if (response.data.success) {
      cartItems.value = [];
    }
  } catch (error) {
    alert('Không thể làm trống giỏ hàng.');
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  getSessionId();
  fetchCart();
});
</script>

<style scoped>
/* Màu sắc chủ đạo Luxury */
.text-primary-custom { color: #9f273b !important; }
.text-gold { color: #e7ce7d !important; }
.bg-gold { background-color: #e7ce7d !important; }
.border-danger-custom { border-color: #9f273b !important; }

.cart-wrapper {
  background-color: #fcfcfc;
}

.brand-logo {
  letter-spacing: 0.15em;
}

/* Kiểu dáng Bộ chọn số lượng (Quantity Picker) */
.quantity-picker {
  display: flex;
  align-items: center;
  background: white;
  border: 1px solid #eee;
  border-radius: 50px;
  padding: 2px;
  width: 110px;
  height: 40px;
}

.qty-btn {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: none;
  background: #f8f9fa;
  color: #555;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.qty-btn:hover:not(:disabled) {
  background: #9f273b;
  color: white;
}

.qty-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.qty-input {
  flex-grow: 1;
  text-align: center;
  font-weight: bold;
  font-size: 1rem;
}

/* Kiểu dáng Nút xóa */
.btn-remove-item {
  background: none;
  border: none;
  color: #ced4da;
  transition: color 0.2s;
  padding: 5px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-remove-item:hover {
  color: #dc3545;
}

/* Kiểu dáng Thẻ Tổng kết */
.summary-card {
  background-color: #9f273b;
  color: white;
}

.btn-gold {
  background-color: #e7ce7d;
  color: #9f273b;
  border: none;
  transition: all 0.3s ease;
}

.btn-gold:hover {
  background-color: #f1e0a8;
  box-shadow: 0 4px 15px rgba(231, 206, 125, 0.4);
  transform: translateY(-2px);
}

.btn-primary-custom {
  background-color: #9f273b;
  border: none;
  transition: all 0.3s ease;
}

.btn-primary-custom:hover {
  background-color: #cc1e2e;
  transform: translateY(-2px);
}

.cart-item-row {
  transition: background-color 0.2s;
}

.cart-item-row:hover {
  background-color: #fffafa;
}

textarea:focus {
  border-color: #9f273b;
  box-shadow: 0 0 0 0.2rem rgba(159, 39, 59, 0.1);
}

.badge {
  font-size: 0.75rem;
}
</style>