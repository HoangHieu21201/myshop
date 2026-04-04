<template>
  <div v-if="isOpen" class="custom-modal-backdrop" v-on:click.self="$emit('close')">
    <div class="custom-modal-content shadow-lg border-0 rounded-0 slide-up" style="max-width: 1100px;">

      <!-- Header -->
      <div class="modal-header-luxury p-4 p-md-4 d-flex justify-content-between align-items-center">
        <div>
          <h3 class="mb-1 fw-bold font-serif text-white tracking-wider">ĐƠN HÀNG #<span
              v-text="order?.order_code"></span></h3>
          <span class="text-light opacity-75"><i class="bi bi-calendar3 me-2"></i>Ngày đặt: <span
              v-text="formatDateTime(order?.created_at)"></span></span>
        </div>
        <button v-on:click="$emit('close')" class="btn-close-luxury"><i class="bi bi-x-lg"></i></button>
      </div>

      <div class="modal-body p-4 p-md-5 overflow-auto bg-light" style="max-height: 75vh;">

        <div class="bg-white p-4 shadow-sm border border-light-subtle mb-4 rounded">
          <h5 class="fw-bold mb-4 font-serif text-primary-custom d-flex align-items-center">
            <i class="bi bi-clock-history me-2"></i> TRẠNG THÁI ĐƠN HÀNG
          </h5>

          <div v-if="!['cancelled', 'returned'].includes(order?.status)"
            class="order-stepper-horizontal d-none d-md-flex mt-2 mb-2">
            <div v-for="(step, index) in orderSteps" :key="index" class="stepper-step"
              :class="{ 'completed': isStepCompleted(order?.status, step.value), 'active': order?.status === step.value }">
              <div class="step-icon-wrap">
                <div class="step-icon"><i :class="step.icon"></i></div>
              </div>
              <div class="step-text">
                <div class="step-title" v-text="step.label"></div>
                <div class="step-date" v-if="getStepDate(order, step.value)" v-text="getStepDate(order, step.value)">
                </div>
              </div>
            </div>
          </div>

          <ul v-if="!['cancelled', 'returned'].includes(order?.status)" class="timeline-vertical d-md-none mt-3">
            <li v-for="(h, idx) in order?.histories" :key="h.id" :class="{ 'latest': idx === 0 }">
              <div class="timeline-dot"></div>
              <div class="timeline-content">
                <h6 class="fw-bold mb-1" :class="idx === 0 ? 'text-primary-custom' : 'text-dark'"
                  v-text="translateStatus(h.new_status)"></h6>
                <div class="text-muted small mb-1" v-text="formatDateTime(h.created_at)"></div>
                <div v-if="h.note" class="fst-italic text-secondary small">"<span v-text="h.note"></span>"</div>
              </div>
            </li>
          </ul>

          <div v-if="['cancelled', 'returned'].includes(order?.status)"
            class="alert bg-light border border-light-subtle rounded-0 mb-0 d-flex align-items-center py-3 px-4">
            <i class="bi bi-x-circle-fill text-secondary me-3 fs-3"></i>
            <div>
              <strong class="text-dark d-block mb-1">Đơn hàng đã bị <span
                  v-text="order?.status === 'cancelled' ? 'Hủy' : 'Trả hàng'"></span>.</strong>
              <span class="text-muted small">Cập nhật lúc: <span v-text="getCancelTime(order)"></span></span>
            </div>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-lg-8">
            <div class="bg-white p-4 shadow-sm border border-light-subtle mb-4 rounded">
              <h5 class="fw-bold mb-4 font-serif text-primary-custom d-flex align-items-center">
                <i class="bi bi-bag-check me-2"></i> CHI TIẾT SẢN PHẨM
              </h5>

              <div class="table-responsive">
                <table class="table table-borderless align-middle">
                  <thead class="border-bottom text-uppercase small text-muted">
                    <tr>
                      <th class="ps-0">Sản phẩm</th>
                      <th class="text-center">Đơn giá</th>
                      <th class="text-center">SL</th>
                      <th class="text-end pe-0">Thành tiền</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in order?.items" :key="item.id" class="border-bottom">
                      <td class="ps-0 py-3">
                        <div class="d-flex align-items-center gap-3">
                          <div class="product-img border p-1"
                            style="width: 70px; height: 70px; background: #fff; flex-shrink: 0;">
                            <img :src="getImageUrl(item.variant_image)" v-on:error="handleImageError"
                              class="w-100 h-100 object-fit-cover">
                          </div>
                          <div>
                            <div class="fw-bold text-dark mb-1" v-text="item.product_name"></div>
                            <div v-if="item.combo_id" class="small text-muted">
                              <span class="badge bg-light text-dark border"><i
                                  class="bi bi-stars text-primary-custom me-1"></i> Combo (<span
                                  v-text="parseCombo(item.combo_selections).length"></span> món)</span>
                            </div>
                            <div v-else-if="item.variant_attributes" class="small text-muted">
                              <span v-for="(val, key) in parseAttributes(item.variant_attributes)" :key="key"
                                class="badge bg-light text-secondary border me-1 mb-1 fw-normal">
                                <span v-text="key"></span>: <strong class="text-dark" v-text="val"></strong>
                              </span>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="text-center text-muted text-nowrap" v-text="formatPrice(item.price)"></td>
                      <td class="text-center fw-bold" v-text="item.quantity || 1"></td>
                      <td class="text-end pe-0 fw-bold text-primary-custom text-nowrap"
                        v-text="formatPrice(item.total_price || (item.price * (item.quantity || 1)))"></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="bg-white p-4 shadow-sm border border-light-subtle rounded">
              <h5 class="fw-bold mb-4 font-serif text-primary-custom d-flex align-items-center">
                <i class="bi bi-credit-card me-2"></i> THANH TOÁN
              </h5>
              <div class="row align-items-center">
                <div class="col-md-5 col-lg-5 mb-4 mb-md-0">
                  <div class="bg-light p-3 rounded border border-light-subtle h-100">
                    <p class="text-muted small text-uppercase mb-1 tracking-wider fw-bold">Phương thức</p>
                    <p class="text-dark fw-bold mb-3 d-flex align-items-center">
                      <i class="bi bi-cash-coin fs-5 text-primary-custom me-2"></i> <span
                        v-text="translatePaymentMethod(order?.payment_method)"></span>
                    </p>
                    <p class="text-muted small text-uppercase mb-1 tracking-wider fw-bold">Trạng thái</p>
                    <span
                      :class="['badge px-3 py-2 rounded-pill fw-bold border', getPaymentStatusClass(order?.payment_status)]">
                      <i :class="getPaymentStatusIcon(order?.payment_status)" class="me-1"></i> <span
                        v-text="translatePaymentStatus(order?.payment_status)"></span>
                    </span>
                  </div>
                </div>

                <div class="col-md-7 col-lg-7 border-start-md ps-md-4">
                  <div class="d-flex justify-content-between mb-2"><span class="text-muted">Tạm tính:</span><span
                      class="fw-medium" v-text="formatPrice(order?.sub_total)"></span></div>
                  <div v-if="order?.discount_amount > 0" class="d-flex justify-content-between mb-2 text-success">
                    <span>Giảm giá <span v-if="order?.coupon_code" class="badge bg-success-subtle text-success ms-1"
                        v-text="order.coupon_code"></span>:</span>
                    <span class="fw-bold">- <span v-text="formatPrice(order?.discount_amount)"></span></span>
                  </div>
                  <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Phí vận chuyển:</span>
                    <span :class="order?.shipping_fee > 0 ? 'fw-medium' : 'text-success fw-bold'"
                      v-text="order?.shipping_fee > 0 ? formatPrice(order.shipping_fee) : 'Miễn phí'"></span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <span class="fw-bold text-dark text-uppercase">Tổng cộng:</span>
                    <h3 class="fw-bold text-primary-custom mb-0 font-serif" v-text="formatPrice(order?.total_amount)">
                    </h3>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="bg-white p-4 shadow-sm border border-light-subtle rounded h-100">
              <h5 class="fw-bold mb-4 font-serif text-primary-custom d-flex align-items-center">
                <i class="bi bi-person-lines-fill me-2"></i> GIAO HÀNG
              </h5>
              <div class="mb-4">
                <p class="text-muted small text-uppercase mb-1 tracking-wider fw-bold">Người nhận</p>
                <p class="fw-bold text-dark mb-0 fs-5" v-text="order?.customer_name"></p>
              </div>
              <div class="mb-4">
                <p class="text-muted small text-uppercase mb-1 tracking-wider fw-bold">Liên hệ</p>
                <p class="text-dark mb-1"><i class="bi bi-telephone text-muted me-2"></i><span
                    v-text="order?.customer_phone"></span></p>
                <p class="text-dark mb-0"><i class="bi bi-envelope text-muted me-2"></i><span
                    v-text="order?.customer_email || 'Không cung cấp'"></span></p>
              </div>
              <div class="mb-4">
                <p class="text-muted small text-uppercase mb-1 tracking-wider fw-bold">Địa chỉ</p>
                <p class="text-dark mb-0 lh-base" v-text="order?.customer_address"></p>
              </div>
              <div v-if="order?.order_note" class="pt-3 border-top">
                <p class="text-muted small text-uppercase mb-1 tracking-wider fw-bold">Ghi chú</p>
                <div class="bg-light p-3 border rounded text-dark fst-italic small">"<span
                    v-text="order?.order_note"></span>"</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- FOOTER THÔNG MINH (THÊM NÚT HỦY ĐƠN) -->
      <div
        class="modal-footer-luxury p-3 px-md-4 border-top bg-white d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex gap-2">
          <template v-if="order?.status === 'delivered' && (!order?.reviews || order?.reviews.length === 0)">
            <button v-on:click="$emit('open-review', order)"
              class="btn btn-outline-primary-custom rounded-0 px-3 px-md-4 fw-bold text-uppercase small">
              <i class="bi bi-star-fill me-1"></i> Đánh giá
            </button>
          </template>

          <!-- NÚT MUA LẠI: Đã xóa v-if để hiển thị ở mọi trạng thái -->
          <button v-on:click="$emit('reorder', order)"
            class="btn btn-primary-custom rounded-0 px-3 px-md-4 fw-bold text-uppercase small">
            <i class="bi bi-cart-plus me-1"></i> Mua lại
          </button>

          <!-- NÚT HỦY XUẤT HIỆN Ở ĐÂY NẾU TRẠNG THÁI LÀ PENDING -->
          <template v-if="order?.status === 'pending'">
            <button v-on:click="$emit('cancel-order', order)"
              class="btn btn-outline-danger rounded-0 px-3 px-md-4 fw-bold text-uppercase small">
              <i class="bi bi-x-circle me-1"></i> Hủy Đơn Hàng
            </button>
          </template>
          <button v-on:click="exportInvoice"
            class="btn btn-outline-success rounded-0 px-3 px-md-4 fw-bold text-uppercase small">
            <i class="bi bi-file-earmark-pdf me-1"></i> Xuất Hóa Đơn PDF
          </button>
        </div>
        <button v-on:click="$emit('close')"
          class="btn btn-dark rounded-0 px-4 px-md-5 fw-bold text-uppercase small flex-shrink-0">
          Đóng cửa sổ
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import defaultPlaceholder from '@/assets/images/defaults/placeholder.png';
import axios from 'axios';           // ← THÊM
import Swal from 'sweetalert2';      // ← THÊM

