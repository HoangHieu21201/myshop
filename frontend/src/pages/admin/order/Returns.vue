<template>
  <div class="order-returns-wrapper pb-5 mb-5">
    
    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ThinkHub</h1>
      <p class="text-muted fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Tải dữ liệu hoàn trả...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      <div class="row mb-4 align-items-center">
        <div class="col-md-6">
          <h3 class="fw-bold text-dark mb-0">Xử lý Hàng Hoàn / Trả</h3>
          <p class="text-muted mb-0">Quản lý các đơn hàng bị khách trả lại và yêu cầu hoàn tiền</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end align-items-center gap-3">
          <button class="btn btn-light border shadow-sm fw-bold text-dark px-4 py-2" @click="fetchData(1, true)">
            <i class="bi bi-arrow-clockwise me-1"></i> Làm mới
          </button>
        </div>
      </div>

      <!-- TABS PHÂN LOẠI (THAY CHO DROPDOWN CŨ) -->
      <div class="mb-3">
        <ul class="nav nav-underline border-bottom mb-2 pb-1" style="flex-wrap: wrap !important; gap: 8px;">
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'all' }" @click.prevent="switchTab('all')">
              <i class="bi bi-grid-fill me-2"></i> Tất cả yêu cầu
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'all'}">{{ statusCounts['all'] || 0 }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'paid' }" @click.prevent="switchTab('paid')">
              <i class="bi bi-hourglass-split me-2 text-warning"></i> Chờ hoàn tiền
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'paid'}">{{ statusCounts['paid'] || 0 }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'refunded' }" @click.prevent="switchTab('refunded')">
              <i class="bi bi-check-circle-fill me-2 text-success"></i> Đã hoàn tiền
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'refunded'}">{{ statusCounts['refunded'] || 0 }}</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- BỘ LỌC HOÀN TRẢ -->
      <div class="d-flex flex-wrap gap-3 mb-4 align-items-center">
        <!-- Lọc Ngày tháng -->
        <div class="d-flex align-items-center bg-white px-3 py-1 rounded-pill border shadow-sm">
          <span class="text-muted small fw-semibold me-2"><i class="bi bi-calendar-range text-brand"></i> Lọc Ngày:</span>
          <input type="date" class="form-control form-control-sm border-0 bg-transparent fw-bold p-1" style="box-shadow: none; width: 130px;" v-model="filters.start_date" @change="fetchData(1, true)">
          <span class="mx-1 text-muted">-</span>
          <input type="date" class="form-control form-control-sm border-0 bg-transparent fw-bold p-1" style="box-shadow: none; width: 130px;" v-model="filters.end_date" @change="fetchData(1, true)">
        </div>
      </div>

      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <h6 class="fw-bold mb-0 text-dark d-flex align-items-center">
            <i class="bi bi-exclamation-triangle text-danger me-2"></i>Danh sách Đơn bị hoàn
            <div v-if="isSilentLoading" class="spinner-border spinner-border-sm text-brand ms-2" role="status"></div>
          </h6>
          <div class="search-box position-relative" style="width: 300px; max-width: 100%;">
            <input type="text" class="form-control rounded-pill pe-5 shadow-sm bg-light border-0" v-model="searchQuery" @keyup.enter="fetchData(1, true)" placeholder="Tìm Mã đơn, Tên KH, SĐT...">
            <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted cursor-pointer" @click="fetchData(1, true)"></i>
          </div>
        </div>
        
        <div class="card-body p-0 mt-2">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 1000px;">
              <thead class="bg-light">
                <tr>
                  <th class="py-3 px-4 text-secondary border-0" style="width: 20%;">Mã Đơn / Cập nhật</th>
                  <th class="py-3 px-4 text-secondary border-0" style="width: 25%;">Khách hàng</th>
                  <th class="py-3 px-4 text-secondary border-0 text-end" style="width: 15%;">Số tiền cần hoàn</th>
                  <th class="py-3 px-4 text-secondary border-0 text-center" style="width: 20%;">Trạng thái TT</th>
                  <th class="py-3 px-4 text-secondary text-center border-0" style="width: 20%;">Thao tác (Cấp quyền)</th>
                </tr>
              </thead>
              <tbody :class="{'opacity-50 pe-none': isSilentLoading}" style="transition: opacity 0.2s;">
                <tr v-if="orders.length === 0 && !isSilentLoading">
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="bi bi-shield-check fs-1 d-block mb-2 text-success opacity-50"></i>Tuyệt vời! Không tìm thấy yêu cầu hoàn tiền nào.
                  </td>
                </tr>
                <tr v-else v-for="order in displayedOrders" :key="order.id" class="bg-light">
                  
                  <td class="px-4 py-3">
                    <div class="fw-bold text-danger fs-6 mb-1 font-monospace cursor-pointer" @click="openQuickView(order.id)">{{ order.order_code }}</div>
                    <div class="text-muted small"><i class="bi bi-clock me-1"></i>{{ formatDateTime(order.updated_at) }}</div>
                  </td>
                  
                  <td class="px-4 overflow-hidden">
                    <div class="fw-bold text-dark text-truncate mb-1"><i class="bi bi-person-fill me-1 text-secondary"></i> {{ order.customer_name }}</div>
                    <div class="small text-muted text-truncate"><i class="bi bi-telephone-fill me-1"></i> {{ order.customer_phone }}</div>
                  </td>
                  
                  <td class="px-4 text-end">
                    <div class="fw-bold text-danger fs-5">{{ formatCurrency(order.total_amount) }}</div>
                    <div class="small text-muted mt-1">{{ order.payment_method }}</div>
                  </td>

                  <td class="px-4 text-center">
                    <div class="badge px-3 py-2 w-100 text-uppercase" :class="order.payment_status === 'refunded' ? 'bg-success text-white' : 'bg-warning text-dark border border-warning'">
                        <i class="bi" :class="order.payment_status === 'refunded' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill'"></i>
                        {{ order.payment_status === 'refunded' ? 'Đã hoàn tiền' : 'CHỜ HOÀN TIỀN' }}
                    </div>
                  </td>

                  <td class="px-4 text-center">
                    <div class="d-flex gap-2 justify-content-center">
                      <button class="btn btn-sm btn-light text-brand shadow-sm border" @click="openQuickView(order.id)" title="Xem chi tiết đơn hoàn">
                          <i class="bi bi-eye-fill"></i>
                      </button>
                      <button v-if="order.payment_status !== 'refunded'" class="btn btn-sm btn-danger shadow-sm fw-bold flex-grow-1" @click="processRefund(order)">
                          <i class="bi bi-arrow-return-left me-1"></i> Đã Hoàn
                      </button>
                      <div v-else class="text-success small fw-bold flex-grow-1 d-flex align-items-center justify-content-center border rounded bg-success bg-opacity-10"><i class="bi bi-check-all fs-5 me-1"></i> Xong</div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- SERVER-SIDE PAGINATION -->
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2" v-if="pagination.lastPage > 1 && !isTableLoading">
        <span class="text-muted small">Hiển thị trang {{ pagination.currentPage }} / {{ pagination.lastPage }} (Tổng: {{ pagination.total }} đơn)</span>
        <nav>
          <ul class="pagination pagination-sm mb-0 shadow-sm">
            <li class="page-item" :class="{ disabled: pagination.currentPage === 1 }"><button class="page-link text-brand" @click="fetchData(pagination.currentPage - 1, true)"><i class="bi bi-chevron-left"></i></button></li>
            <li class="page-item" v-for="page in pagination.lastPage" :key="page" :class="{ active: pagination.currentPage === page }"><button class="page-link" :class="pagination.currentPage === page ? 'bg-brand border-brand text-white' : 'text-dark'" @click="fetchData(page, true)">{{ page }}</button></li>
            <li class="page-item" :class="{ disabled: pagination.currentPage === pagination.lastPage }"><button class="page-link text-brand" @click="fetchData(pagination.currentPage + 1, true)"><i class="bi bi-chevron-right"></i></button></li>
          </ul>
        </nav>
      </div>
      
      <!-- MODAL QUICK VIEW: CHI TIẾT ĐƠN HÀNG HOÀN TRẢ -->
      <div class="modal fade" id="quickViewOrderModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-xl">
          <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom pb-3 bg-light rounded-top-4">
              <h5 class="fw-bold text-dark mb-0"><i class="bi bi-receipt text-danger me-2"></i>Chi Tiết Yêu Cầu Hoàn Trả <span class="text-danger font-monospace">{{ selectedOrder?.order_code }}</span></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4 bg-white" v-if="selectedOrder">
              <div class="row g-4">
                  <!-- THÔNG TIN CHUNG & KHÁCH HÀNG -->
                  <div class="col-lg-4 border-end">
                      <h6 class="fw-bold text-muted text-uppercase mb-3"><i class="bi bi-person-badge me-2"></i>Người yêu cầu</h6>
                      <div class="mb-2"><span class="text-muted">Họ tên:</span> <strong class="text-dark float-end">{{ selectedOrder.customer_name }}</strong></div>
                      <div class="mb-2"><span class="text-muted">Điện thoại:</span> <strong class="text-dark float-end">{{ selectedOrder.customer_phone }}</strong></div>
                      <div class="mb-3">
                          <span class="text-muted d-block mb-1">Địa chỉ nhận hàng:</span> 
                          <div class="p-2 bg-light border rounded small">{{ selectedOrder.customer_address }}</div>
                      </div>
                      <div class="mb-4">
                          <span class="text-muted d-block mb-1">Ghi chú lúc mua:</span> 
                          <div class="p-2 bg-warning bg-opacity-10 text-dark fw-medium border border-warning rounded small fst-italic">{{ selectedOrder.order_note || 'Không có ghi chú' }}</div>
                      </div>

                      <h6 class="fw-bold text-muted text-uppercase mb-3 border-top pt-4"><i class="bi bi-credit-card me-2"></i>Hoàn Trả Thanh Toán</h6>
                      <div class="mb-2 d-flex justify-content-between align-items-center">
                          <span class="text-muted">Phương thức:</span> 
                          <span class="badge bg-secondary text-uppercase">{{ selectedOrder.payment_method }}</span>
                      </div>
                      <div class="mb-2 d-flex justify-content-between align-items-center">
                          <span class="text-muted">Trạng thái TT:</span> 
                          <span class="badge" :class="getPaymentBadge(selectedOrder.payment_status)">{{ formatPaymentStatus(selectedOrder.payment_status) }}</span>
                      </div>
                  </div>

                  <!-- DANH SÁCH SẢN PHẨM MUA (SNAPSHOT) -->
                  <div class="col-lg-8">
                      <h6 class="fw-bold text-danger text-uppercase mb-3"><i class="bi bi-box-seam me-2"></i>Các mặt hàng cần hoàn tiền</h6>
                      <div class="table-responsive border rounded mb-4 border-danger border-opacity-25" style="max-height: 250px; overflow-y: auto;">
                          <table class="table table-hover align-middle mb-0 small">
                              <thead class="bg-light sticky-top">
                                  <tr>
                                      <th class="ps-3">Sản phẩm</th>
                                      <th class="text-center">Đơn giá</th>
                                      <th class="text-center">SL</th>
                                      <th class="text-end pe-3">Thành tiền</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  <tr v-for="item in selectedOrder.items" :key="item.id">
                                      <td class="ps-3 py-3">
                                          <div class="d-flex align-items-center gap-2">
                                              <img :src="item.variant_image ? `http://127.0.0.1:8000/storage/${item.variant_image}` : 'https://placehold.co/40'" class="rounded border" style="width: 40px; height: 40px; object-fit: cover;">
                                              <div>
                                                  <div class="fw-bold text-dark text-wrap" style="max-width: 250px;">{{ item.product_name }}</div>
                                                  <div class="text-muted" style="font-size: 0.7rem;">SKU: {{ item.variant_sku }}</div>
                                                  
                                                  <!-- ĐÃ BỔ SUNG: COMBO & ATTRIBUTES ĐỒNG BỘ VỚI ORDER INDEX -->
                                                  <div class="text-brand mt-1" style="font-size: 0.7rem;" v-if="item.combo_id">
                                                      <span class="badge bg-light text-dark border">
                                                          <i class="bi bi-stars text-brand me-1"></i> Combo ({{ parseCombo(item.combo_selections).length }} món)
                                                      </span>
                                                  </div>
                                                  <div class="text-brand mt-1" style="font-size: 0.7rem;" v-else-if="item.variant_attributes">
                                                      <span v-for="(val, key) in parseAttributes(item.variant_attributes)" :key="key" class="me-2 badge bg-light text-dark border">[{{ key }}: {{ val }}]</span>
                                                  </div>
                                              </div>
                                          </div>
                                      </td>
                                      <td class="text-center fw-medium">{{ formatCurrency(item.price) }}</td>
                                      <td class="text-center fw-bold text-brand">x{{ item.quantity }}</td>
                                      <td class="text-end pe-3 fw-bold text-danger">{{ formatCurrency(item.total_price) }}</td>
                                  </tr>
                              </tbody>
                          </table>
                      </div>

                      <!-- TỔNG KẾT TIỀN -->
                      <div class="row justify-content-end">
                          <div class="col-md-6">
                              <div class="bg-danger bg-opacity-10 p-3 rounded border border-danger border-opacity-25">
                                  <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Tạm tính hàng:</span> <strong class="text-dark">{{ formatCurrency(selectedOrder.sub_total) }}</strong></div>
                                  <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Phí giao hàng:</span> <strong class="text-dark">{{ formatCurrency(selectedOrder.shipping_fee) }}</strong></div>
                                  <div class="d-flex justify-content-between mb-2 small text-success"><span class="text-muted">Giảm giá <span v-if="selectedOrder.coupon_code">({{ selectedOrder.coupon_code }})</span>:</span> <strong>- {{ formatCurrency(selectedOrder.discount_amount) }}</strong></div>
                                  <div class="d-flex justify-content-between mt-3 pt-2 border-top border-danger border-opacity-25"><span class="fw-bold text-dark">SỐ TIỀN CẦN HOÀN:</span> <strong class="fs-5 text-danger">{{ formatCurrency(selectedOrder.total_amount) }}</strong></div>
                              </div>
                          </div>
                      </div>

                      <!-- LỊCH SỬ TRẠNG THÁI -->
                      <div class="mt-4">
                          <h6 class="fw-bold text-muted text-uppercase mb-3"><i class="bi bi-clock-history me-2"></i>Lịch sử cập nhật</h6>
                          <ul class="list-group list-group-flush border rounded custom-scrollbar-y" style="max-height: 200px; overflow-y: auto;">
                              <li class="list-group-item d-flex justify-content-between align-items-start bg-light" v-for="history in selectedOrder.histories" :key="history.id">
                                  <div class="ms-2 me-auto">
                                      <div class="fw-bold text-dark small">
                                          <span class="text-muted fw-normal me-1">{{ formatDateTime(history.created_at) }}</span>
                                          Chuyển sang <span class="badge ms-1" :class="getOrderStatusClass(history.new_status)">{{ formatOrderStatus(history.new_status) }}</span>
                                      </div>
                                      <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                          <i class="bi bi-person-fill me-1"></i>Bởi: <strong>{{ history.changer?.fullName || 'Hệ thống/Khách' }}</strong>
                                          <span v-if="history.note" class="ms-2 fst-italic text-danger fw-semibold">- "{{ history.note }}"</span>
                                      </div>
                                  </div>
                              </li>
                          </ul>
                      </div>

                  </div>
              </div>
            </div>

            <div class="modal-footer bg-light border-top-0 rounded-bottom-4 mt-3">
               <button type="button" class="btn btn-outline-brand rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Đóng</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';

