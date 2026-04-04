<template>
  <div class="admin-contact-page p-4">
    <!-- Tiêu đề & Thống kê nhanh -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="h3 font-serif text-dark mb-1">Quản Lý Yêu Cầu Liên Hệ</h2>
        <p class="text-secondary mb-0">Theo dõi và phản hồi thắc mắc từ khách hàng qua hệ thống SORA.</p>
      </div>
      <div class="d-flex gap-3">
        <div class="badge bg-warning text-dark px-3 py-2 rounded-3 shadow-sm border border-warning d-flex align-items-center">
          <i class="fa-solid fa-envelope-open-text me-2"></i> Chờ xử lý: {{ pendingCount }}
        </div>
        <div class="badge bg-success px-3 py-2 rounded-3 shadow-sm border border-success d-flex align-items-center">
          <i class="fa-solid fa-check-double me-2"></i> Đã giải quyết: {{ resolvedCount }}
        </div>
      </div>
    </div>

    <!-- Bảng danh sách liên hệ -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary">
              <tr>
                <th scope="col" class="ps-4 py-3 fw-medium">Khách Hàng</th>
                <th scope="col" class="py-3 fw-medium">Liên Hệ</th>
                <th scope="col" class="py-3 fw-medium" style="max-width: 300px;">Nội Dung Tóm Tắt</th>
                <th scope="col" class="py-3 fw-medium text-center">Trạng Thái</th>
                <th scope="col" class="py-3 fw-medium">Thời Gian</th>
                <th scope="col" class="pe-4 py-3 fw-medium text-end">Thao Tác</th>
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
                  Không có dữ liệu liên hệ nào.
                </td>
              </tr>
              <tr v-else v-for="contact in contacts" :key="contact.id" :class="{'bg-light-danger': contact.status === 'pending'}">
                <td class="ps-4 py-3">
                  <span class="fw-bold text-dark">{{ contact.fullname }}</span>
                </td>
                <td class="py-3">
                  <div class="small text-dark mb-1"><i class="fa-solid fa-phone text-muted me-1"></i> {{ contact.phone }}</div>
                  <div class="small text-muted"><i class="fa-solid fa-envelope text-muted me-1"></i> {{ contact.email }}</div>
                </td>
                <td class="py-3 text-truncate" style="max-width: 300px;">
                  <span class="text-secondary small">{{ contact.message }}</span>
                </td>
                <td class="py-3 text-center">
                  <span v-if="contact.status === 'pending'" class="badge bg-danger-subtle text-danger px-2 py-1 rounded-1">Chờ Xử Lý</span>
                  <span v-else class="badge bg-success-subtle text-success px-2 py-1 rounded-1">Đã Xử Lý</span>
                </td>
                <td class="py-3">
                  <span class="small text-secondary">{{ formatDate(contact.created_at) }}</span>
                </td>
                <td class="pe-4 py-3 text-end">
                  <button class="btn btn-sm btn-outline-primary me-2" @click="viewDetail(contact)">
                    <i class="fa-solid fa-eye"></i> Xem
                  </button>
                  <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(contact.id)">
                    <i class="fa-solid fa-trash-can"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <!-- Phân trang -->
      <div class="card-footer bg-white border-0 p-3 d-flex justify-content-end" v-if="pagination.last_page > 1">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item" :class="{'disabled': pagination.current_page === 1}">
            <button class="page-link text-danger" @click="fetchContacts(pagination.current_page - 1)">Trước</button>
          </li>
          <li class="page-item disabled"><span class="page-link text-dark">{{ pagination.current_page }} / {{ pagination.last_page }}</span></li>
          <li class="page-item" :class="{'disabled': pagination.current_page === pagination.last_page}">
            <button class="page-link text-danger" @click="fetchContacts(pagination.current_page + 1)">Sau</button>
          </li>
        </ul>
      </div>
    </div>

    <!-- MODAL XEM CHI TIẾT TIN NHẮN -->
    <div class="modal fade" id="contactDetailModal" tabindex="-1" aria-hidden="true" ref="detailModal">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" v-if="selectedContact">
          <div class="modal-header bg-light border-0">
            <h5 class="modal-title font-serif fw-bold text-dark">
              Chi Tiết Yêu Cầu #{{ selectedContact.id }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div class="row mb-3">
              <div class="col-sm-4 text-muted small">Khách Hàng:</div>
              <div class="col-sm-8 fw-bold">{{ selectedContact.fullname }}</div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted small">SĐT:</div>
              <div class="col-sm-8"><a :href="`tel:${selectedContact.phone}`" class="text-decoration-none text-danger">{{ selectedContact.phone }}</a></div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted small">Email:</div>
              <div class="col-sm-8"><a :href="`mailto:${selectedContact.email}`" class="text-decoration-none text-danger">{{ selectedContact.email }}</a></div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted small">Thời Gian Gửi:</div>
              <div class="col-sm-8">{{ formatDate(selectedContact.created_at) }}</div>
            </div>
            
            <div class="mt-4 p-3 bg-light rounded-3 border border-light">
              <div class="text-muted small mb-2 fw-bold text-uppercase">Nội Dung Lời Nhắn:</div>
              <p class="mb-0" style="white-space: pre-wrap; line-height: 1.6;">{{ selectedContact.message }}</p>
            </div>
          </div>
          <div class="modal-footer border-0 bg-light d-flex justify-content-between">
            <span v-if="selectedContact.status === 'pending'" class="text-danger fw-bold small"><i class="fa-solid fa-circle-exclamation me-1"></i> Chưa giải quyết</span>
            <span v-else class="text-success fw-bold small"><i class="fa-solid fa-circle-check me-1"></i> Đã giải quyết</span>
            
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
              <button v-if="selectedContact.status === 'pending'" type="button" class="btn btn-success" @click="markAsResolved(selectedContact.id)">
                Đánh dấu Đã Xử Lý
              </button>
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

