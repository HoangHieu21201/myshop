<template>
  <div class="cart-wrapper pb-5" style="min-height: 100vh; font-family: 'Lato', sans-serif;">


    <main class="container mt-5">
      <div class="d-flex align-items-center justify-content-between mb-5">
        <h2 class="fs-1 text-dark mb-0" style="font-family: 'Playfair Display', serif;">Giỏ hàng của bạn</h2>
        <span class="text-secondary fw-light border-bottom border-danger-custom pb-1">
          {{ totalItems }} Sản phẩm
        </span>
      </div>

      <!-- Trạng thái Loading -->
      <div v-if="isLoading" class="d-flex justify-content-center align-items-center py-5">
        <div class="spinner-border" style="color: #9f273b; width: 3rem; height: 3rem;" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <!-- Giỏ hàng trống -->
      <div v-else-if="cartItems.length === 0" class="text-center py-5 bg-white shadow-sm rounded-0">
        <i class="ph ph-handbag text-secondary mb-3" style="font-size: 5rem; opacity: 0.3;"></i>
        <p class="fs-5 text-secondary mb-4" style="font-family: 'Playfair Display', serif;">Giỏ hàng của bạn đang trống.</p>
        <!-- Nút tiếp tục mua sắm: Đá về trang chủ -->
        <button @click="router.push('/products')" class="btn btn-primary-custom rounded-0 px-5 py-3 text-uppercase text-white shadow-sm">
          Tiếp tục mua sắm
        </button>
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
            <div class="col-2 text-end">Tổng</div>
          </div>

          <!-- Lặp qua từng sản phẩm -->
          <div v-for="item in cartItems" :key="item.id" class="row align-items-center border-bottom py-4 position-relative cart-item-row">
            
            <!-- Ảnh & Info -->
            <div class="col-12 col-md-6 d-flex align-items-center gap-4">
              <div class="position-relative bg-light rounded-0 overflow-hidden shadow-sm" style="width: 100px; height: 100px; flex-shrink: 0;">
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
                
                <div class="d-flex flex-wrap gap-2 mb-2">
                  <span v-for="(val, key) in item.variant?.attributes" :key="key" 
                        class="badge bg-white text-secondary border fw-normal px-2 py-1">
                    {{ key }}: <span class="text-dark fw-medium">{{ val }}</span>
                  </span>
                </div>
                
                <div class="d-flex d-md-none justify-content-between align-items-center mt-3">
                  <span class="fw-bold text-primary-custom">{{ formatPrice(item.variant?.price) }}</span>
                  <button @click="removeItem(item.id)" class="btn btn-link text-danger p-0 text-decoration-none small">
                    <i class="ph ph-trash"></i> Xóa
                  </button>
                </div>
              </div>
            </div>

            <!-- Đơn giá (Desktop) -->
            <div class="col-2 d-none d-md-block text-center text-secondary">
              {{ formatPrice(item.variant?.price) }}
            </div>

            <!-- Input Số lượng -->
            <div class="col-12 col-md-2 mt-4 mt-md-0 d-flex justify-content-center">
              <div class="input-group border rounded-pill overflow-hidden shadow-sm" style="width: 110px;">
                <button @click="updateQuantity(item, -1)" 
                        :disabled="item.quantity <= 1 || item.isUpdating"
                        class="btn btn-light border-0 text-secondary px-2">
                  <i class="ph ph-minus"></i>
                </button>
                <input type="text" readonly :value="item.quantity" 
                       class="form-control border-0 text-center bg-transparent px-0 fw-bold shadow-none">
                <button @click="updateQuantity(item, 1)" 
                        :disabled="item.quantity >= item.variant?.stock_quantity || item.isUpdating"
                        class="btn btn-light border-0 text-secondary px-2">
                  <i class="ph ph-plus"></i>
                </button>
              </div>
            </div>

            <!-- Tổng và Xóa (Desktop) -->
            <div class="col-2 d-none d-md-flex justify-content-end align-items-center gap-3">
              <span class="fs-5 fw-bold text-primary-custom" style="font-family: 'Playfair Display', serif;">
                {{ formatPrice(item.quantity * (item.variant?.price || 0)) }}
              </span>
              <button @click="removeItem(item.id)" :disabled="item.isUpdating" class="btn btn-link text-secondary p-0 fs-5 hover-red">
                <i class="ph ph-x-circle"></i>
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
                <span class="badge bg-gold text-dark fw-bold px-2 py-1 rounded-0">MIỄN PHÍ</span>
              </div>
            </div>

            <div class="border-top border-light border-opacity-25 pt-4 mb-4">
              <div class="d-flex justify-content-between align-items-end mb-1">
                <span class="text-white text-uppercase small" style="letter-spacing: 1px;">Tổng cộng</span>
                <span class="fs-2 fw-bold text-gold" style="font-family: 'Playfair Display', serif;">{{ formatPrice(summary.subtotal) }}</span>
              </div>
              <p class="text-end small text-light text-opacity-50 mb-0">Đã bao gồm VAT</p>
            </div>

            <button class="btn btn-gold w-100 rounded-0 py-3 fw-bold text-uppercase shadow-lg btn-checkout">
              Thanh toán an toàn
              <i class="ph ph-lock-key-fill ms-2"></i>
            </button>

            <div class="mt-4 pt-4 border-top border-light border-opacity-10 d-flex justify-content-center gap-4 text-gold opacity-75">
              <i class="ph ph-shield-check fs-3"></i>
              <i class="ph ph-package fs-3"></i>
              <i class="ph ph-arrow-u-up-left fs-3"></i>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router'; // Import useRouter để điều hướng
