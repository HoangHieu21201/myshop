<template>
  <div class="bg-light pb-5" style="min-height: 100vh; font-family: 'Lato', sans-serif;">
    

    <main class="container mt-5">
      <div class="d-flex align-items-center justify-content-between mb-5">
        <h2 class="fs-1 text-dark mb-0" style="font-family: 'Playfair Display', serif;">Giỏ hàng của bạn</h2>
        <span class="text-secondary fw-light border-bottom pb-1">
          {{ totalItems }} Sản phẩm
        </span>
      </div>

      <!-- Trạng thái Loading -->
      <div v-if="isLoading" class="d-flex justify-content-center align-items-center py-5">
        <div class="spinner-border" style="color: #D4AF37; width: 3rem; height: 3rem;" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <!-- Giỏ hàng trống -->
      <div v-else-if="cartItems.length === 0" class="text-center py-5 bg-white shadow-sm rounded">
        <i class="ph ph-handbag text-secondary mb-3" style="font-size: 5rem; opacity: 0.5;"></i>
        <p class="fs-5 text-secondary mb-4" style="font-family: 'Playfair Display', serif;">Giỏ hàng của bạn đang trống.</p>
        <button class="btn rounded-0 px-5 py-3 text-uppercase text-white" style="background-color: #2D2D2D; letter-spacing: 0.05em;">
          Tiếp tục mua sắm
        </button>
      </div>

      <!-- Nội dung giỏ hàng -->
      <div v-else class="row g-5">
        
        <!-- Cột trái: Danh sách sản phẩm -->
        <div class="col-lg-8">
          <!-- Header danh sách -->
          <div class="row d-none d-md-flex border-bottom pb-3 text-secondary small text-uppercase mb-4" style="letter-spacing: 0.05em;">
            <div class="col-6">Sản phẩm</div>
            <div class="col-2 text-center">Đơn giá</div>
            <div class="col-2 text-center">Số lượng</div>
            <div class="col-2 text-end">Tổng</div>
          </div>

          <!-- Lặp qua từng sản phẩm -->
          <div v-for="item in cartItems" :key="item.id" class="row align-items-center border-bottom py-4 position-relative">
            
            <!-- Ảnh & Info -->
            <div class="col-12 col-md-6 d-flex align-items-center gap-4">
              <div class="position-relative bg-light rounded overflow-hidden" style="width: 100px; height: 100px; flex-shrink: 0;">
                <!-- Overlay khi đang update item này -->
                <div v-if="item.isUpdating" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center" style="z-index: 10;">
                  <div class="spinner-border spinner-border-sm" style="color: #D4AF37;" role="status"></div>
                </div>
                <!-- Sửa thẻ img để bắt sự kiện lỗi và ghép link storage -->
                <img :src="getImageUrl(item.variant?.image_url)" 
                     @error="handleImageError"
                     :alt="item.product_name" 
                     class="w-100 h-100 object-fit-cover">
              </div>
              
              <div class="flex-grow-1">
                <h3 class="fs-5 text-dark mb-1" style="font-family: 'Playfair Display', serif;">{{ item.product_name }}</h3>
                <p class="small text-secondary text-uppercase mb-2" style="letter-spacing: 0.05em;">SKU: {{ item.variant.sku }}</p>
                
                <!-- Thuộc tính -->
                <div class="d-flex flex-wrap gap-2 mb-2">
                  <span v-for="(val, key) in item.variant.attributes" :key="key" 
                        class="badge bg-light text-secondary border fw-normal px-2 py-1">
                    {{ key }}: <span class="text-dark fw-medium">{{ val }}</span>
                  </span>
                </div>
                
                <!-- Mobile only: Nút xóa và Giá -->
                <div class="d-flex d-md-none justify-content-between align-items-center mt-3">
                  <span class="fw-medium text-dark">{{ formatPrice(item.variant.price) }}</span>
                  <button @click="removeItem(item.id)" class="btn btn-link text-secondary p-0 text-decoration-none small">
                    <i class="ph ph-trash"></i> Xóa
                  </button>
                </div>
              </div>
            </div>

            <!-- Đơn giá (Desktop) -->
            <div class="col-2 d-none d-md-block text-center text-secondary">
              {{ formatPrice(item.variant.price) }}
            </div>

            <!-- Input Số lượng -->
            <div class="col-12 col-md-2 mt-4 mt-md-0 d-flex justify-content-center">
              <div class="input-group border rounded" style="width: 110px;">
                <button @click="updateQuantity(item, -1)" 
                        :disabled="item.quantity <= 1 || item.isUpdating"
                        class="btn btn-light border-0 text-secondary px-2">
                  <i class="ph ph-minus"></i>
                </button>
                <input type="text" readonly :value="item.quantity" 
                       class="form-control border-0 text-center bg-transparent px-0 fw-medium shadow-none">
                <!-- Đổi stock thành stock_quantity theo đúng tên cột CSDL -->
                <button @click="updateQuantity(item, 1)" 
                        :disabled="item.quantity >= item.variant?.stock_quantity || item.isUpdating"
                        class="btn btn-light border-0 text-secondary px-2">
                  <i class="ph ph-plus"></i>
                </button>
              </div>
            </div>

            <!-- Tổng và Xóa (Desktop) -->
            <div class="col-2 d-none d-md-flex justify-content-end align-items-center gap-3">
              <span class="fs-5 fw-medium text-dark" style="font-family: 'Playfair Display', serif;">
                {{ formatPrice(item.quantity * item.variant.price) }}
              </span>
              <button @click="removeItem(item.id)" :disabled="item.isUpdating" class="btn btn-link text-secondary p-0 fs-5">
                <i class="ph ph-x"></i>
              </button>
            </div>
          </div>
          
          <!-- Ghi chú đơn hàng -->
          <div class="mt-5">
            <label class="form-label small text-secondary">Thêm ghi chú cho đơn hàng của bạn</label>
            <textarea class="form-control rounded shadow-none" rows="3" placeholder="Ví dụ: Đóng gói quà tặng, giao giờ hành chính..."></textarea>
          </div>
        </div>

        <!-- Cột phải: Tổng kết đơn hàng (Summary) -->
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm rounded p-4 sticky-top" style="top: 2rem;">
            <h3 class="fs-4 text-dark mb-4 pb-3 border-bottom" style="font-family: 'Playfair Display', serif;">Tổng quan</h3>
            
            <div class="mb-4">
              <div class="d-flex justify-content-between mb-3 text-secondary">
                <span>Tạm tính</span>
                <span class="fw-medium text-dark">{{ formatPrice(summary.subtotal) }}</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-3 text-secondary">
                <span>Phí vận chuyển</span>
                <span class="badge bg-success bg-opacity-10 text-success fw-medium px-2 py-1 rounded">MIỄN PHÍ</span>
              </div>
              <div class="text-end small text-secondary opacity-75">
                <span>Áp dụng cho đơn hàng trang sức</span>
              </div>
            </div>

            <div class="border-top pt-4 mb-4">
              <div class="d-flex justify-content-between align-items-end mb-1">
                <span class="text-dark fw-medium">Tổng cộng</span>
                <span class="fs-3 fw-bold" style="color: #D4AF37; font-family: 'Playfair Display', serif;">{{ formatPrice(summary.subtotal) }}</span>
              </div>
              <p class="text-end small text-secondary mb-0">Đã bao gồm VAT</p>
            </div>

            <button class="btn w-100 rounded-0 py-3 fw-medium text-uppercase text-white d-flex align-items-center justify-content-center gap-2" style="background-color: #2D2D2D; letter-spacing: 0.1em;">
              Thanh toán an toàn
              <i class="ph ph-lock-key fs-5"></i>
            </button>

            <!-- Trust badges -->
            <div class="mt-4 pt-4 border-top d-flex justify-content-center gap-4 text-secondary">
              <i class="ph ph-shield-check fs-3" title="Bảo hành trọn đời"></i>
              <i class="ph ph-package fs-3" title="Vận chuyển bảo mật"></i>
              <i class="ph ph-arrow-u-up-left fs-3" title="Đổi trả 7 ngày"></i>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios'; // Bắt buộc phải import axios để gọi API
