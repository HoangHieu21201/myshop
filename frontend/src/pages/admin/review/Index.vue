<template>
  <div class="review-index-wrapper pb-5 mb-5">
    
    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ThinkHub</h1>
      <p class="text-muted fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải đánh giá...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      <div class="row mb-4 align-items-center">
        <div class="col-md-6">
          <h3 class="fw-bold text-dark mb-0">Quản lý Đánh giá</h3>
          <p class="text-muted mb-0 mt-1">Kiểm duyệt và phản hồi ý kiến từ khách hàng</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end align-items-center gap-3 flex-wrap">
          <div class="border rounded px-3 py-1 bg-white shadow-sm text-muted small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>
          <button class="btn btn-brand btn-brand-solid px-4 py-2 fw-bold shadow-sm" @click="fetchReviews(1, true)">
            <i class="bi bi-arrow-clockwise me-1"></i> Làm mới
          </button>
        </div>
      </div>

      <div class="mb-4">
        <ul class="nav nav-underline border-bottom mb-2 pb-1" style="flex-wrap: wrap !important; gap: 8px;">
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'all' }" @click.prevent="switchTab('all')">
              <i class="bi bi-grid-fill me-2"></i> Tất cả
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'all'}">{{ statusCounts['all'] || 0 }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'pending' }" @click.prevent="switchTab('pending')">
              <i class="bi bi-hourglass-split me-2 text-warning"></i> Chờ duyệt
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'pending'}">{{ statusCounts['pending'] || 0 }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'approved' }" @click.prevent="switchTab('approved')">
              <i class="bi bi-check-circle-fill me-2 text-success"></i> Đã duyệt
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'approved'}">{{ statusCounts['approved'] || 0 }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'hidden' }" @click.prevent="switchTab('hidden')">
              <i class="bi bi-eye-slash-fill me-2 text-secondary"></i> Đã ẩn
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'hidden'}">{{ statusCounts['hidden'] || 0 }}</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
          <h6 class="fw-bold mb-0 text-dark d-flex align-items-center">
            <i class="bi bi-star-half text-warning me-2"></i> Danh sách Đánh giá
            <div v-if="isSilentLoading" class="spinner-border spinner-border-sm text-brand ms-2" role="status"></div>
          </h6>
          
          <div class="d-flex align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center bg-light px-3 py-1 rounded-pill border shadow-sm">
              <i class="bi bi-funnel text-brand me-2"></i>
              <select class="form-select form-select-sm border-0 bg-transparent fw-bold text-secondary p-0 pe-4 cursor-pointer shadow-none" style="width: 120px;" v-model="filters.rating" @change="fetchReviews(1, true)">
                <option value="">Lọc số sao</option>
                <option value="5">5 Sao</option>
                <option value="4">4 Sao</option>
                <option value="3">3 Sao</option>
                <option value="2">2 Sao</option>
                <option value="1">1 Sao</option>
              </select>
            </div>

            <div class="search-box position-relative" style="width: 250px; max-width: 100%;">
              <input type="text" class="form-control form-control-sm rounded-pill pe-5 shadow-sm bg-light border-0 py-2" v-model="searchQuery" placeholder="Tìm theo tên SP/Khách...">
              <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
            </div>
          </div>
        </div>
        
        <div class="card-body p-0 mt-2">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 1100px;">
              <thead class="bg-light">
                <tr>
                  <th class="py-3 px-4 text-secondary border-0" style="width: 25%;">Khách hàng & Nhận xét</th>
                  <th class="py-3 px-4 text-secondary border-0" style="width: 22%;">Mục đánh giá</th>
                  <th class="py-3 px-4 text-secondary border-0 text-center" style="width: 13%;">Đánh giá</th>
                  <th class="py-3 px-4 text-secondary border-0 text-center" style="width: 20%;">Trạng thái (Kiểm duyệt)</th>
                  <th class="py-3 px-4 text-secondary text-center border-0" style="width: 20%;">Thao tác</th>
                </tr>
              </thead>
              <tbody :class="{'pe-none': isSilentLoading}">
                <tr v-if="isLoading && !isSilentLoading">
                  <td colspan="5" class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-brand mb-2" role="status"></div>
                    <div class="small fw-semibold">Đang tải dữ liệu...</div>
                  </td>
                </tr>
                <tr v-else-if="displayReviews.length === 0">
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Không tìm thấy đánh giá nào.
                  </td>
                </tr>
                
                <tr v-else v-for="review in displayReviews" :key="review.id" :class="{'bg-light opacity-75': review.status === 'hidden'}">
                  
                  <td class="px-4 py-3 overflow-hidden">
                    <div class="d-flex align-items-start gap-3">
                      <div class="bg-secondary bg-opacity-10 text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold border shadow-sm flex-shrink-0" style="width: 40px; height: 40px;">
                        {{ review.user?.fullName?.charAt(0).toUpperCase() || 'U' }}
                      </div>
                      <div class="overflow-hidden">
                        <div class="fw-bold text-dark text-truncate">{{ review.user?.fullName || 'Khách vãng lai' }}</div>
                        <div class="text-muted small text-truncate mb-2">{{ review.user?.email || 'N/A' }}</div>
                        <div class="text-truncate small fst-italic text-dark bg-light px-2 py-1 rounded border" :title="review.comment">
                           "{{ review.comment || 'Không có bình luận' }}"
                        </div>
                      </div>
                    </div>
                  </td>

                  <td class="px-4">
                    <div class="d-flex align-items-center">
                      <div v-if="review.product" class="d-flex flex-column w-100 overflow-hidden">
                        <span class="badge bg-info text-white w-auto align-self-start mb-1 fw-semibold"><i class="bi bi-gem me-1"></i>Sản phẩm</span>
                        <span class="text-truncate fw-bold text-dark small" :title="review.product.name">{{ review.product.name }}</span>
                      </div>
                      <div v-else-if="review.combo" class="d-flex flex-column w-100 overflow-hidden">
                        <span class="badge bg-primary text-white w-auto align-self-start mb-1 fw-semibold"><i class="bi bi-stars me-1"></i>Combo</span>
                        <span class="text-truncate fw-bold text-dark small" :title="review.combo.name">{{ review.combo.name }}</span>
                      </div>
                    </div>
                  </td>

                  <td class="px-4 text-center">
                    <div class="text-warning fs-6 mb-1">
                      <i v-for="n in 5" :key="n" class="bi" :class="n <= review.rating ? 'bi-star-fill' : 'bi-star'"></i>
                    </div>
                    <div class="small text-muted">{{ formatDate(review.created_at) }}</div>
                  </td>

                  <td class="px-4 text-center">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                      <div class="d-flex align-items-center justify-content-center gap-1 w-100 flex-nowrap">
                        <select class="form-select form-select-sm border shadow-sm fw-semibold flex-shrink-0" 
                                style="width: 110px; font-size: 0.8rem; cursor: pointer;"
                                :class="getStatusSelectClass(review.localStatus || review.status)"
                                v-model="review.localStatus"
                                @change="checkStatusChange(review)"
                                :disabled="review.isUpdatingStatus">
                          <option value="pending">Chờ duyệt</option>
                          <option value="approved">Đã duyệt</option>
                          <option value="hidden">Đã ẩn</option>
                        </select>
                        
                        <div class="d-flex align-items-center justify-content-start flex-shrink-0" style="min-width: 55px; height: 28px;">
                          <div v-if="review.isUpdatingStatus" class="spinner-border text-brand ms-1" style="width: 1.1rem; height: 1.1rem; border-width: 0.15em;" role="status"></div>
                          <template v-else-if="review.isStatusChanged">
                            <button @click="saveReviewStatus(review)" class="btn btn-sm btn-success rounded-circle shadow-sm d-flex align-items-center justify-content-center ms-1" style="width: 24px; height: 24px; padding: 0;" title="Lưu">
                              <i class="bi bi-check-lg fw-bold" style="font-size: 0.7rem;"></i>
                            </button>
                            <button @click="cancelStatusChange(review)" class="btn btn-sm btn-light rounded-circle shadow-sm text-danger border d-flex align-items-center justify-content-center ms-1" style="width: 24px; height: 24px; padding: 0;" title="Hủy">
                              <i class="bi bi-x-lg fw-bold" style="font-size: 0.7rem;"></i>
                            </button>
                          </template>
                        </div>
                      </div>
                      
                      <div v-if="review.admin_reply" class="mt-2 text-success small fw-bold d-flex align-items-center">
                          <i class="bi bi-check-circle-fill me-1"></i> Đã phản hồi
                      </div>
                    </div>
                  </td>

                  <td class="px-4 text-center">
                    <button class="btn btn-sm btn-light text-brand shadow-sm border me-2 fw-bold" title="Xem chi tiết & Phản hồi" @click="openReplyModal(review)">
                      <i class="bi bi-reply-fill me-1"></i> Phản hồi
                    </button>
                    <button class="btn btn-sm btn-light text-danger shadow-sm border" @click="confirmDelete(review.id)" title="Xóa vĩnh viễn">
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2" v-if="pagination.last_page > 1 && !isLoading">
        <span class="text-muted small">Hiển thị trang {{ pagination.current_page }} / {{ pagination.last_page }}</span>
        <nav>
          <ul class="pagination pagination-sm mb-0 shadow-sm">
            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }"><button class="page-link text-brand" @click="fetchReviews(pagination.current_page - 1, true)"><i class="bi bi-chevron-left"></i></button></li>
            <li class="page-item" v-for="page in pagination.last_page" :key="page" :class="{ active: pagination.current_page === page }"><button class="page-link" :class="pagination.current_page === page ? 'bg-brand border-brand text-white' : 'text-dark'" @click="fetchReviews(page, true)">{{ page }}</button></li>
            <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }"><button class="page-link text-brand" @click="fetchReviews(pagination.current_page + 1, true)"><i class="bi bi-chevron-right"></i></button></li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- MODAL PHẢN HỒI ĐÁNH GIÁ -->
    <div class="modal fade" id="replyReviewModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="modal-header border-bottom pb-3 bg-light rounded-top-4">
            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-chat-quote-fill text-brand me-2"></i>Chi Tiết & Phản Hồi Đánh Giá</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          
          <div class="modal-body p-4 bg-white" v-if="selectedReview">
            <div class="row g-4">
              <div class="col-md-5 border-end">
                <h6 class="fw-bold text-muted text-uppercase mb-3 small"><i class="bi bi-person-badge me-2"></i>Thông tin Đánh giá</h6>
                
                <div class="mb-3 p-3 bg-light rounded border border-light-subtle">
                  <div class="fw-bold text-dark mb-1">{{ selectedReview.user?.fullName }}</div>
                  <div class="text-muted small mb-2">{{ selectedReview.user?.email }}</div>
                  
                  <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                     <div class="text-warning fs-5">
                       <i v-for="n in 5" :key="n" class="bi" :class="n <= selectedReview.rating ? 'bi-star-fill' : 'bi-star'"></i>
                     </div>
                     <span class="badge" :class="getStatusSelectClass(selectedReview.status).replace('bg-opacity-10', '')">{{ translateStatus(selectedReview.status) }}</span>
                  </div>
                </div>

                <div class="mb-3">
                  <span class="text-muted small fw-semibold d-block mb-1">Mục đánh giá:</span>
                  <div class="fw-bold text-dark text-truncate" :title="selectedReview.product ? selectedReview.product.name : (selectedReview.combo ? selectedReview.combo.name : '')">
                    {{ selectedReview.product ? selectedReview.product.name : (selectedReview.combo ? selectedReview.combo.name : '') }}
                  </div>
                </div>

                <div>
                  <span class="text-muted small fw-semibold d-block mb-1">Nội dung đánh giá:</span>
                  <div class="p-3 bg-warning bg-opacity-10 border border-warning rounded small text-dark fst-italic lh-base">
                    "{{ selectedReview.comment || 'Khách hàng không để lại nhận xét.' }}"
                  </div>
                  <div v-if="selectedReview.images && selectedReview.images.length" class="mt-3 d-flex gap-2 flex-wrap">
                    <img v-for="(img, idx) in selectedReview.images" :key="idx" :src="img" class="rounded border shadow-sm" style="width: 60px; height: 60px; object-fit: cover;">
                  </div>
                </div>
              </div>

              <div class="col-md-7">
                <h6 class="fw-bold text-muted text-uppercase mb-3 small"><i class="bi bi-reply-fill me-2"></i>Cửa hàng phản hồi</h6>
                
                <div class="mb-4">
                  <label class="form-label fw-semibold text-dark small">Nội dung trả lời khách hàng <span class="text-muted fw-normal">(Hiển thị công khai)</span></label>
                  <textarea class="form-control bg-light border-light-subtle rounded-3" rows="6" 
                            v-model="editForm.admin_reply" 
                            placeholder="Nhập nội dung phản hồi, cảm ơn hoặc xin lỗi khách hàng..."
                            :class="{'is-invalid': errors.admin_reply}"></textarea>
                  <div class="invalid-feedback" v-if="errors.admin_reply">{{ errors.admin_reply[0] }}</div>
                </div>

                <div class="alert alert-info border-0 bg-info bg-opacity-10 small mb-0">
                  <i class="bi bi-info-circle-fill me-2 text-info"></i>
                  Phản hồi của bạn sẽ hiển thị công khai ngay dưới đánh giá của khách hàng trên trang sản phẩm.
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer bg-light border-top-0 rounded-bottom-4">
             <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Đóng</button>
             <button type="button" class="btn btn-brand btn-brand-solid rounded-pill px-4 fw-bold shadow-sm" @click="saveReviewReply" :disabled="isSaving">
                <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-send-fill me-1"></i> Lưu Phản Hồi
             </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, onBeforeUnmount } from 'vue';
import { useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';

const route = useRoute();
const reviews = ref([]);
const systemModules = ref([]);
const currentPageLevel = ref(null);

const isLoading = ref(true);
const isFirstLoad = ref(true);
const isSilentLoading = ref(false);
const isSaving = ref(false);

const searchQuery = ref('');
const activeTab = ref('all');
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });

const filters = reactive({ rating: '' });

const selectedReview = ref(null);
let replyModalInstance = null;
const editForm = reactive({ admin_reply: '' });
const errors = ref({});

const statusCounts = ref({ all: 0, pending: 0, approved: 0, hidden: 0 });

const getHeaders = () => ({ 'Accept': 'application/json', 'Content-Type': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

const handleAxiosError = (e, defaultMsg = 'Lỗi hệ thống') => {
  if (e.response) {
    if (e.response.status === 401) {
      Swal.fire('Lỗi xác thực', 'Phiên đăng nhập đã hết hạn!', 'error');
    } else if (e.response.data && e.response.data.errors) {
      let errorHtml = '<ul class="text-start text-danger small mt-2" style="max-height: 200px; overflow-y: auto; padding-left: 20px;">';
      Object.values(e.response.data.errors).flat().forEach(msg => { errorHtml += `<li class="mb-1">${msg}</li>`; });
      errorHtml += '</ul>';
      Swal.fire({ title: 'Dữ liệu không hợp lệ', html: errorHtml, icon: 'error', confirmButtonColor: '#dc3545' });
    } else {
      Swal.fire('Lỗi', e.response.data.message || defaultMsg, 'error');
    }
  } else {
    Swal.fire('Lỗi', 'Mất kết nối Server', 'error');
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' });
};

const translateStatus = (s) => {
  const map = { 'pending': 'Chờ duyệt', 'approved': 'Đã duyệt', 'hidden': 'Đã ẩn' };
  return map[s] || s;
};

const getLevelColor = (level) => {
  if(!level) return 'bg-secondary';
  const l = parseInt(level);
  switch (l) {
    case 1: return 'bg-danger text-white border-danger shadow-sm';        
    case 2: return 'bg-warning text-dark border-warning';                  
    case 3: return 'bg-info text-dark border-info';                        
    case 4: return 'bg-primary bg-opacity-10 text-primary border-primary'; 
    case 5: return 'bg-success bg-opacity-10 text-success border-success'; 
    default: return 'bg-light text-secondary border-secondary'; 
  }
};

const getStatusSelectClass = (status) => {
  const map = { 
    'pending': 'text-warning border-warning bg-warning bg-opacity-10', 
    'approved': 'text-success border-success bg-success bg-opacity-10',
    'hidden': 'text-secondary border-secondary bg-secondary bg-opacity-10'
  }; 
  return map[status] || 'bg-light text-secondary'; 
};

const fetchCounts = async () => {
    try {
        const statuses = ['all', 'pending', 'approved', 'hidden'];
        const requests = statuses.map(status => {
            let url = `http://127.0.0.1:8000/api/admin/reviews?page=1`;
            if (status !== 'all') url += `&status=${status}`;
            return axios.get(url, { headers: getHeaders() }).then(res => res.data);
        });

        const results = await Promise.all(requests);
        statuses.forEach((status, index) => {
            if (results[index] && results[index].data) {
                statusCounts.value[status] = results[index].data.total;
            }
        });
    } catch (e) { console.error(e); }
};

const fetchReviews = async (page = 1, silent = false) => {
  if (silent) isSilentLoading.value = true;
  else if (!isFirstLoad.value) isLoading.value = true;

  try {
    let queryParams = new URLSearchParams({ page });
    if (activeTab.value !== 'all') queryParams.append('status', activeTab.value);
    if (filters.rating) queryParams.append('rating', filters.rating);

    const [resReviews, resModules] = await Promise.all([
      axios.get(`http://127.0.0.1:8000/api/admin/reviews?${queryParams.toString()}`, { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/admin/modules', { headers: getHeaders() })
    ]);

    const result = resReviews.data;
    const dataPayload = result.data.data ? result.data.data : result.data; 
    
    reviews.value = dataPayload.map(r => ({
      ...r, localStatus: r.status, isStatusChanged: false, isUpdatingStatus: false
    }));

    pagination.value = result.data.last_page ? {
        current_page: result.data.current_page,
        last_page: result.data.last_page,
        total: result.data.total
    } : pagination.value;

    systemModules.value = resModules.data.data;
    const currentModule = systemModules.value.find(m => m.module_code === (route.meta?.moduleCode || 'admin_products'));
    if (currentModule) currentPageLevel.value = currentModule.required_level;

  } catch (err) { 
    console.error('Lỗi khi tải dữ liệu', err); 
  } finally { 
    isLoading.value = false;
    isFirstLoad.value = false;
    isSilentLoading.value = false;
  }
};

const switchTab = (tabId) => { 
  activeTab.value = tabId; 
  fetchReviews(1, true); 
};

// ================= INLINE STATUS =================
const checkStatusChange = (review) => { review.isStatusChanged = (review.localStatus !== review.status); };
const cancelStatusChange = (review) => { review.localStatus = review.status; review.isStatusChanged = false; };

const saveReviewStatus = async (review) => {
  review.isUpdatingStatus = true;
  try {
    await axios.put(`http://127.0.0.1:8000/api/admin/reviews/${review.id}`, {
      status: review.localStatus,
      admin_reply: review.admin_reply
    }, { headers: getHeaders() });
    
    review.status = review.localStatus; 
    review.isStatusChanged = false;
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã cập nhật trạng thái', showConfirmButton: false, timer: 1500 });
    fetchCounts(); 
  } catch (error) { 
    cancelStatusChange(review); 
    handleAxiosError(error, 'Không thể cập nhật trạng thái');
  } finally { 
    review.isUpdatingStatus = false; 
  }
};

// ================= MODAL PHẢN HỒI =================
const openReplyModal = (review) => {
  selectedReview.value = review;
  editForm.admin_reply = review.admin_reply || '';
  errors.value = {};
  if (!replyModalInstance) replyModalInstance = new window.bootstrap.Modal(document.getElementById('replyReviewModal'));
  replyModalInstance.show();
};

const saveReviewReply = async () => {
  isSaving.value = true;
  errors.value = {};
  try {
    await axios.put(`http://127.0.0.1:8000/api/admin/reviews/${selectedReview.value.id}`, {
      status: selectedReview.value.status,
      admin_reply: editForm.admin_reply
    }, { headers: getHeaders() });
    
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã lưu phản hồi', showConfirmButton: false, timer: 1500 });
    replyModalInstance.hide();
    fetchReviews(pagination.value.current_page, true);
  } catch (error) {
    if (error.response && error.response.status === 422) {
        errors.value = error.response.data.errors || {};
    } else {
        handleAxiosError(error, 'Không thể lưu phản hồi');
    }
  } finally {
    isSaving.value = false;
  }
};

const confirmDelete = (id) => {
  Swal.fire({ title: 'Xóa vĩnh viễn?', text: `Đánh giá này sẽ bị xóa khỏi hệ thống.`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Đồng ý xóa' }).then(async (result) => {
    if (result.isConfirmed) {
      isSilentLoading.value = true;
      try {
        await axios.delete(`http://127.0.0.1:8000/api/admin/reviews/${id}`, { headers: getHeaders() });
        Swal.fire({icon: 'success', title: 'Đã xóa', timer: 1500, showConfirmButton: false});
        fetchReviews(pagination.value.current_page, true);
        fetchCounts();
      } catch(e) {
        isSilentLoading.value = false;
        handleAxiosError(e, 'Không thể xóa');
      }
    }
  });
};

const displayReviews = computed(() => {
  let result = reviews.value;
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(r => 
        (r.user?.fullName?.toLowerCase().includes(q)) || 
        (r.product?.name?.toLowerCase().includes(q)) ||
        (r.combo?.name?.toLowerCase().includes(q))
    );
  }
  return result;
});

onMounted(() => {
  fetchReviews();
  fetchCounts();
});

onBeforeUnmount(() => {
  if (replyModalInstance) replyModalInstance.hide();
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
});
</script>

<style scoped>
.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, #009981 30%, #4dffdf 50%, #009981 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }

.custom-tab { font-weight: 600 !important; color: #6c757d; border-bottom: 2px solid transparent !important; margin-bottom: -1px; transition: color 0.2s ease; }
.custom-tab:hover { color: #009981; }
.custom-tab.active-tab { color: #009981 !important; border-bottom: 2px solid #009981 !important; }
.tab-badge { font-size: 0.75rem; font-weight: 600; background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; transition: all 0.2s ease; }
.active-badge { background-color: #e6f5f2 !important; color: #009981 !important; border-color: #009981 !important; }

.bg-brand { background-color: #009981 !important; } .text-brand { color: #009981 !important; } .border-brand { border-color: #009981 !important; }
.btn-brand-solid { background-color: #009981 !important; color: white !important; transition: all 0.2s ease; border: none; }
.btn-brand-solid:hover:not(:disabled) { background-color: #007a67 !important; color: white !important; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.cursor-pointer { cursor: pointer; }
.transition-all { transition: all 0.3s ease; }
</style>