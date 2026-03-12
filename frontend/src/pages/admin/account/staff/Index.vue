<template>
  <div class="staff-index-wrapper">
    
    <div class="container-fluid py-4" v-if="!isLoading">
      <div class="row mb-4 align-items-center">
        <div class="col-md-6">
          <h3 class="fw-bold text-dark mb-0">Quản lý Nhân sự (Nội bộ)</h3>
          <p class="text-muted mb-0">Quản lý tài khoản Ban quản trị và Nhân viên hệ thống ThinkHub</p>
        </div>
        
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end align-items-center gap-3">
          <div class="border rounded px-3 py-1 bg-white shadow-sm text-muted small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>

          <router-link :to="{ name: 'admin-staff-create' }" class="btn btn-brand px-4 py-2 fw-bold shadow-sm text-white">
            <i class="bi bi-person-plus-fill me-1"></i> Thêm Tài Khoản
          </router-link>
        </div>
      </div>

      <!-- TABS PHÂN LOẠI (Có thêm tab Thùng rác) -->
      <div class="mb-4">
        <div v-for="(row, rowIndex) in tabRows" :key="rowIndex" class="nav-tabs-row">
          <ul class="nav nav-underline border-bottom mb-2 flex-nowrap overflow-hidden">
            <li class="nav-item" v-for="tab in row" :key="tab.id">
              <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab"
                 href="#"
                 :class="{ 'active-tab': activeTab === tab.id }"
                 @click.prevent="switchTab(tab.id)">
                <i class="bi me-2" :class="getTabIcon(tab.id)"></i>
                {{ tab.name }}
                <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === tab.id}">
                  {{ tab.count }}
                </span>
              </a>
            </li>
          </ul>
        </div>
      </div>

      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-list-ul me-2"></i>Danh sách hiển thị</h6>
          <div class="search-box position-relative" style="width: 280px;">
            <input type="text" class="form-control rounded-pill pe-5 shadow-sm bg-light border-0" 
                   v-model="searchQuery" @input="currentPage = 1" placeholder="Tìm tên, email, SĐT...">
            <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
          </div>
        </div>

        <div class="card-body p-0 mt-2">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="py-3 px-4 text-secondary border-0">Nhân viên</th>
                  <th class="py-3 px-4 text-secondary border-0">Chức vụ (Role)</th>
                  <th class="py-3 px-4 text-secondary border-0">Thông tin liên hệ</th>
                  <th class="py-3 px-4 text-secondary border-0">Trạng thái</th>
                  <th class="py-3 px-4 text-secondary text-center border-0">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="paginatedStaff.length === 0">
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                    Không có dữ liệu trong danh sách này.
                  </td>
                </tr>
                <tr v-else v-for="staff in paginatedStaff" :key="staff.id" :class="{'bg-light opacity-75': staff.deleted_at, 'bg-light': staff.id === currentUserId && !staff.deleted_at}">
                  <td class="px-4 py-3">
                    <div class="d-flex align-items-center">
                      <img :src="getAvatarUrl(staff.avatar_url)" class="rounded-circle object-fit-cover me-3 border shadow-sm" style="width: 45px; height: 45px;">
                      <div>
                        <h6 class="mb-0 fw-bold text-dark">
                          {{ staff.fullname }} 
                          <span v-if="staff.id === currentUserId" class="badge bg-primary ms-1" style="font-size: 0.65rem;">(Bạn)</span>
                          <span v-if="staff.id === 1 && staff.id !== currentUserId" class="badge bg-danger ms-1" style="font-size: 0.65rem;">GỐC</span>
                        </h6>
                        <small class="text-muted">{{ staff.email }}</small>
                      </div>
                    </div>
                  </td>
                  <td class="px-4">
                    <span class="badge" :class="staff.role?.badgeClass || 'bg-secondary'">{{ staff.role?.label || 'Chưa gán' }}</span>
                  </td>
                  <td class="px-4">
                    <div class="text-dark fw-medium small mb-1"><i class="bi bi-telephone text-brand me-1"></i> {{ staff.phone || 'Chưa cập nhật' }}</div>
                    <div class="text-muted small text-truncate" style="max-width: 200px;" :title="staff.address">
                      <i class="bi bi-geo-alt text-brand me-1"></i> {{ staff.address || 'Chưa cập nhật' }}
                    </div>
                  </td>
                  <td class="px-4">
                    <span v-if="staff.deleted_at" class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary" title="Đã chuyển vào thùng rác">
                      <i class="bi bi-trash3-fill"></i> Đã xóa
                    </span>
                    <span v-else class="badge" :class="staff.status === 'active' ? 'bg-success bg-opacity-10 text-success border border-success' : 'bg-warning bg-opacity-10 text-warning border border-warning'">
                      <i class="bi" :class="staff.status === 'active' ? 'bi-check-circle-fill' : 'bi-lock-fill'"></i>
                      {{ staff.status === 'active' ? 'Hoạt động' : 'Bị Khóa' }}
                    </span>
                  </td>
                  <td class="px-4 text-center">
                    <!-- Nút Xem Nhanh -->
                    <button class="btn btn-sm btn-light text-info me-2 shadow-sm" title="Xem chi tiết" @click="openQuickView(staff)">
                      <i class="bi bi-eye"></i>
                    </button>

                    <!-- Nút Sửa/Xóa hoặc Khôi phục -->
                    <template v-if="!staff.deleted_at">
                      <router-link :to="{ name: 'admin-staff-edit', params: { id: staff.id } }" class="btn btn-sm btn-light text-primary me-2 shadow-sm">
                        <i class="bi bi-pencil-square"></i>
                      </router-link>
                      <button class="btn btn-sm btn-light text-danger shadow-sm" @click="confirmDelete(staff.id)" :disabled="staff.id === 1 || staff.id === currentUserId">
                        <i class="bi bi-trash"></i>
                      </button>
                    </template>
                    <template v-else>
                      <button class="btn btn-sm btn-light text-success shadow-sm" @click="restoreStaff(staff.id)" title="Khôi phục tài khoản">
                        <i class="bi bi-arrow-counterclockwise"></i> Khôi phục
                      </button>
                    </template>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center" v-if="totalPages > 1">
        <span class="text-muted small">
          Hiển thị {{ (currentPage - 1) * itemsPerPage + 1 }} đến {{ Math.min(currentPage * itemsPerPage, processedStaff.length) }}
        </span>
        <nav>
          <ul class="pagination pagination-sm mb-0 shadow-sm">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <button class="page-link text-brand" @click="currentPage--"><i class="bi bi-chevron-left"></i></button>
            </li>
            <li class="page-item" v-for="page in totalPages" :key="page" :class="{ active: currentPage === page }">
              <button class="page-link" :class="currentPage === page ? 'bg-brand border-brand text-white' : 'text-dark'" @click="currentPage = page">
                {{ page }}
              </button>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <button class="page-link text-brand" @click="currentPage++"><i class="bi bi-chevron-right"></i></button>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- HIỆU ỨNG LOGO SHIMMER (MÀN HÌNH CHỜ) -->
    <div v-else class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ThinkHub</h1>
      <p class="text-muted fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">
        Đang tải dữ liệu...
      </p>
    </div>

    <!-- POPUP (MODAL) XEM CHI TIẾT NHÂN VIÊN -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="fw-bold text-dark">Hồ sơ Nhân viên</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4 text-center" v-if="selectedStaff">
            <div class="position-relative d-inline-block mb-3">
              <img :src="getAvatarUrl(selectedStaff.avatar_url)" class="rounded-circle shadow-sm border border-2 border-white object-fit-cover" style="width: 110px; height: 110px;">
              <span v-if="!selectedStaff.deleted_at" class="position-absolute bottom-0 end-0 p-2 border border-light rounded-circle" :class="selectedStaff.status === 'active' ? 'bg-success' : 'bg-warning'" style="width: 15px; height: 15px;"></span>
              <span v-else class="position-absolute bottom-0 end-0 p-2 border border-light rounded-circle bg-secondary" style="width: 15px; height: 15px;"></span>
            </div>
            
            <h5 class="fw-bold mb-1">{{ selectedStaff.fullname }}</h5>
            <p class="text-muted small mb-2">{{ selectedStaff.email }}</p>
            <span class="badge mb-4" :class="selectedStaff.role?.badgeClass || 'bg-secondary'">{{ selectedStaff.role?.label || 'Chưa gán quyền' }}</span>
            
            <div class="text-start bg-light p-3 rounded-4 shadow-sm border border-light-subtle">
              <div class="row mb-2">
                <div class="col-5 text-muted fw-semibold"><i class="bi bi-telephone text-brand me-2"></i>SĐT:</div>
                <div class="col-7 fw-bold text-dark">{{ selectedStaff.phone || 'Chưa cập nhật' }}</div>
              </div>
              <div class="row mb-2">
                <div class="col-5 text-muted fw-semibold"><i class="bi bi-geo-alt text-brand me-2"></i>Địa chỉ:</div>
                <div class="col-7 text-dark">{{ selectedStaff.address || 'Chưa cập nhật' }}</div>
              </div>
              <div class="row mb-2">
                <div class="col-5 text-muted fw-semibold"><i class="bi bi-clock-history text-brand me-2"></i>Ngày tạo:</div>
                <div class="col-7 text-dark">{{ formatDateTime(selectedStaff.created_at) }}</div>
              </div>
              <!-- Thêm thông tin ngày xóa nếu tài khoản đang trong thùng rác -->
              <div class="row" v-if="selectedStaff.deleted_at">
                <div class="col-5 text-muted fw-semibold"><i class="bi bi-trash3 text-danger me-2"></i>Đã xóa lúc:</div>
                <div class="col-7 text-danger fw-semibold">{{ formatDateTime(selectedStaff.deleted_at) }}</div>
              </div>
            </div>
            
            <div class="mt-4" v-if="!selectedStaff.deleted_at">
              <router-link :to="{ name: 'admin-staff-edit', params: { id: selectedStaff.id } }" class="btn btn-outline-brand rounded-pill px-4" data-bs-dismiss="modal">
                <i class="bi bi-pencil-square me-1"></i> Chỉnh sửa thông tin
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import defaultAvatar from '../../../../assets/images/defaults/avatar1.png';

