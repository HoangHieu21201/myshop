<template>
  <div class="admin-contact-page p-4 bg-light min-vh-100">
    <!-- Tiêu đề & Thống kê nhanh -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
      <div>
        <h2 class="h3 font-serif text-dark mb-2 fw-bold">Hộp Thư Khách Hàng</h2>
        <p class="text-secondary mb-0 small">Quản lý và phản hồi trực tiếp các yêu cầu từ trang Liên Hệ.</p>
      </div>
      <div class="d-flex gap-3">
        <div class="stats-card bg-white shadow-sm border-start border-warning border-4 rounded-3 px-4 py-3 d-flex flex-column align-items-center" style="min-width: 140px;">
          <span class="text-muted small text-uppercase fw-bold mb-1" style="letter-spacing: 1px;">Chờ Xử Lý</span>
          <span class="fs-3 fw-bold text-warning">{{ pendingCount }}</span>
        </div>
        <div class="stats-card bg-white shadow-sm border-start border-success border-4 rounded-3 px-4 py-3 d-flex flex-column align-items-center" style="min-width: 140px;">
          <span class="text-muted small text-uppercase fw-bold mb-1" style="letter-spacing: 1px;">Đã Xử Lý</span>
          <span class="fs-3 fw-bold text-success">{{ resolvedCount }}</span>
        </div>
      </div>
    </div>

    <!-- Bảng danh sách liên hệ -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 custom-table">
            <thead class="bg-light">
              <tr>
                <th scope="col" class="ps-4 py-3 fw-medium text-muted text-uppercase small" style="letter-spacing: 0.5px;">Khách Hàng</th>
                <th scope="col" class="py-3 fw-medium text-muted text-uppercase small" style="letter-spacing: 0.5px;">Thông Tin</th>
                <th scope="col" class="py-3 fw-medium text-muted text-uppercase small" style="max-width: 300px; letter-spacing: 0.5px;">Nội Dung</th>
                <th scope="col" class="py-3 fw-medium text-muted text-uppercase small text-center" style="letter-spacing: 0.5px;">Trạng Thái</th>
                <th scope="col" class="py-3 fw-medium text-muted text-uppercase small" style="letter-spacing: 0.5px;">Thời Gian</th>
                <th scope="col" class="pe-4 py-3 fw-medium text-muted text-uppercase small text-end" style="letter-spacing: 0.5px;">Tác Vụ</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="isLoading">
                <td colspan="6" class="text-center py-5">
                  <div class="spinner-border text-danger" role="status"></div>
                </td>
              </tr>
              <tr v-else-if="contacts.length === 0">
                <td colspan="6" class="text-center py-5 text-muted">
                  <i class="fa-solid fa-inbox fs-1 mb-3 d-block" style="color: #e0e0e0;"></i>
                  Hộp thư trống. SORA chưa có yêu cầu mới nào.
                </td>
              </tr>
              <tr v-else v-for="contact in contacts" :key="contact.id" :class="{'bg-danger-light': contact.status === 'pending'}">
                <td class="ps-4 py-3">
                  <div class="d-flex align-items-center">
                    <div class="avatar-circle bg-main-light text-main fw-bold me-3">
                      {{ contact.fullname.charAt(0).toUpperCase() }}
                    </div>
                    <span class="fw-bold text-dark">{{ contact.fullname }}</span>
                  </div>
                </td>
                <td class="py-3">
                  <div class="small text-dark fw-medium mb-1"><i class="fa-solid fa-phone text-accent me-2"></i>{{ contact.phone }}</div>
                  <div class="small text-muted"><i class="fa-solid fa-envelope text-accent me-2"></i>{{ contact.email }}</div>
                </td>
                <td class="py-3" style="max-width: 300px;">
                  <p class="text-secondary small mb-0 text-truncate" :title="contact.message">{{ contact.message }}</p>
                </td>
                <td class="py-3 text-center">
                  <span v-if="contact.status === 'pending'" class="badge custom-badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="fa-regular fa-clock me-1"></i>Chờ xử lý</span>
                  <span v-else class="badge custom-badge bg-success-subtle text-success-emphasis border border-success-subtle"><i class="fa-solid fa-check me-1"></i>Đã trả lời</span>
                </td>
                <td class="py-3">
                  <span class="small text-secondary">{{ formatDate(contact.created_at) }}</span>
                </td>
                <td class="pe-4 py-3 text-end">
                  <button class="btn btn-sm btn-light border me-2 btn-action" title="Xem & Trả lời" @click="viewDetail(contact)">
                    <i class="fa-solid fa-reply text-main"></i>
                  </button>
                  <button class="btn btn-sm btn-light border btn-action" title="Xóa" @click="confirmDelete(contact.id)">
                    <i class="fa-solid fa-trash-can text-danger"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <!-- Phân trang -->
      <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center" v-if="pagination.last_page > 1">
        <span class="text-muted small">Trang {{ pagination.current_page }} / {{ pagination.last_page }}</span>
        <ul class="pagination pagination-sm mb-0 custom-pagination">
          <li class="page-item" :class="{'disabled': pagination.current_page === 1}">
            <button class="page-link" @click="fetchContacts(pagination.current_page - 1)"><i class="fa-solid fa-chevron-left"></i></button>
          </li>
          <li class="page-item" :class="{'disabled': pagination.current_page === pagination.last_page}">
            <button class="page-link" @click="fetchContacts(pagination.current_page + 1)"><i class="fa-solid fa-chevron-right"></i></button>
          </li>
        </ul>
      </div>
    </div>

    <!-- ==========================================
         MODAL CHI TIẾT & GỬI EMAIL PHẢN HỒI (CHIA 2 CỘT)
         ========================================== -->
    <div class="modal fade" id="contactDetailModal" tabindex="-1" aria-hidden="true" ref="detailModal">
      <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable"> <!-- Thêm scrollable để không bị tràn -->
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" v-if="selectedContact">
          
          <div class="modal-header bg-main text-white border-0 py-3">
            <h5 class="modal-title font-serif fw-bold">
              <i class="fa-solid fa-envelope-open-text me-2 text-accent"></i> Chi Tiết Yêu Cầu #{{ selectedContact.id }}
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          
          <div class="modal-body p-0 d-flex flex-column flex-md-row">
            
            <!-- CỘT TRÁI: THÔNG TIN KHÁCH GỬI -->
            <div class="col-12 col-md-5 bg-light p-4 border-end-md">
              <h6 class="text-uppercase fw-bold text-muted small mb-4" style="letter-spacing: 1px;">
                <i class="fa-solid fa-user me-2"></i>Thông tin người gửi
              </h6>
              
              <div class="d-flex flex-column gap-3 mb-4">
                <div>
                  <span class="d-block text-muted small mb-1">Họ và Tên</span>
                  <strong class="text-dark">{{ selectedContact.fullname }}</strong>
                </div>
                <div>
                  <span class="d-block text-muted small mb-1">Số điện thoại</span>
                  <a :href="`tel:${selectedContact.phone}`" class="text-decoration-none text-main fw-medium"><i class="fa-solid fa-phone me-2 small"></i>{{ selectedContact.phone }}</a>
                </div>
                <div>
                  <span class="d-block text-muted small mb-1">Email liên hệ</span>
                  <a :href="`mailto:${selectedContact.email}`" class="text-decoration-none text-main fw-medium"><i class="fa-solid fa-envelope me-2 small"></i>{{ selectedContact.email }}</a>
                </div>
                <div>
                  <span class="d-block text-muted small mb-1">Thời gian tiếp nhận</span>
                  <span class="text-dark"><i class="fa-regular fa-clock me-2 small text-muted"></i>{{ formatDate(selectedContact.created_at) }}</span>
                </div>
              </div>

              <h6 class="text-uppercase fw-bold text-muted small mb-2" style="letter-spacing: 1px;">Nội dung lời nhắn:</h6>
              <div class="p-3 bg-white rounded-3 border border-light custom-scrollbar" style="max-height: 180px; overflow-y: auto;">
                <p class="mb-0 text-secondary" style="white-space: pre-wrap; line-height: 1.6; font-size: 0.95rem;">{{ selectedContact.message }}</p>
              </div>
              
              <!-- Nút đổi trạng thái thủ công (Không gửi mail) -->
              <div class="mt-4 pt-3 border-top text-center" v-if="selectedContact.status === 'pending'">
                <button class="btn btn-outline-secondary btn-sm w-100" @click="markAsResolved(selectedContact.id)">
                  <i class="fa-solid fa-check me-1"></i> Chuyển trạng thái Đã xử lý (Không gửi mail)
                </button>
              </div>
            </div>

            <!-- CỘT PHẢI: FORM SOẠN EMAIL TRẢ LỜI -->
            <div class="col-12 col-md-7 p-4 bg-white d-flex flex-column">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="text-uppercase fw-bold text-main small mb-0" style="letter-spacing: 1px;">
                  <i class="fa-solid fa-paper-plane me-2"></i>Gửi Email Trả Lời
                </h6>
                <span v-if="selectedContact.status === 'resolved'" class="badge bg-success text-white"><i class="fa-solid fa-check-double me-1"></i>Đã hoàn tất</span>
              </div>
              
              <form @submit.prevent="sendReplyEmail" class="d-flex flex-column flex-grow-1">
                <div class="mb-3">
                  <label class="form-label small fw-medium text-dark">Chủ đề (Subject) <span class="text-danger">*</span></label>
                  <input type="text" class="form-control bg-light border-0" v-model="replyForm.subject" required>
                </div>
                
                <div class="mb-3 flex-grow-1 d-flex flex-column">
                  <label class="form-label small fw-medium text-dark">Nội dung phản hồi <span class="text-danger">*</span></label>
                  <textarea class="form-control bg-light border-0 custom-scrollbar flex-grow-1" v-model="replyForm.message" placeholder="Nhập câu trả lời của chuyên viên tại đây..." required style="resize: none; min-height: 150px;"></textarea>
                  <div class="form-text small text-muted mt-2">
                    <i class="fa-solid fa-circle-info text-accent me-1"></i> Email gửi đi sẽ được bọc trong giao diện chuẩn của thương hiệu SORA.
                  </div>
                </div>

                <div class="text-end mt-auto pt-3 border-top">
                  <button type="button" class="btn btn-light me-2 border" data-bs-dismiss="modal">Đóng cửa sổ</button>
                  <button type="submit" class="btn btn-main px-4 shadow-sm" :disabled="isReplying">
                    <span v-if="isReplying" class="spinner-border spinner-border-sm me-2" role="status"></span>
                    <span v-else><i class="fa-solid fa-reply-all me-2"></i></span> Gửi Email Ngay
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { Modal } from 'bootstrap'; 

