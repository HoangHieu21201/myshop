<template>
  <div class="order-history-wrapper pb-5" style="min-height: 100vh; font-family: 'Lato', sans-serif;">
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
            <li class="breadcrumb-item active">Đơn hàng</li>
          </ol>
        </nav>
      </div>

      <!-- Trạng thái Loading -->
      <div v-if="isLoading" class="d-flex justify-content-center align-items-center py-5">
        <div class="spinner-border text-primary-custom" style="width: 3rem; height: 3rem;"></div>
      </div>

      <!-- Hiển thị khi không có đơn hàng -->
      <div v-else-if="orders.length === 0" class="text-center py-5 bg-white shadow-sm border-top border-4 border-danger-custom">
        <div class="py-5">
          <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="#dee2e6" viewBox="0 0 256 256" class="mb-3"><path d="M216,40H40A16,16,0,0,0,24,56V200a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V56A16,16,0,0,0,216,40Zm0,160H40V56H216V200ZM184,96H72a8,8,0,0,1,0-16H184a8,8,0,0,1,0,16Zm0,32H72a8,8,0,0,1,0-16H184a8,8,0,0,1,0,16Zm-40,32H72a8,8,0,0,1,0-16h72a8,8,0,0,1,0,16Z"></path></svg>
          <p class="fs-5 text-secondary mb-4 font-serif">Bạn chưa thực hiện đơn hàng nào.</p>
          <button @click="router.push('/products')" class="btn btn-primary-custom rounded-0 px-5 py-3 text-uppercase text-white">Khám phá ngay</button>
        </div>
      </div>

      <!-- Danh sách đơn hàng từ API -->
      <div v-else class="order-list">
        <div class="card border-0 shadow-sm rounded-0 mb-4" v-for="order in orders" :key="order.id">
          <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
              <span class="fw-bold text-primary-custom">#{{ order.order_code }}</span>
              <span class="text-muted small">|</span>
              <span class="text-muted small">{{ formatDate(order.created_at) }}</span>
            </div>
            <span :class="['status-badge px-3 py-1 rounded-pill small fw-bold', getStatusClass(order.status)]">
              {{ translateStatus(order.status) }}
            </span>
          </div>
          
          <div class="card-body p-4">
            <div class="row align-items-center">
              <div class="col-md-8 border-end-md">
                <div v-for="item in order.items" :key="item.id" class="d-flex align-items-center gap-3 mb-3">
                  <img :src="getImageUrl(item.variant_image)" @error="handleImageError" class="product-preview-img border">
                  <div class="flex-grow-1">
                    <h6 class="mb-1 text-truncate">{{ item.product_name }}</h6>
                    <div class="d-flex justify-content-between">
                      <span class="small text-muted">{{ formatPrice(item.price) }} x {{ item.quantity }}</span>
                      <span class="fw-medium text-primary-custom">{{ formatPrice(item.price * item.quantity) }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4 text-md-end mt-4 mt-md-0">
                <p class="text-muted small mb-1 uppercase tracking-wider">Tổng thanh toán</p>
                <h3 class="fw-bold text-primary-custom mb-4 font-serif">{{ formatPrice(order.total_amount) }}</h3>
                <div class="d-flex flex-column gap-2">
                  <button @click="openDetails(order)" class="btn btn-primary-custom rounded-0 py-2 fw-bold small uppercase">Xem chi tiết</button>
                  <button v-if="order.status === 'pending'" @click="confirmCancel(order)" class="btn btn-outline-danger rounded-0 py-2 small fw-bold uppercase">Hủy đơn</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Phân trang động -->
        <nav v-if="pagination.last_page > 1" class="mt-5 d-flex justify-content-center">
          <ul class="pagination pagination-custom">
            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
              <a class="page-link" href="#" @click.prevent="changePage(1)">««</a>
            </li>
            <li v-for="page in pagination.last_page" :key="page" class="page-item" :class="{ active: pagination.current_page === page }">
              <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
            </li>
            <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
              <a class="page-link" href="#" @click.prevent="changePage(pagination.last_page)">»»</a>
            </li>
          </ul>
        </nav>
      </div>
    </main>

    <OrderDetailModal :is-open="isModalOpen" :order="selectedOrder" @close="closeModal" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import defaultPlaceholder from '@/assets/images/defaults/placeholder.png';
import OrderDetailModal from './OrderDetailModal.vue';

const router = useRouter();
const isLoading = ref(true);
const orders = ref([]);
const pagination = ref({ current_page: 1, last_page: 1 });
const isModalOpen = ref(false);
const selectedOrder = ref(null);

const formatPrice = (v) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v || 0);
const formatDate = (d) => d ? new Date(d).toLocaleString('vi-VN') : 'N/A';
const getImageUrl = (p) => p ? (p.startsWith('http') ? p : `/storage/${p}`) : defaultPlaceholder;
const handleImageError = (e) => { e.target.src = defaultPlaceholder; };

const getStatusClass = (s) => ({
  pending: 'bg-warning-custom', confirmed: 'bg-info-custom', delivered: 'bg-success-custom', cancelled: 'bg-secondary-custom'
}[s] || 'bg-secondary text-white');

const translateStatus = (s) => ({
  pending: 'Chờ xác nhận', confirmed: 'Đã xác nhận', delivered: 'Hoàn tất', cancelled: 'Đã hủy'
}[s] || s);

const fetchOrders = async (page = 1) => {
  isLoading.value = true;
  try {
    const res = await axios.get(`/api/client/orders?page=${page}`);
    orders.value = res.data.data || [];
    pagination.value = { current_page: res.data.current_page, last_page: res.data.last_page };
  } catch (err) {
    console.error(err);
    orders.value = [];
  } finally { isLoading.value = false; }
};

const openDetails = async (order) => {
  try {
    const res = await axios.get(`/api/client/orders/${order.order_code}`);
    selectedOrder.value = res.data.data;
    isModalOpen.value = true;
    document.body.style.overflow = 'hidden';
  } catch (err) { alert('Không thể lấy chi tiết đơn hàng'); }
};

const closeModal = () => {
  isModalOpen.value = false;
  document.body.style.overflow = 'auto';
};

const changePage = (p) => {
  if (p !== pagination.value.current_page) fetchOrders(p);
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const confirmCancel = async (order) => {
  const reason = prompt('Lý do hủy đơn (tối thiểu 10 ký tự):');
  if (reason && reason.length >= 10) {
    try {
      await axios.put(`/api/client/orders/${order.order_code}`, { action: 'cancel', cancel_reason: reason });
      alert('Đã hủy thành công');
      fetchOrders(pagination.value.current_page);
    } catch (err) { alert(err.response?.data?.message || 'Lỗi hủy đơn'); }
  }
};

onMounted(fetchOrders);
</script>

<style scoped>
.text-primary-custom { color: #9f273b !important; }
.btn-primary-custom { background: #9f273b; border: none; color: white; transition: 0.3s; }
.btn-primary-custom:hover { background: #cc1e2e; color: white; }
.bg-warning-custom { background: #fff9e6; color: #856404; }
.bg-success-custom { background: #ebfaf0; color: #155724; }
.bg-secondary-custom { background: #f8f9fa; color: #6c757d; }
.order-history-wrapper { background: #fcfcfc; }
.product-preview-img { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; }
.pagination-custom .page-link { color: #9f273b; border-radius: 50% !important; margin: 0 4px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; }
.pagination-custom .active .page-link { background: #9f273b; border-color: #9f273b; color: white; }
.font-serif { font-family: 'Playfair Display', serif; }
@media (min-width: 768px) { .border-end-md { border-right: 1px solid #f0f0f0; } }
</style>