const route = useRoute();
const staffs = ref([]);
const roles = ref([]);
const systemModules = ref([]);
const isLoading = ref(true);
const searchQuery = ref('');
const activeTab = ref('all');
const currentPageLevel = ref(null);

const currentAdmin = JSON.parse(localStorage.getItem('admin_info') || '{}');
const currentUserId = currentAdmin.id;

const currentPage = ref(1);
const itemsPerPage = 8; 

// State cho Popup Quick View
const selectedStaff = ref(null);
let quickViewModalInstance = null;

const getHeaders = () => ({
  'Accept': 'application/json',
  'Authorization': `Bearer ${localStorage.getItem('admin_token')}`
});

const getAvatarUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : defaultAvatar;

const formatDateTime = (dateString) => {
  if(!dateString) return '';
  const d = new Date(dateString);
  return `${d.toLocaleDateString('vi-VN')} ${d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
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

const getTabIcon = (tabId) => {
  if (tabId === 'all') return 'bi-people-fill';
  if (tabId === 'locked') return 'bi-lock-fill text-warning';
  if (tabId === 'deleted') return 'bi-trash3-fill text-danger';
  return 'bi-person-badge text-primary';
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const [resStaff, resRole, resModules] = await Promise.all([
      fetch('http://127.0.0.1:8000/api/admin/staff', { headers: getHeaders() }),
      fetch('http://127.0.0.1:8000/api/admin/roles', { headers: getHeaders() }),
      fetch('http://127.0.0.1:8000/api/admin/modules', { headers: getHeaders() })
    ]);
    
    if (resStaff.ok) staffs.value = (await resStaff.json()).data;
    if (resRole.ok) roles.value = (await resRole.json()).data;
    
    if (resModules.ok) {
      systemModules.value = (await resModules.json()).data;
      const currentCode = route.meta.moduleCode;
      if (currentCode) {
        const currentModule = systemModules.value.find(m => m.module_code === currentCode);
        if (currentModule) currentPageLevel.value = currentModule.required_level;
      }
    }
  } catch (err) { 
    console.error(err); 
  } finally { 
    isLoading.value = false; 
  }
};

// Hàm mở Popup Xem chi tiết
const openQuickView = (staff) => {
  selectedStaff.value = staff;
  if (!quickViewModalInstance) {
    quickViewModalInstance = new window.bootstrap.Modal(document.getElementById('quickViewModal'));
  }
  quickViewModalInstance.show();
};

const tabRows = computed(() => {
  const rows = [];
  
  // Tách các role tab thành dòng thứ 2 trở đi
  const allRoleTabs = roles.value.map(r => ({
    id: `role_${r.id}`,
    name: r.label,
    count: staffs.value.filter(s => s.role_id === r.id && !s.deleted_at).length
  }));

  let roleIndex = 0;

  // Dòng 1: Các tab trạng thái chung (Đã cập nhật đếm số lượng loại trừ user đã xóa)
  const row1 = [
    { id: 'all', name: 'Tất cả', count: staffs.value.filter(s => !s.deleted_at).length },
    { id: 'locked', name: 'Bị khóa', count: staffs.value.filter(s => s.status === 'locked' && !s.deleted_at).length },
    { id: 'deleted', name: 'Thùng rác', count: staffs.value.filter(s => s.deleted_at).length }
  ];
  
  while (row1.length < 5 && roleIndex < allRoleTabs.length) {
    row1.push(allRoleTabs[roleIndex++]);
  }
  rows.push(row1);

  // Các dòng Role Tab còn lại (Mỗi dòng max 5 tabs)
  while (roleIndex < allRoleTabs.length) {
    const nextRow = [];
    while (nextRow.length < 5 && roleIndex < allRoleTabs.length) {
      nextRow.push(allRoleTabs[roleIndex++]);
    }
    rows.push(nextRow);
  }

  return rows;
});

const switchTab = (tabId) => {
  activeTab.value = tabId;
  currentPage.value = 1; 
};

const processedStaff = computed(() => {
  let result = staffs.value;

  // Lọc theo Tab
  if (activeTab.value === 'deleted') {
    result = result.filter(s => s.deleted_at);
  } else {
    // Loại trừ các account đã xóa khỏi các tab khác
    result = result.filter(s => !s.deleted_at);

    if (activeTab.value === 'locked') {
      result = result.filter(s => s.status === 'locked');
    } else if (activeTab.value.startsWith('role_')) {
      const roleId = parseInt(activeTab.value.split('_')[1]);
      result = result.filter(s => s.role_id === roleId);
    }
  }

  // Lọc theo từ khóa tìm kiếm
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(s => 
      s.fullname.toLowerCase().includes(q) || 
      s.email.toLowerCase().includes(q) || 
      (s.phone && s.phone.includes(q)) ||
      (s.address && s.address.toLowerCase().includes(q))
    );
  }

  return result.sort((a, b) => {
    if (a.id === currentUserId) return -1;
    if (b.id === currentUserId) return 1;
    return b.id - a.id;
  });
});

const totalPages = computed(() => Math.ceil(processedStaff.value.length / itemsPerPage) || 1);

const paginatedStaff = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return processedStaff.value.slice(start, start + itemsPerPage);
});

// Xóa vào thùng rác
const confirmDelete = (id) => {
  Swal.fire({
    title: 'Đưa vào thùng rác?', text: "Nhân viên này sẽ bị vô hiệu hóa và chuyển vào thùng rác!", icon: 'warning',
    showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Đồng ý xóa'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const res = await fetch(`http://127.0.0.1:8000/api/admin/staff/${id}`, { method: 'DELETE', headers: getHeaders() });
        const data = await res.json();
        if (res.ok) { 
          Swal.fire('Đã xóa', data.message, 'success'); 
          fetchData(); 
        } else {
          Swal.fire('Lỗi', data.message, 'error');
        }
      } catch (err) { Swal.fire('Lỗi', 'Mất kết nối', 'error'); }
    }
  });
};