const API_URL = 'http://localhost:8000/api/admin/contacts';

const contacts = ref([]);
const isLoading = ref(true);
const pagination = ref({ current_page: 1, last_page: 1 });

const selectedContact = ref(null);
let bsModal = null; 

// Form Reply
const replyForm = ref({
  subject: '',
  message: ''
});
const isReplying = ref(false);

const getAdminToken = () => {
  return localStorage.getItem('admin_token') || localStorage.getItem('token');
};

const axiosConfig = computed(() => ({
  headers: { 
    Authorization: `Bearer ${getAdminToken()}`,
    Accept: 'application/json' 
  }
}));

// Lấy danh sách
const fetchContacts = async (page = 1) => {
  isLoading.value = true;
  try {
    const response = await axios.get(`${API_URL}?page=${page}`, axiosConfig.value);
    if (response.data.status) {
      contacts.value = response.data.data.data;
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page
      };
    }
  } catch (error) {
    Swal.fire('Lỗi!', 'Không thể lấy dữ liệu liên hệ', 'error');
  } finally {
    isLoading.value = false;
  }
};

const pendingCount = computed(() => contacts.value.filter(c => c.status === 'pending').length);
const resolvedCount = computed(() => contacts.value.filter(c => c.status === 'resolved').length);

const formatDate = (dateString) => {
  if (!dateString) return '';
  const d = new Date(dateString);
  return d.toLocaleString('vi-VN', { hour: '2-digit', minute:'2-digit', day: '2-digit', month: '2-digit', year: 'numeric' });
};