const props = defineProps({
  isOpen: Boolean,
  order: Object
});

// Emit thêm sự kiện 'reorder' ra component cha để xử lý tập trung
const emit = defineEmits(['close', 'cancel-order', 'open-review', 'reorder']);

const orderSteps = [
  { value: 'pending', label: 'Chờ xác nhận', icon: 'bi-receipt' },
  { value: 'confirmed', label: 'Đã xác nhận', icon: 'bi-box-seam' },
  { value: 'processing', label: 'Đang xử lý', icon: 'bi-gear' },
  { value: 'shipping', label: 'Đang giao', icon: 'bi-truck' },
  { value: 'delivered', label: 'Hoàn tất', icon: 'bi-check-circle-fill' }
];

const isStepCompleted = (currentStatus, stepValue) => {
  if (!currentStatus) return false;
  if (['cancelled', 'returned'].includes(currentStatus)) return false;
  const currentIdx = orderSteps.findIndex(s => s.value === currentStatus);
  const stepIdx = orderSteps.findIndex(s => s.value === stepValue);
  return currentIdx >= stepIdx;
};

const getStepDate = (order, stepValue) => {
  if (!order?.histories || !Array.isArray(order.histories)) return '';
  const history = order.histories.find(h => h.new_status === stepValue);
  if (history) {
    const d = new Date(history.created_at);
    return `${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')} ${d.getDate().toString().padStart(2, '0')}/${(d.getMonth() + 1).toString().padStart(2, '0')}`;
  }
  return '';
};