const route = useRoute();
const orders = ref([]);
const systemModules = ref([]); 

const isFirstLoad = ref(true);
const isTableLoading = ref(false);
const isSilentLoading = ref(false);

const searchQuery = ref('');
const activeTab = ref('all');
const filters = ref({ 
    start_date: '',
    end_date: ''
});

const pagination = ref({ currentPage: 1, lastPage: 1, total: 0 });
const currentPageLevel = ref(null);

const selectedOrder = ref(null);
let quickViewModalInstance = null;
let isUnmounted = false;

const statusCounts = ref({
    all: 0, paid: 0, refunded: 0
});

const tabCache = ref({});

onBeforeUnmount(() => {
  isUnmounted = true;
  if (quickViewModalInstance) quickViewModalInstance.hide();
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.className = '';
  document.body.style = '';
});

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(val || 0);
const formatDate = (dateString) => { if (!dateString) return ''; const d = new Date(dateString); return d.toLocaleDateString('vi-VN'); };
const formatDateTime = (dateString) => { if (!dateString) return ''; const d = new Date(dateString); return d.toLocaleString('vi-VN'); };

const parseAttributes = (attr) => {
  if (!attr) return {};
  if (typeof attr === 'object') return attr;
  try { return JSON.parse(attr); } catch { return {}; }
};