// Mở Modal
const viewDetail = (contact) => {
  selectedContact.value = contact;
  // Cài đặt sẵn Tiêu đề mặc định
  replyForm.value.subject = `SORA Jewelry - Trả lời yêu cầu hỗ trợ của ${contact.fullname}`;
  replyForm.value.message = ''; 
  
  if (!bsModal) {
    bsModal = new Modal(document.getElementById('contactDetailModal'));
  }
  bsModal.show();
};

// GỬI EMAIL REPLY
const sendReplyEmail = async () => {
  isReplying.value = true;
  try {
    const response = await axios.post(`${API_URL}/${selectedContact.value.id}/reply`, replyForm.value, axiosConfig.value);
    
    if (response.data.status) {
      Swal.fire({ icon: 'success', title: 'Đã Gửi!', text: 'Email đã được gửi đến khách hàng.', confirmButtonColor: '#9f273b' });
      bsModal.hide();
      fetchContacts(pagination.value.current_page); // Load lại để ăn trạng thái Xanh
    }
  } catch (error) {
    let errorMsg = 'Có lỗi xảy ra khi gửi mail.';
    if (error.response && error.response.data && error.response.data.message) {
      errorMsg = error.response.data.message;
    }
    Swal.fire('Lỗi Gửi Mail', errorMsg, 'error');
  } finally {
    isReplying.value = false;
  }
};