// Lấy Token của Admin
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
    console.error(error);
    Swal.fire('Lỗi!', 'Không thể lấy dữ liệu liên hệ', 'error');
  } finally {
    isLoading.value = false;
  }
};

// Đếm thống kê nhanh (chỉ tính trên trang hiện tại để tham khảo nhanh)
const pendingCount = computed(() => contacts.value.filter(c => c.status === 'pending').length);
const resolvedCount = computed(() => contacts.value.filter(c => c.status === 'resolved').length);

// Format ngày giờ
const formatDate = (dateString) => {
  if (!dateString) return '';
  const d = new Date(dateString);
  return d.toLocaleString('vi-VN', { hour: '2-digit', minute:'2-digit', day: '2-digit', month: '2-digit', year: 'numeric' });
};

// Xem chi tiết (Mở Modal)
const viewDetail = (contact) => {
  selectedContact.value = contact;
  if (!bsModal) {
    bsModal = new Modal(document.getElementById('contactDetailModal'));
  }
  bsModal.show();
};

// Đánh dấu đã xử lý
const markAsResolved = async (id) => {
  try {
    const response = await axios.put(`${API_URL}/${id}/status`, { status: 'resolved' }, axiosConfig.value);
    if (response.data.status) {
      Swal.fire('Thành Công!', 'Đã đánh dấu liên hệ là Đã Xử Lý', 'success');
      bsModal.hide();
      fetchContacts(pagination.value.current_page); // Load lại danh sách
    }
  } catch (error) {
    Swal.fire('Lỗi!', 'Không thể cập nhật trạng thái', 'error');
  }
};

// Xóa yêu cầu liên hệ
const confirmDelete = (id) => {
  Swal.fire({
    title: 'Xóa yêu cầu này?',
    text: "Hành động này không thể hoàn tác!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Đồng ý xóa',
    cancelButtonText: 'Hủy'
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
.bg-light-danger {
  background-color: rgba(220, 53, 69, 0.03); /* Đỏ siêu nhạt để nhận biết dễ các đơn chờ xử lý */
}
.table > :not(caption) > * > * {
  border-bottom-color: #f1f1f1;
}
.text-truncate {
  display: inline-block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>