const parseCombo = (combo) => {
  if (!combo) return [];
  if (typeof combo === 'object') return combo;
  try { return JSON.parse(combo); } catch { return []; }
};

const formatPaymentStatus = (status) => {
    const map = { 'unpaid': 'Chưa TT', 'paid': 'Đã TT', 'refunded': 'Đã hoàn', 'failed': 'Thất bại' };
    return map[status] || status;
};

const getPaymentBadge = (status) => {
    const map = { 'unpaid': 'bg-warning text-dark border-warning', 'paid': 'bg-success text-white border-success', 'refunded': 'bg-info text-dark border-info', 'failed': 'bg-danger text-white border-danger' };
    return map[status] || 'bg-secondary';
};

const formatOrderStatus = (status) => {
    const map = { 'pending': 'Chờ duyệt', 'confirmed': 'Đã xác nhận', 'processing': 'Đang chuẩn bị', 'shipping': 'Đang giao', 'delivered': 'Đã giao', 'cancelled': 'Đã hủy', 'returned': 'Hoàn trả' };
    return map[status] || status;
};

const getOrderStatusClass = (status) => {
  const map = { 
    'pending': 'text-warning border-warning bg-warning bg-opacity-10', 
    'confirmed': 'text-info border-info bg-info bg-opacity-10', 
    'processing': 'text-primary border-primary bg-primary bg-opacity-10', 
    'shipping': 'text-primary border-primary bg-primary bg-opacity-10', 
    'delivered': 'text-success border-success bg-success bg-opacity-10',
    'cancelled': 'text-danger border-danger bg-danger bg-opacity-10',
    'returned': 'text-secondary border-secondary bg-secondary bg-opacity-10'
  }; 
  return map[status] || 'bg-light text-secondary'; 
};