// Đánh dấu đã xử lý (Thủ công)
const markAsResolved = async (id) => {
  try {
    const response = await axios.put(`${API_URL}/${id}/status`, { status: 'resolved' }, axiosConfig.value);
    if (response.data.status) {
      Swal.fire({ icon: 'success', title: 'Thành Công!', text: 'Đã chuyển trạng thái Đã Xử Lý', confirmButtonColor: '#9f273b' });
      bsModal.hide();
      fetchContacts(pagination.value.current_page);
    }
  } catch (error) {
    Swal.fire('Lỗi!', 'Không thể cập nhật trạng thái', 'error');
  }
};

// Xóa
const confirmDelete = (id) => {
  Swal.fire({
    title: 'Xóa yêu cầu này?',
    text: "Hành động này không thể hoàn tác!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Đồng ý xóa'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.delete(`${API_URL}/${id}`, axiosConfig.value);
        Swal.fire('Đã Xóa!', 'Yêu cầu đã bị xóa.', 'success');
        fetchContacts(pagination.value.current_page);
      } catch (error) {
        Swal.fire('Lỗi!', 'Không thể xóa yêu cầu này.', 'error');
      }
    }
  });
};

onMounted(() => {
  fetchContacts();
});
</script>

<style scoped>
/* Màu sắc thương hiệu Admin */
.bg-main { background-color: #9f273b !important; }
.text-main { color: #9f273b !important; }
.bg-main-light { background-color: rgba(159, 39, 59, 0.1) !important; }
.text-accent { color: #e7ce7d !important; }
.bg-danger-light { background-color: rgba(220, 53, 69, 0.04); }

.font-serif {
  font-family: "Playfair Display", "Merriweather", serif;
}

/* Custom Table */
.custom-table th {
  border-bottom: 2px solid #eaeaea;
}
.custom-table td {
  border-bottom: 1px solid #f9f9f9;
  vertical-align: middle;
}
.custom-table tbody tr:hover {
  background-color: #fff;
  box-shadow: 0 0 15px rgba(0,0,0,0.02);
}

/* Avatar Circle */
.avatar-circle {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

/* Badges */
.custom-badge {
  padding: 0.5rem 0.8rem;
  font-weight: 500;
  border-radius: 6px;
  letter-spacing: 0.5px;
}

/* Buttons */
.btn-action {
  width: 35px;
  height: 35px;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: all 0.2s ease;
}
.btn-action:hover {
  background-color: #f0f0f0;
  transform: translateY(-2px);
}
.btn-main {
  background-color: #9f273b;
  color: white;
  border: none;
  transition: all 0.3s ease;
}
.btn-main:hover:not(:disabled) {
  background-color: #cc1e2e;
  color: white;
  box-shadow: 0 4px 10px rgba(159, 39, 59, 0.3);
}

/* Pagination */
.custom-pagination .page-link {
  color: #9f273b;
  border: none;
  border-radius: 6px;
  margin: 0 2px;
}
.custom-pagination .page-link:hover:not(:disabled) {
  background-color: #f1f1f1;
}

/* Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f1f1; 
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #c1c1c1; 
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #9f273b; 
}

/* Border cho màn hình lớn */
@media (min-width: 768px) {
  .border-end-md {
    border-right: 1px solid #dee2e6 !important;
  }
}
</style>