const getCancelTime = (order) => {
  if (!order?.histories || !Array.isArray(order.histories)) return 'N/A';
  const history = order.histories.find(h => ['cancelled', 'returned'].includes(h.new_status));
  return history ? formatDateTime(history.created_at) : 'N/A';
};

const formatPrice = (value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleString('vi-VN', {
    hour: '2-digit', minute: '2-digit', second: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric'
  });
};

const parseAttributes = (attr) => { if (!attr) return {}; if (typeof attr === 'object') return attr; try { return JSON.parse(attr); } catch { return {}; } };
const parseCombo = (combo) => { if (!combo) return []; if (typeof combo === 'object') return combo; try { return JSON.parse(combo); } catch { return []; } };

const getImageUrl = (path) => {
  if (!path) return defaultPlaceholder;
  if (path.startsWith('http')) return path;
  return `http://127.0.0.1:8000/storage/${path}`;
};
const handleImageError = (e) => { e.target.src = defaultPlaceholder; };

const translateStatus = (status) => ({
  pending: 'Chờ xác nhận', confirmed: 'Đã xác nhận', processing: 'Đang xử lý', shipping: 'Đang giao hàng',
  delivered: 'Hoàn tất', cancelled: 'Đã hủy', returned: 'Trả hàng/Hoàn tiền'
})[status] || status;