const processRefund = async (order) => {
  const { value: noteText, isDismissed } = await Swal.fire({
    title: 'Xác nhận Đã Hoàn Tiền',
    html: `Bạn xác nhận đã chuyển khoản trả lại số tiền <b>${formatCurrency(order.total_amount)}</b> cho khách hàng <b>${order.customer_name}</b>?`,
    input: 'text',
    inputPlaceholder: 'Mã giao dịch ngân hàng / Ghi chú...',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonText: 'Đóng',
    confirmButtonText: 'Đã hoàn tiền',
  });

  if (isDismissed) return;

  const payload = {
      status: 'returned', 
      payment_status: 'refunded', 
      note: noteText ? `Kế toán xác nhận Hoàn tiền: ${noteText}` : 'Kế toán xác nhận Hoàn tiền'
  };

  try {
    const res = await axios.put(`http://127.0.0.1:8000/api/admin/orders/${order.id}/status`, payload, { 
      headers: getHeaders() 
    });
    
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Hoàn tiền thành công', showConfirmButton: false, timer: 1500 });
    tabCache.value = {}; 
    fetchData(pagination.value.currentPage, true); 
    fetchCounts(); 
    
  } catch (error) { 
    if (error.response && error.response.status === 401) {
        Swal.fire('Lỗi xác thực', 'Phiên đăng nhập đã hết hạn!', 'error');
    } else {
        Swal.fire('Lỗi', 'Không thể cập nhật hệ thống', 'error');
    }
  }
};