import defaultPlaceholder from '@/assets/images/defaults/placeholder.png'; // Import ảnh mặc định của bạn

// Khởi tạo state
const isLoading = ref(true);
const cartItems = ref([]);

// Lấy Session ID từ LocalStorage (Dành cho Guest)
const getSessionId = () => {
  let sid = localStorage.getItem('X-Cart-Session-Id');
  if (!sid) {
    sid = 'session_' + Math.random().toString(36).substr(2, 9);
    localStorage.setItem('X-Cart-Session-Id', sid);
  }
  return sid;
};

// Hàm format tiền tệ VNĐ
const formatPrice = (value) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(value);
};

// Tổng số lượng item trong giỏ
const totalItems = computed(() => {
  return cartItems.value.reduce((total, item) => total + item.quantity, 0);
});

// Summary chốt lại
const summary = computed(() => {
  const subtotal = cartItems.value.reduce((sum, item) => sum + (item.quantity * item.variant.price), 0);
  return { subtotal };
});

// Hàm xử lý đường dẫn ảnh (thêm /storage/ nếu backend chỉ trả về tên file)
const getImageUrl = (path) => {
  if (!path) return defaultPlaceholder;
  if (path.startsWith('http')) return path;
  return `/storage/${path}`; // Tùy thuộc vào cấu hình symlink storage của Laravel
};