const translatePaymentMethod = (method) => ({
  'cod': 'Thanh toán khi nhận hàng (COD)', 'vnpay': 'Thanh toán qua VNPAY', 'momo': 'Thanh toán qua Ví MoMo', 'bank_transfer': 'Chuyển khoản ngân hàng'
})[method] || method || 'Không xác định';

const translatePaymentStatus = (status) => ({
  'unpaid': 'Chưa thanh toán', 'paid': 'Đã thanh toán', 'refunded': 'Đã hoàn tiền'
})[status] || status || 'Chưa thanh toán';

const getPaymentStatusClass = (status) => {
  if (status === 'paid') return 'bg-success-subtle text-success border-success-subtle';
  if (status === 'refunded') return 'bg-info-subtle text-info border-info-subtle';
  return 'bg-warning-subtle text-warning-emphasis border-warning-subtle';
};

const getPaymentStatusIcon = (status) => {
  if (status === 'paid') return 'bi-check-circle-fill';
  if (status === 'refunded') return 'bi-arrow-counterclockwise';
  return 'bi-hourglass-split';
};
// === HÀM XUẤT HÓA ĐƠN ĐÃ SỬA ===
const exportInvoice = async () => {
  if (!props.order?.order_code) {
    Swal.fire({
      icon: 'error',
      title: 'Lỗi',
      text: 'Không tìm thấy mã đơn hàng',
    });
    return;
  }

  const token = localStorage.getItem('auth_token');

  try {
    const res = await axios.get(
      `http://127.0.0.1:8000/api/client/orders/${props.order.order_code}/invoice`,
      {
        headers: {
          Authorization: token ? `Bearer ${token}` : '',
          Accept: 'application/pdf',
        },
        responseType: 'blob',
      }
    );

    // Tạo link tải file
    const blobUrl = window.URL.createObjectURL(res.data);
    const link = document.createElement('a');
    link.href = blobUrl;
    link.download = `hoa-don-${props.order.order_code}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(blobUrl);

  } catch (err) {
    console.error(err);
    Swal.fire({
      icon: 'error',
      title: 'Lỗi',
      text: err.response?.data?.message || 'Không thể tải hóa đơn. Vui lòng thử lại.',
    });
  }
};
</script>

<style scoped>
.text-primary-custom {
  color: #9f273b !important;
}

.font-serif {
  font-family: 'Playfair Display', serif;
}

.tracking-wider {
  letter-spacing: 0.1em;
}

.btn-primary-custom {
  background: #9f273b;
  border: 1px solid #9f273b;
  color: white;
  transition: 0.3s;
}

.btn-primary-custom:hover {
  background: #cc1e2e;
  border-color: #cc1e2e;
  color: white;
}

.btn-outline-primary-custom {
  color: #9f273b;
  border: 1px solid #9f273b;
  background: transparent;
  transition: 0.3s;
}

.btn-outline-primary-custom:hover {
  background: #9f273b;
  color: white;
}

.custom-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(3px);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.custom-modal-content {
  background: white;
  width: 100%;
  max-height: 95vh;
  position: relative;
  display: flex;
  flex-direction: column;
}

.modal-header-luxury {
  background: #9f273b;
  color: white;
  flex-shrink: 0;
}

.btn-close-luxury {
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  opacity: 0.8;
  transition: 0.3s;
}

.btn-close-luxury:hover {
  opacity: 1;
  transform: scale(1.2);
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(40px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.slide-up {
  animation: slideUp 0.3s ease-out;
}

.product-img {
  overflow: hidden;
  border-radius: 4px;
}

.order-stepper-horizontal {
  position: relative;
  justify-content: space-between;
  width: 100%;
  padding: 0 10px;
}

.order-stepper-horizontal::before {
  content: '';
  position: absolute;
  top: 18px;
  left: 40px;
  right: 40px;
  height: 2px;
  background-color: #e9ecef;
  z-index: 1;
}

.stepper-step {
  position: relative;
  z-index: 2;
  text-align: center;
  flex: 1;
}

.step-icon-wrap {
  display: flex;
  justify-content: center;
  margin-bottom: 8px;
}

.step-icon {
  width: 38px;
  height: 38px;
  background-color: #f8f9fa;
  color: #adb5bd;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  border: 3px solid #fff;
  transition: all 0.3s;
  box-shadow: 0 0 0 1px #dee2e6;
}

.stepper-step.completed .step-icon {
  background-color: #9f273b;
  color: #fff;
  border-color: #fff;
  box-shadow: none;
}

.stepper-step.active .step-icon {
  background-color: #9f273b;
  color: #fff;
  border-color: #fff;
  box-shadow: 0 0 0 4px rgba(159, 39, 59, 0.15);
}

.step-title {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #adb5bd;
  letter-spacing: 0.05em;
}

.stepper-step.completed .step-title,
.stepper-step.active .step-title {
  color: #9f273b;
}

.step-date {
  font-size: 0.7rem;
  color: #6c757d;
  margin-top: 2px;
}

.timeline-vertical {
  list-style: none;
  padding: 0;
  margin: 0;
  position: relative;
}

.timeline-vertical::before {
  content: '';
  position: absolute;
  top: 10px;
  bottom: 0;
  left: 6px;
  width: 2px;
  background: #e9ecef;
}

.timeline-vertical li {
  position: relative;
  padding-left: 25px;
  margin-bottom: 20px;
}

.timeline-vertical li:last-child {
  margin-bottom: 0;
}

.timeline-dot {
  position: absolute;
  left: 0;
  top: 4px;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: #fff;
  border: 2px solid #adb5bd;
  z-index: 2;
}

.timeline-vertical li.latest .timeline-dot {
  border-color: #9f273b;
  background: #9f273b;
  box-shadow: 0 0 0 3px rgba(159, 39, 59, 0.2);
}

@media (min-width: 768px) {
  .border-start-md {
    border-left: 1px solid #dee2e6;
  }
}
</style>