<template>
  <div class="admin-contact-page p-4 min-vh-100">
    <!-- Tiêu đề & Thống kê nhanh -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
      <div>
        <h2 class="h3 font-serif mb-2 fw-bold">Hộp Thư Khách Hàng</h2>
        <p class="text-secondary mb-0 small">Quản lý và phản hồi trực tiếp các yêu cầu từ trang Liên Hệ.</p>
      </div>
      <div class="d-flex gap-3">
        <div class="card shadow-sm border-0 border-start border-warning border-4 rounded-3 p-3 text-center" style="min-width: 140px;">
          <span class="text-muted small text-uppercase fw-bold mb-1" style="letter-spacing: 1px;">Chờ Xử Lý</span>
          <span class="fs-3 fw-bold text-warning">{{ pendingCount }}</span>
        </div>
        <div class="card shadow-sm border-0 border-start border-success border-4 rounded-3 p-3 text-center" style="min-width: 140px;">
          <span class="text-muted small text-uppercase fw-bold mb-1" style="letter-spacing: 1px;">Đã Xử Lý</span>
          <span class="fs-3 fw-bold text-success">{{ resolvedCount }}</span>
        </div>
      </div>
    </div>

    <!-- Bảng danh sách liên hệ -->
    <div class="card shadow-sm rounded-4 border-0 overflow-hidden">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 custom-table">
            <thead>
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
                  <div class="spinner-border text-primary" role="status"></div>
                </td>
              </tr>
              <tr v-else-if="contacts.length === 0">
                <td colspan="6" class="text-center py-5 text-muted">
                  <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="mb-3 d-block mx-auto opacity-50">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                  </svg>
                  Hộp thư trống. SORA chưa có yêu cầu mới nào.
                </td>
              </tr>
              <!-- Dùng table-warning của Bootstrap để tự động thích ứng Sáng/Tối cho dòng chưa xử lý -->
              <tr v-else v-for="contact in contacts" :key="contact.id" :class="{'table-warning': contact.status === 'pending'}">
                <td class="ps-4 py-3">
                  <div class="d-flex align-items-center">
                    <div class="avatar-circle text-white fw-bold me-3" style="background-color: var(--bs-primary, #9f273b);">
                      {{ contact.fullname.charAt(0).toUpperCase() }}
                    </div>
                    <span class="fw-bold">{{ contact.fullname }}</span>
                  </div>
                </td>
                <td class="py-3">
                  <div class="small fw-medium mb-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2 opacity-75 d-inline-block">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    {{ contact.phone }}
                  </div>
                  <div class="small text-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2 opacity-75 d-inline-block">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    {{ contact.email }}
                  </div>
                </td>
                <td class="py-3" style="max-width: 300px;">
                  <p class="mb-0 text-truncate opacity-75" :title="contact.message" style="font-size: 0.9rem;">{{ contact.message }}</p>
                </td>
                <td class="py-3 text-center">
                  <span v-if="contact.status === 'pending'" class="badge bg-warning text-dark px-3 py-2 rounded-pill d-inline-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-1">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Chờ xử lý
                  </span>
                  <span v-else class="badge bg-success text-white px-3 py-2 rounded-pill d-inline-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-1">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Đã trả lời
                  </span>
                </td>
                <td class="py-3">
                  <span class="small text-secondary">{{ formatDate(contact.created_at) }}</span>
                </td>
                <td class="pe-4 py-3 text-end">
                  <!-- Trả lại Icon Đẹp Mắt, Dùng Flexbox Để Căn Giữa Hoàn Hảo -->
                  <button class="btn btn-sm btn-outline-primary me-2 shadow-sm d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" title="Xem & Trả lời" @click="viewDetail(contact)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                    </svg>
                  </button>
                  <button class="btn btn-sm btn-outline-danger shadow-sm d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" title="Xóa" @click="confirmDelete(contact.id)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <!-- Phân trang -->
      <div class="card-footer border-top py-3 d-flex justify-content-between align-items-center" v-if="pagination.last_page > 1">
        <span class="text-muted small">Trang {{ pagination.current_page }} / {{ pagination.last_page }}</span>
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item" :class="{'disabled': pagination.current_page === 1}">
            <button class="page-link d-flex align-items-center justify-content-center" @click="fetchContacts(pagination.current_page - 1)">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
          </li>
          <li class="page-item" :class="{'disabled': pagination.current_page === pagination.last_page}">
            <button class="page-link d-flex align-items-center justify-content-center" @click="fetchContacts(pagination.current_page + 1)">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </li>
        </ul>
      </div>
    </div>

    <!-- ==========================================
         MODAL CHI TIẾT & GỬI EMAIL PHẢN HỒI
         ========================================== -->
    <div class="modal fade" id="contactDetailModal" tabindex="-1" aria-hidden="true" ref="detailModal">
      <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable"> 
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" v-if="selectedContact">
          
          <div class="modal-header border-bottom py-3">
            <h5 class="modal-title font-serif fw-bold d-flex align-items-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2 text-primary">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M12 11v-1" />
              </svg>
              Chi Tiết Yêu Cầu #{{ selectedContact.id }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          
          <div class="modal-body p-0 d-flex flex-column flex-md-row">
            
            <!-- CỘT TRÁI: THÔNG TIN KHÁCH GỬI -->
            <div class="col-12 col-md-5 p-4 border-end-md">
              <h6 class="text-uppercase fw-bold text-muted small mb-4 d-flex align-items-center" style="letter-spacing: 1px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Thông tin người gửi
              </h6>
              
              <div class="d-flex flex-column gap-3 mb-4">
                <div>
                  <span class="d-block text-muted small mb-1">Họ và Tên</span>
                  <strong class="fs-6">{{ selectedContact.fullname }}</strong>
                </div>
                <div>
                  <span class="d-block text-muted small mb-1">Số điện thoại</span>
                  <a :href="`tel:${selectedContact.phone}`" class="text-decoration-none fw-medium d-inline-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2 opacity-75">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    {{ selectedContact.phone }}
                  </a>
                </div>
                <div>
                  <span class="d-block text-muted small mb-1">Email liên hệ</span>
                  <a :href="`mailto:${selectedContact.email}`" class="text-decoration-none fw-medium d-inline-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2 opacity-75">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    {{ selectedContact.email }}
                  </a>
                </div>
                <div>
                  <span class="d-block text-muted small mb-1">Thời gian tiếp nhận</span>
                  <span class="d-inline-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2 text-muted">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ formatDate(selectedContact.created_at) }}
                  </span>
                </div>
              </div>

              <h6 class="text-uppercase fw-bold text-muted small mb-2" style="letter-spacing: 1px;">Nội dung lời nhắn:</h6>
              <div class="p-3 rounded-3 custom-msg-box custom-scrollbar" style="max-height: 180px; overflow-y: auto;">
                <p class="mb-0" style="white-space: pre-wrap; line-height: 1.6; font-size: 0.95rem;">{{ selectedContact.message }}</p>
              </div>
              
              <div class="mt-4 pt-3 border-top text-center" v-if="selectedContact.status === 'pending'">
                <button class="btn btn-outline-success btn-sm w-100 d-inline-flex align-items-center justify-content-center" @click="markAsResolved(selectedContact.id)">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                  Đánh dấu Đã xử lý
                </button>
              </div>
            </div>

            <!-- CỘT PHẢI: FORM SOẠN EMAIL TRẢ LỜI -->
            <div class="col-12 col-md-7 p-4 d-flex flex-column">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="text-uppercase fw-bold small mb-0 d-flex align-items-center" style="letter-spacing: 1px; color: var(--bs-primary, #9f273b);">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                  </svg>
                  Gửi Email Trả Lời
                </h6>
                <span v-if="selectedContact.status === 'resolved'" class="badge bg-success text-white px-2 py-1 d-inline-flex align-items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7M5 18l4 4L19 12" />
                  </svg>
                  Đã phản hồi
                </span>
              </div>
              
              <form @submit.prevent="sendReplyEmail" class="d-flex flex-column flex-grow-1">
                <div class="mb-3">
                  <label class="form-label small fw-medium">Chủ đề (Subject) <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" v-model="replyForm.subject" required>
                </div>
                
                <div class="mb-3 flex-grow-1 d-flex flex-column">
                  <label class="form-label small fw-medium">Nội dung phản hồi <span class="text-danger">*</span></label>
                  <textarea class="form-control custom-scrollbar flex-grow-1" v-model="replyForm.message" placeholder="Nhập câu trả lời của chuyên viên tại đây..." required style="resize: none; min-height: 150px;"></textarea>
                  <div class="form-text small mt-2 opacity-75 d-flex align-items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-1 mt-1 flex-shrink-0">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Email gửi đi sẽ được bọc trong giao diện HTML chuẩn của hệ thống.
                  </div>
                </div>

                <div class="text-end mt-auto pt-3 border-top">
                  <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Đóng</button>
                  <button type="submit" class="btn btn-primary px-4 shadow-sm d-inline-flex align-items-center" :disabled="isReplying">
                    <span v-if="isReplying" class="spinner-border spinner-border-sm me-2" role="status"></span>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="me-2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                    </svg>
                    Gửi Email
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
      fetchContacts(pagination.value.current_page);
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

// Đánh dấu đã xử lý
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
.font-serif {
  font-family: "Playfair Display", "Merriweather", serif;
}

/* Custom Table (Xóa background hover màu trắng để chuẩn Dark Mode) */
.custom-table th {
  border-bottom: 2px solid var(--bs-border-color);
}
.custom-table td {
  border-bottom: 1px solid var(--bs-border-color);
  vertical-align: middle;
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

/* Khối nội dung tin nhắn trong Modal */
.custom-msg-box {
  background-color: rgba(128, 128, 128, 0.08); /* Dùng rgba trong suốt để nổi bật ở cả 2 mode */
  border: 1px solid var(--bs-border-color);
}

/* Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(128, 128, 128, 0.3); 
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(128, 128, 128, 0.5); 
}

/* Border cột chia đôi màn hình trên Desktop */
@media (min-width: 768px) {
  .border-end-md {
    border-right: 1px solid var(--bs-border-color) !important;
  }
}
</style>