// Khôi phục từ thùng rác
const restoreStaff = (id) => {
  Swal.fire({
    title: 'Khôi phục tài khoản?',
    text: "Tài khoản này sẽ hoạt động trở lại bình thường.",
    icon: 'info',
    showCancelButton: true, confirmButtonColor: '#009981', confirmButtonText: 'Khôi phục ngay'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const res = await fetch(`http://127.0.0.1:8000/api/admin/staff/${id}/restore`, { 
          method: 'POST', 
          headers: getHeaders() 
        });
        const data = await res.json();
        if (res.ok) { 
          Swal.fire('Thành công', data.message, 'success'); 
          fetchData(); 
        } else {
          Swal.fire('Lỗi', data.message, 'error');
        }
      } catch (err) { Swal.fire('Lỗi', 'Mất kết nối', 'error'); }
    }
  });
};

onMounted(() => fetchData());
</script>

<style scoped>
.logo-shimmer {
  font-size: 3.5rem;
  font-weight: 900;
  letter-spacing: -1.5px;
  background: linear-gradient(120deg, #009981 30%, #4dffdf 50%, #009981 70%);
  background-size: 200% auto;
  color: transparent;
  -webkit-background-clip: text;
  background-clip: text;
  animation: shine 1.5s linear infinite;
}

@keyframes shine {
  to { background-position: 200% center; }
}

.custom-tab {
  font-weight: 600 !important;
  color: #6c757d;
  border-bottom: 2px solid transparent !important;
  margin-bottom: -1px;
  transition: color 0.2s ease;
}

.custom-tab:hover {
  color: #009981;
}

.custom-tab.active-tab {
  color: #009981 !important;
  border-bottom: 2px solid #009981 !important;
}

.tab-badge {
  font-size: 0.75rem;
  font-weight: 600;
  background-color: #f8f9fa;
  color: #6c757d;
  border: 1px solid #dee2e6;
  transition: all 0.2s ease;
}

.active-badge {
  background-color: #e6f5f2 !important;
  color: #009981 !important;
  border-color: #009981 !important;
}

.bg-brand { background-color: #009981 !important; }
.text-brand { color: #009981 !important; }
.border-brand { border-color: #009981 !important; }
.btn-brand { background-color: #009981; border: none; transition: 0.2s; }
.btn-brand:hover { background-color: #007a67; }
.btn-outline-brand { color: #009981; border-color: #009981; transition: 0.2s; background: transparent; }
.btn-outline-brand:hover { background-color: #009981; color: white; }
</style>