const openQuickView = async (id) => {
  try {
    const res = await axios.get(`http://127.0.0.1:8000/api/admin/orders/${id}`, { headers: getHeaders() });
    if(!isUnmounted) {
      selectedOrder.value = res.data.data;
      if(!quickViewModalInstance) quickViewModalInstance = new window.bootstrap.Modal(document.getElementById('quickViewOrderModal'));
      quickViewModalInstance.show();
    }
  } catch(e){}
};

const fetchCounts = async () => {
    try {
        const tabs = ['all', 'paid', 'refunded'];
        const requests = tabs.map(tab => {
            let url = `http://127.0.0.1:8000/api/admin/orders?page=1&status=returned`;
            if (tab !== 'all') url += `&payment_status=${tab}`;
            return axios.get(url, { headers: getHeaders() }).then(res => res.data);
        });

        const results = await Promise.all(requests);
        tabs.forEach((tab, index) => {
            if (results[index] && results[index].data) {
                statusCounts.value[tab] = results[index].data.total;
            }
        });
    } catch (e) {}
};

const fetchData = async (page = 1, silent = false) => {
  const cacheKey = `${activeTab.value}_${page}_${filters.value.start_date}_${filters.value.end_date}`;

  if (tabCache.value[cacheKey]) {
      orders.value = tabCache.value[cacheKey].data;
      pagination.value = tabCache.value[cacheKey].pagination;
      isSilentLoading.value = true;
  } else {
      if (silent) isSilentLoading.value = true;
      else if (!isFirstLoad.value) isTableLoading.value = true;
  }
  
  let queryParams = new URLSearchParams({ page, status: 'returned' });
  if (activeTab.value !== 'all') queryParams.append('payment_status', activeTab.value);
  if (filters.value.start_date) queryParams.append('start_date', filters.value.start_date);
  if (filters.value.end_date) queryParams.append('end_date', filters.value.end_date);

  try {
    const [resOrders, resModules] = await Promise.all([
      axios.get(`http://127.0.0.1:8000/api/admin/orders?${queryParams.toString()}`, { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/admin/modules', { headers: getHeaders() })
    ]);

    if (isUnmounted) return;

    const sysModules = resModules.data.data;
    const currentModule = sysModules.find(m => m.module_code === (route.meta.moduleCode || 'admin_orders'));
    if (currentModule) currentPageLevel.value = currentModule.required_level;

    const result = resOrders.data;
    const dataPayload = result.data.data ? result.data.data : result.data; 
    
    const newPagination = result.data.last_page ? {
        currentPage: result.data.current_page,
        lastPage: result.data.last_page,
        total: result.data.total
    } : pagination.value;

    orders.value = dataPayload;
    pagination.value = newPagination;
    tabCache.value[cacheKey] = { data: dataPayload, pagination: newPagination };

  } catch (err) { 
      console.error(err);
  } finally { 
    if(!isUnmounted) {
      isFirstLoad.value = false;
      isTableLoading.value = false;
      isSilentLoading.value = false;
    }
  }
};

const switchTab = (tabId) => { 
    activeTab.value = tabId; 
    fetchData(1, true); 
};

const displayedOrders = computed(() => {
    let result = orders.value;
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        result = result.filter(o => 
            (o.order_code && o.order_code.toLowerCase().includes(q)) || 
            (o.customer_name && o.customer_name.toLowerCase().includes(q)) || 
            (o.customer_phone && o.customer_phone.includes(q))
        );
    }
    return result;
});

onMounted(() => {
    fetchData(1);
    fetchCounts();
});
</script>

<style scoped>
.custom-tab { font-weight: 600 !important; color: #6c757d; border-bottom: 2px solid transparent !important; margin-bottom: -1px; transition: color 0.2s ease; }
.custom-tab:hover { color: #009981; }
.custom-tab.active-tab { color: #009981 !important; border-bottom: 2px solid #009981 !important; }
.tab-badge { font-size: 0.75rem; font-weight: 600; background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; transition: all 0.2s ease; }
.active-badge { background-color: #e6f5f2 !important; color: #009981 !important; border-color: #009981 !important; }

.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, #009981 30%, #4dffdf 50%, #009981 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }
.bg-brand { background-color: #009981 !important; } .text-brand { color: #009981 !important; } .border-brand { border-color: #009981 !important; }
.cursor-pointer { cursor: pointer; }
.custom-scrollbar-y::-webkit-scrollbar { width: 4px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: #e0e0e0; border-radius: 10px; }
</style>