<template>
  <div class="order-history-wrapper pb-5" style="min-height: 100vh; font-family: 'Lato', sans-serif;">
    <!-- Header đồng nhất với Giỏ hàng -->
    <header class="bg-white border-bottom py-4 text-center sticky-top shadow-sm" style="z-index: 1000;">
      <h1 class="brand-logo fs-3 fw-bold text-uppercase mb-0" style="font-family: 'Playfair Display', serif; letter-spacing: 0.2em; color: #9f273b;">
        LUXURY<span style="color: #e7ce7d;">JEWEL</span>
      </h1>
    </header>

    <main class="container mt-5">
      <div class="d-flex align-items-center justify-content-between mb-5">
        <h2 class="fs-1 text-dark mb-0" style="font-family: 'Playfair Display', serif;">Lịch sử đơn hàng</h2>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="#" @click.prevent="router.push('/')" class="text-decoration-none text-primary-custom">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Đơn hàng</li>
          </ol>
        </nav>
      </div>

      <!-- Trạng thái Loading: Chỉ hiện khi đang thực sự tải -->
      <div v-if="isLoading" class="d-flex justify-content-center align-items-center py-5">
        <div class="spinner-border text-primary-custom" style="width: 3rem; height: 3rem;" role="status">
          <span class="visually-hidden">Đang tải...</span>
        </div>
      </div>

      <!-- Hiển thị khi đã tải xong NHƯNG không có đơn hàng -->
      <div v-else-if="!isLoading && orders.length === 0" class="text-center py-5 bg-white shadow-sm border-top border-4 border-danger-custom shadow-sm">
        <div class="py-5">
          <i class="ph ph-receipt text-secondary mb-3" style="font-size: 5rem; opacity: 0.2;"></i>
          <p class="fs-5 text-secondary mb-4" style="font-family: 'Playfair Display', serif;">Bạn chưa thực hiện đơn hàng nào.</p>
          <button @click="router.push('/products')" class="btn btn-primary-custom rounded-0 px-5 py-3 text-uppercase text-white shadow-sm">
            Khám phá bộ sưu tập ngay
          </button>
        </div>
      </div>

      <!-- Danh sách đơn hàng: Chỉ hiện khi có dữ liệu -->
      <div v-else class="order-list">
        <div class="card border-0 shadow-sm rounded-0 overflow-hidden mb-4" v-for="order in orders" :key="order.id">
          <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
              <span class="fw-bold text-primary-custom">#{{ order.order_code }}</span>
              <span class="text-muted small">|</span>
              <span class="text-muted small"><i class="ph ph-calendar-blank me-1"></i>{{ formatDate(order.created_at) }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span :class="['status-badge px-3 py-1 rounded-pill small fw-bold', getStatusClass(order.status)]">
                {{ translateStatus(order.status) }}
              </span>
            </div>
          </div>
          
          <div class="card-body p-4">
            <div class="row align-items-center">
              <div class="col-md-7">
                <!-- Preview các sản phẩm -->
                <div class="d-flex gap-2 mb-3 mb-md-0 overflow-hidden">
                  <div v-for="item in order.items?.slice(0, 3)" :key="item.id" class="product-preview-img border">
                    <img :src="getImageUrl(item.variant_image)" @error="handleImageError" class="w-100 h-100 object-fit-cover">
                  </div>
                  <div v-if="order.items?.length > 3" class="more-items-count d-flex align-items-center justify-content-center bg-light border text-muted small px-2">
                    +{{ order.items.length - 3 }}
                  </div>
                </div>
              </div>
              <div class="col-md-5 text-md-end">
                <p class="text-muted small mb-1">Tổng thanh toán</p>
                <h4 class="fw-bold text-primary-custom mb-3" style="font-family: 'Playfair Display', serif;">
                  {{ formatPrice(order.total_amount) }}
                </h4>
                <div class="d-flex justify-content-md-end gap-2">
                  <button @click="viewDetails(order.order_code)" class="btn btn-outline-primary-custom rounded-0 px-4 py-2 text-uppercase small fw-bold">
                    Chi tiết
                  </button>
                  <button v-if="order.status === 'pending'" @click="confirmCancel(order)" class="btn btn-link text-danger text-decoration-none small p-0 ms-2">
                    Hủy đơn
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Phân trang -->
        <nav v-if="pagination.last_page > 1" class="mt-5 d-flex justify-content-center">
          <ul class="pagination pagination-custom shadow-sm">
            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
              <a class="page-link rounded-0" href="#" @click.prevent="changePage(pagination.current_page - 1)">
                <i class="ph ph-caret-left"></i>
              </a>
            </li>
            <li v-for="page in pagination.last_page" :key="page" class="page-item" :class="{ active: pagination.current_page === page }">
              <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
            </li>
            <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
              <a class="page-link rounded-0" href="#" @click.prevent="changePage(pagination.current_page + 1)">
                <i class="ph ph-caret-right"></i>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import defaultPlaceholder from '@/assets/images/defaults/placeholder.png';

const router = useRouter();
const isLoading = ref(true);
const orders = ref([]);
const pagination = ref({
  current_page: 1,
  last_page: 1
});

// Định dạng tiền tệ
const formatPrice = (value) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(value || 0);
};