import axios from 'axios';
import defaultPlaceholder from '@/assets/images/defaults/placeholder.png';

const router = useRouter(); // Khởi tạo router
const isLoading = ref(true);
const cartItems = ref([]);

// Hàm điều hướng về trang chủ
const goHome = () => {
  router.push('/');
};

const getSessionId = () => {
  let sid = localStorage.getItem('X-Cart-Session-Id');
  if (!sid) {
    sid = 'session_' + Math.random().toString(36).substr(2, 9);
    localStorage.setItem('X-Cart-Session-Id', sid);
  }
  return sid;
};

const formatPrice = (value) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(value || 0);
};

const totalItems = computed(() => {
  return cartItems.value.reduce((total, item) => total + item.quantity, 0);
});

const summary = computed(() => {
  const subtotal = cartItems.value.reduce((sum, item) => sum + (item.quantity * (item.variant?.price || 0)), 0);
  return { subtotal };
});

const getImageUrl = (path) => {
  if (!path) return defaultPlaceholder;
  if (path.startsWith('http')) return path;
  return `/storage/${path}`;
};

const handleImageError = (e) => {
  e.target.src = defaultPlaceholder;
};

const fetchCart = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/client/cart', {
      headers: { 'X-Cart-Session-Id': getSessionId() }
    });
    if (response.data && response.data.data) {
      cartItems.value = response.data.data.map(item => ({
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

const updateQuantity = async (item, change) => {
  const newQty = item.quantity + change;
  if (newQty < 1 || newQty > (item.variant?.stock_quantity || 0)) return; 
  
  item.isUpdating = true;
  try {
    await axios.put(`/api/client/cart/${item.id}`, { quantity: newQty }, {
      headers: { 'X-Cart-Session-Id': getSessionId() }
    });
    item.quantity = newQty;
  } catch (error) {
    alert(error.response?.data?.message || 'Không thể cập nhật số lượng');
  } finally {
    item.isUpdating = false;
  }
};

const removeItem = async (itemId) => {
  if(!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) return;
  const index = cartItems.value.findIndex(i => i.id === itemId);
  if (index === -1) return;

  cartItems.value[index].isUpdating = true;
  try {
    await axios.delete(`/api/client/cart/${itemId}`, {
      headers: { 'X-Cart-Session-Id': getSessionId() }
    });
    cartItems.value.splice(index, 1);
  } catch (error) {
    alert('Có lỗi xảy ra khi xóa sản phẩm.');
    cartItems.value[index].isUpdating = false;
  }
};

onMounted(() => {
  getSessionId();
  fetchCart();
});
</script>

<style scoped>
/* Màu sắc chủ đạo theo yêu cầu */
.text-primary-custom { color: #9f273b !important; }
.text-gold { color: #e7ce7d !important; }
.bg-gold { background-color: #e7ce7d !important; }
.border-danger-custom { border-color: #cc1e2e !important; }

.cart-wrapper {
  background-color: #f8f9fa;
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

.btn-checkout {
  letter-spacing: 2px;
}

.cart-item-row {
  transition: background-color 0.2s;
}

.cart-item-row:hover {
  background-color: #fffafa;
}

.hover-red:hover {
  color: #cc1e2e !important;
}

textarea:focus {
  border-color: #9f273b;
  box-shadow: 0 0 0 0.2rem rgba(159, 39, 59, 0.1);
}

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>