// Hàm fallback: Nếu trình duyệt không tải được ảnh (lỗi 404), thay bằng ảnh mặc định
const handleImageError = (e) => {
  e.target.src = defaultPlaceholder;
};

// [KẾT NỐI API] Lấy giỏ hàng TỪ DATABASE
const fetchCart = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/client/cart', {
      headers: { 'X-Cart-Session-Id': getSessionId() }
    });
    
    // Ánh xạ dữ liệu trả về và thêm cờ isUpdating cho từng món
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

// [KẾT NỐI API] Cập nhật số lượng
const updateQuantity = async (item, change) => {
  const newQty = item.quantity + change;
  // Dùng stock_quantity theo đúng cấu trúc bảng product_variants
  if (newQty < 1 || newQty > item.variant.stock_quantity) return; 
  
  item.isUpdating = true;
  try {
    await axios.put(`/api/client/cart/${item.id}`, { quantity: newQty }, {
      headers: { 'X-Cart-Session-Id': getSessionId() }
    });
    item.quantity = newQty; // Chỉ cập nhật UI nếu API báo thành công
  } catch (error) {
    alert(error.response?.data?.message || 'Không thể cập nhật số lượng');
  } finally {
    item.isUpdating = false;
  }
};

// [KẾT NỐI API] Xóa item
const removeItem = async (itemId) => {
  const index = cartItems.value.findIndex(i => i.id === itemId);
  if (index === -1) return;

  cartItems.value[index].isUpdating = true;
  try {
    await axios.delete(`/api/client/cart/${itemId}`, {
      headers: { 'X-Cart-Session-Id': getSessionId() }
    });
    cartItems.value.splice(index, 1); // Xóa khỏi UI
  } catch (error) {
    alert('Có lỗi xảy ra khi xóa sản phẩm khỏi giỏ hàng.');
    cartItems.value[index].isUpdating = false;
  }
};

onMounted(() => {
  getSessionId();
  fetchCart();
});
</script>

<style scoped>
/* Focus Input */
textarea:focus {
  border-color: #D4AF37;
  box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
}
/* Disable states */
button:disabled { opacity: 0.4; }
</style>