// Định dạng ngày
const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('vi-VN', {
    year: 'numeric', month: 'long', day: 'numeric'
  });
};

// Xử lý ảnh
const getImageUrl = (path) => {
  if (!path) return defaultPlaceholder;
  if (path.startsWith('http')) return path;
  return `/storage/${path}`;
};
const handleImageError = (e) => { e.target.src = defaultPlaceholder; };

// Phân loại màu trạng thái
const getStatusClass = (status) => {
  const map = {
    pending: 'bg-warning-custom',
    confirmed: 'bg-info-custom',
    processing: 'bg-primary-light',
    shipping: 'bg-primary-light',
    delivered: 'bg-success-custom',
    cancelled: 'bg-secondary-custom',
    returned: 'bg-dark-custom'
  };
  return map[status] || 'bg-secondary text-white';
};

// Dịch trạng thái
const translateStatus = (status) => {
  const map = {
    pending: 'Chờ xác nhận',
    confirmed: 'Đã xác nhận',
    processing: 'Đang xử lý',
    shipping: 'Đang giao hàng',
    delivered: 'Hoàn tất',
    cancelled: 'Đã hủy',
    returned: 'Hoàn trả'
  };
  return map[status] || status;
};

/**
 * Lấy dữ liệu từ API
 * Đảm bảo isLoading được tắt kể cả khi lỗi hoặc không có dữ liệu
 */
const fetchOrders = async (page = 1) => {
  isLoading.value = true;
  try {
    const response = await axios.get(`/api/client/orders?page=${page}`);
    // Kiểm tra cấu trúc dữ liệu trả về từ Laravel Paginate
    orders.value = response.data.data || [];
    pagination.value = {
      current_page: response.data.current_page || 1,
      last_page: response.data.last_page || 1
    };
  } catch (error) {
    console.error('Lỗi tải đơn hàng:', error);
    orders.value = []; // Reset về rỗng nếu lỗi để hiện thông báo trống thay vì loading mãi
  } finally {
    // Đợi một chút để trải nghiệm mượt hơn hoặc tắt ngay lập tức
    setTimeout(() => {
      isLoading.value = false;
    }, 300);
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchOrders(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const viewDetails = (code) => {
  router.push(`/orders/${code}`);
};

const confirmCancel = async (order) => {
  const reason = prompt('Vui lòng nhập lý do hủy đơn hàng của bạn (tối thiểu 10 ký tự):');
  if (reason && reason.length >= 10) {
    try {
      await axios.put(`/api/client/orders/${order.order_code}`, {
        action: 'cancel',
        cancel_reason: reason
      });
      alert('Đã hủy đơn hàng thành công.');
      fetchOrders(pagination.value.current_page);
    } catch (error) {
      alert(error.response?.data?.message || 'Không thể hủy đơn hàng.');
    }
  } else if (reason) {
    alert('Lý do hủy đơn quá ngắn.');
  }
};

onMounted(() => {
  fetchOrders();
});
</script>

<style scoped>
/* Màu sắc Custom Luxury */
.text-primary-custom { color: #9f273b !important; }
.text-gold { color: #e7ce7d !important; }
.bg-gold { background-color: #e7ce7d !important; }
.border-danger-custom { border-color: #cc1e2e !important; }

/* Trạng thái Badges */
.status-badge { font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase; border: 1px solid rgba(0,0,0,0.05); }
.bg-warning-custom { background-color: #fff9e6; color: #856404; }
.bg-info-custom { background-color: #e7f5ff; color: #004085; }
.bg-primary-light { background-color: #fff0f2; color: #9f273b; }
.bg-success-custom { background-color: #ebfaf0; color: #155724; }
.bg-secondary-custom { background-color: #f8f9fa; color: #6c757d; }
.bg-dark-custom { background-color: #343a40; color: #ffffff; }

/* Buttons */
.btn-primary-custom { background-color: #9f273b; border: none; transition: all 0.3s ease; }
.btn-primary-custom:hover { background-color: #cc1e2e; transform: translateY(-2px); }

.btn-outline-primary-custom { color: #9f273b; border: 1px solid #9f273b; transition: all 0.3s; }
.btn-outline-primary-custom:hover { background-color: #9f273b; color: white; }

/* Layout */
.order-history-wrapper { background-color: #fcfcfc; }
.summary-card { background-color: #9f273b; color: white; }

.product-preview-img { width: 60px; height: 60px; flex-shrink: 0; }
.more-items-count { width: 40px; height: 60px; }

/* Pagination */
.pagination-custom .page-link { color: #9f273b; border: 1px solid #dee2e6; margin: 0 2px; }
.pagination-custom .active .page-link { background-color: #9f273b; border-color: #9f273b; color: white; }
.pagination-custom .page-item.disabled .page-link { background-color: #f8f9fa; }

/* Logo */
.brand-logo { letter-spacing: 0.2rem; }

@media (max-width: 768px) {
  .page-title { font-size: 2rem !important; }
}
</style>