<template>
  <div class="user-index-wrapper">
    <div class="container-fluid py-4" v-if="!isLoading">
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-md-6">
          <h3 class="fw-bold text-dark mb-0">Quản lý Khách Hàng</h3>
          <p class="text-muted mb-0">Danh sách tài khoản người dùng đăng ký mua hàng</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end align-items-center gap-3">
          <div class="border rounded px-3 py-1 bg-white shadow-sm text-muted small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>
          <router-link :to="{ name: 'admin-user-create' }" class="btn btn-brand px-4 py-2 fw-bold shadow-sm text-white">
            <i class="bi bi-person-plus-fill me-1"></i> Thêm Khách Hàng
          </router-link>
        </div>
      </div>

      <!-- Tabs phân loại -->
      <div class="mb-4">
        <ul class="nav nav-underline border-bottom mb-2 flex-nowrap overflow-hidden">
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'all' }" @click.prevent="switchTab('all')">
              <i class="bi bi-people-fill me-2"></i> Tất cả
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'all'}">{{ users.filter(u => !u.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'active' }" @click.prevent="switchTab('active')">
              <i class="bi bi-check-circle-fill me-2 text-success"></i> Đang hoạt động
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'active'}">{{ users.filter(u => u.status === 'active' && !u.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'locked' }" @click.prevent="switchTab('locked')">
              <i class="bi bi-lock-fill me-2 text-warning"></i> Bị khóa
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'locked'}">{{ users.filter(u => u.status === 'locked' && !u.deleted_at).length }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'deleted' }" @click.prevent="switchTab('deleted')">
              <i class="bi bi-trash3-fill me-2 text-danger"></i> Đã xóa
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'deleted'}">{{ users.filter(u => u.deleted_at).length }}</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- Bảng Dữ liệu -->
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-list-ul me-2"></i>Danh sách hiển thị</h6>
          <div class="search-box position-relative" style="width: 280px;">
            <input type="text" class="form-control rounded-pill pe-5 shadow-sm bg-light border-0" v-model="searchQuery" @input="currentPage = 1" placeholder="Tìm tên, email, SĐT...">
            <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
          </div>
        </div>
        <div class="card-body p-0 mt-2">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="py-3 px-4 text-secondary border-0">Khách hàng</th>
                  <th class="py-3 px-4 text-secondary border-0">Thông tin liên hệ</th>
                  <th class="py-3 px-4 text-secondary border-0" style="width: 25%;">Địa chỉ mặc định</th>
                  <th class="py-3 px-4 text-secondary border-0">Trạng thái</th>
                  <th class="py-3 px-4 text-secondary text-center border-0">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="paginatedUsers.length === 0">
                  <td colspan="5" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Không có dữ liệu.</td>
                </tr>
                <tr v-else v-for="user in paginatedUsers" :key="user.id" :class="{'bg-light opacity-75': user.deleted_at}">
                  <td class="px-4 py-3">
                    <div class="d-flex align-items-center">
                      <img :src="getAvatarUrl(user.avatar_url)" class="rounded-circle object-fit-cover me-3 border shadow-sm" style="width: 45px; height: 45px;">
                      <div>
                        <h6 class="mb-0 fw-bold text-dark">{{ user.fullName }}</h6>
                        <small class="text-muted d-block mt-1">
                          <i class="bi bi-calendar-event me-1"></i> {{ formatDate(user.birthday) }}
                        </small>
                      </div>
                    </div>
                  </td>
                  <td class="px-4">
                    <div class="text-dark fw-medium"><i class="bi bi-telephone text-muted me-1"></i> {{ user.phone || 'Chưa cập nhật' }}</div>
                    <div class="text-muted small mt-1"><i class="bi bi-envelope me-1"></i> {{ user.email }}</div>
                  </td>
                  <td class="px-4">
                    <template v-if="user.default_address">
                      <div class="text-dark small text-truncate" style="max-width: 250px;" :title="formatFullAddress(user.default_address)">
                        {{ user.default_address.shipping_address }}
                      </div>
                      <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                        {{ [user.default_address.ward, user.default_address.district, user.default_address.city].filter(Boolean).join(', ') }}
                      </div>
                    </template>
                    <span v-else class="text-muted small fst-italic">Chưa có địa chỉ</span>
                  </td>
                  <td class="px-4">
                    <span v-if="user.deleted_at" class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary" title="Đã chuyển vào thùng rác">
                      <i class="bi bi-trash3-fill"></i> Đã xóa
                    </span>
                    <span v-else class="badge" :class="user.status === 'active' ? 'bg-success bg-opacity-10 text-success border border-success' : 'bg-warning bg-opacity-10 text-warning border border-warning'">
                      <i class="bi" :class="user.status === 'active' ? 'bi-check-circle-fill' : 'bi-lock-fill'"></i>
                      {{ user.status === 'active' ? 'Hoạt động' : 'Bị Khóa' }}
                    </span>
                  </td>
                  <td class="px-4 text-center">
                    <button class="btn btn-sm btn-light text-info me-2 shadow-sm" title="Xem chi tiết" @click="openQuickView(user)">
                      <i class="bi bi-eye"></i>
                    </button>
                    <template v-if="!user.deleted_at">
                      <router-link :to="{ name: 'admin-user-edit', params: { id: user.id } }" class="btn btn-sm btn-light text-primary me-2 shadow-sm" title="Chỉnh sửa">
                        <i class="bi bi-pencil-square"></i>
                      </router-link>
                      <button class="btn btn-sm btn-light text-danger shadow-sm" @click="confirmDelete(user.id)" title="Đưa vào thùng rác">
                        <i class="bi bi-trash"></i>
                      </button>
                    </template>
                    <template v-else>
                      <button class="btn btn-sm btn-light text-success shadow-sm" @click="restoreUser(user.id)" title="Khôi phục tài khoản">
                        <i class="bi bi-arrow-counterclockwise"></i>
                      </button>
                    </template>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Phân trang -->
      <div class="d-flex justify-content-between align-items-center" v-if="totalPages > 1">
        <span class="text-muted small">Hiển thị {{ (currentPage - 1) * itemsPerPage + 1 }} đến {{ Math.min(currentPage * itemsPerPage, processedUsers.length) }}</span>
        <nav>
          <ul class="pagination pagination-sm mb-0 shadow-sm">
            <li class="page-item" :class="{ disabled: currentPage === 1 }"><button class="page-link text-brand" @click="currentPage--"><i class="bi bi-chevron-left"></i></button></li>
            <li class="page-item" v-for="page in totalPages" :key="page" :class="{ active: currentPage === page }"><button class="page-link" :class="currentPage === page ? 'bg-brand border-brand text-white' : 'text-dark'" @click="currentPage = page">{{ page }}</button></li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }"><button class="page-link text-brand" @click="currentPage++"><i class="bi bi-chevron-right"></i></button></li>
          </ul>
        </nav>
      </div>
    </div>

    <div v-else class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ThinkHub</h1>
      <p class="text-muted fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải dữ liệu khách hàng...</p>
    </div>

    <!-- POPUP XEM CHI TIẾT (QUICK VIEW) BAO GỒM TẤT CẢ ĐỊA CHỈ -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="fw-bold text-dark">Hồ sơ Khách Hàng</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4" v-if="selectedUser">
            <div class="row">
              <!-- Cột thông tin cơ bản -->
              <div class="col-md-5 text-center border-end mb-4 mb-md-0">
                <div class="position-relative d-inline-block mb-3">
                  <img :src="getAvatarUrl(selectedUser.avatar_url)" class="rounded-circle shadow-sm border border-3 border-white object-fit-cover" style="width: 130px; height: 130px;">
                  <span v-if="!selectedUser.deleted_at" class="position-absolute bottom-0 end-0 p-2 border border-light rounded-circle" :class="selectedUser.status === 'active' ? 'bg-success' : 'bg-warning'" style="width: 18px; height: 18px;"></span>
                </div>
                <h5 class="fw-bold mb-1">{{ selectedUser.fullName }}</h5>
                <p class="text-muted small mb-3">{{ selectedUser.email }}</p>
                
                <div class="text-start bg-light p-3 rounded-4 shadow-sm border border-light-subtle small">
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted fw-semibold"><i class="bi bi-telephone text-brand me-1"></i>SĐT:</span>
                    <span class="fw-bold text-dark">{{ selectedUser.phone || 'N/A' }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted fw-semibold"><i class="bi bi-gender-ambiguous text-brand me-1"></i>Giới tính:</span>
                    <span class="text-dark">{{ selectedUser.gender || 'N/A' }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted fw-semibold"><i class="bi bi-calendar-event text-brand me-1"></i>Ngày sinh:</span>
                    <span class="text-dark">{{ formatDate(selectedUser.birthday) }}</span>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span class="text-muted fw-semibold"><i class="bi bi-clock-history text-brand me-1"></i>Tham gia:</span>
                    <span class="text-dark">{{ formatDate(selectedUser.created_at) }}</span>
                  </div>
                </div>
                
                <div class="mt-3" v-if="!selectedUser.deleted_at">
                  <router-link :to="{ name: 'admin-user-edit', params: { id: selectedUser.id } }" class="btn btn-outline-brand rounded-pill w-100" data-bs-dismiss="modal">
                    <i class="bi bi-pencil-square me-1"></i> Chỉnh sửa tài khoản
                  </router-link>
                </div>
              </div>
              
              <!-- Cột Sổ địa chỉ -->
              <div class="col-md-7">
                <h6 class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                  <span><i class="bi bi-journal-bookmark text-brand me-2"></i>Sổ địa chỉ đã lưu</span>
                  <span class="badge bg-brand">{{ selectedUser.addresses?.length || 0 }}</span>
                </h6>
                
                <div class="address-list custom-scrollbar pe-2" style="max-height: 350px; overflow-y: auto;">
                  <div v-if="!selectedUser.addresses || selectedUser.addresses.length === 0" class="text-center p-4 bg-light rounded-4 border border-dashed">
                    <i class="bi bi-geo-alt text-muted fs-3 mb-2 d-block opacity-50"></i>
                    <span class="text-muted small">Khách hàng chưa lưu địa chỉ nào.</span>
                  </div>

                  <div v-else v-for="addr in selectedUser.addresses" :key="addr.id" 
                       class="p-3 mb-3 rounded-4 border position-relative" 
                       :class="addr.is_default ? 'bg-brand bg-opacity-10 border-brand' : 'bg-light border-light-subtle'">
                    
                    <span v-if="addr.is_default" class="badge bg-brand position-absolute top-0 end-0 rounded-start-0 rounded-bottom-0 rounded-end-4 px-2 py-1" style="font-size: 0.65rem;">
                      <i class="bi bi-star-fill text-warning me-1"></i> Mặc định
                    </span>

                    <div class="fw-bold text-dark mb-1">{{ addr.customer_name }} <span class="text-muted fw-normal ms-2">| {{ addr.customer_phone }}</span></div>
                    <div class="text-muted small mb-1">{{ addr.shipping_address }}</div>
                    <div class="text-muted small">{{ [addr.ward, addr.district, addr.city].filter(Boolean).join(', ') }}</div>
                  </div>
                </div>
              </div>
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
const users = ref([]);
const systemModules = ref([]);
const isLoading = ref(true);
const searchQuery = ref('');
const activeTab = ref('all');
const currentPageLevel = ref(null);

const currentPage = ref(1);
const itemsPerPage = 8; 

const selectedUser = ref(null);
let quickViewModalInstance = null;

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const getAvatarUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : defaultAvatar;
const formatDate = (dateString) => dateString ? new Date(dateString).toLocaleDateString('vi-VN') : 'Chưa cập nhật';
const formatDateTime = (dateString) => dateString ? `${new Date(dateString).toLocaleDateString('vi-VN')} ${new Date(dateString).toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}` : '';

const formatFullAddress = (addr) => {
  if (!addr) return '';
  return `${addr.shipping_address}, ${[addr.ward, addr.district, addr.city].filter(Boolean).join(', ')}`;
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

const fetchData = async () => {
  isLoading.value = true;
  try {
    const [resUsers, resModules] = await Promise.all([
      fetch('http://127.0.0.1:8000/api/admin/users', { headers: getHeaders() }),
      fetch('http://127.0.0.1:8000/api/admin/modules', { headers: getHeaders() })
    ]);
    if (resUsers.ok) users.value = (await resUsers.json()).data;
    if (resModules.ok) {
      systemModules.value = (await resModules.json()).data;
      const currentModule = systemModules.value.find(m => m.module_code === (route.meta.moduleCode || 'admin_users'));
      if (currentModule) currentPageLevel.value = currentModule.required_level;
    }
  } catch (err) { console.error(err); } finally { isLoading.value = false; }
};

const switchTab = (tabId) => { activeTab.value = tabId; currentPage.value = 1; };

const openQuickView = (user) => {
  // Sắp xếp địa chỉ mặc định lên đầu
  if (user.addresses) {
    user.addresses.sort((a, b) => b.is_default - a.is_default);
  }
  selectedUser.value = user;
  if (!quickViewModalInstance) quickViewModalInstance = new window.bootstrap.Modal(document.getElementById('quickViewModal'));
  quickViewModalInstance.show();
};

const processedUsers = computed(() => {
  let result = users.value;
  if (activeTab.value === 'deleted') { result = result.filter(u => u.deleted_at); } 
  else {
    result = result.filter(u => !u.deleted_at);
    if (activeTab.value !== 'all') result = result.filter(u => u.status === activeTab.value);
  }
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(u => (u.fullName?.toLowerCase().includes(q)) || (u.email?.toLowerCase().includes(q)) || (u.phone?.includes(q)));
  }
  return result;
});

const totalPages = computed(() => Math.ceil(processedUsers.value.length / itemsPerPage) || 1);
const paginatedUsers = computed(() => { const start = (currentPage.value - 1) * itemsPerPage; return processedUsers.value.slice(start, start + itemsPerPage); });

const confirmDelete = (id) => {
  Swal.fire({ title: 'Xóa tài khoản này?', text: "Tài khoản sẽ bị đưa vào thùng rác!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Đồng ý xóa' }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const res = await fetch(`http://127.0.0.1:8000/api/admin/users/${id}`, { method: 'DELETE', headers: getHeaders() });
        if (res.ok) { Swal.fire('Đã xóa', 'Thành công', 'success'); fetchData(); }
      } catch (err) {}
    }
  });
};

const restoreUser = (id) => {
  Swal.fire({ title: 'Khôi phục tài khoản?', icon: 'info', showCancelButton: true, confirmButtonColor: '#009981', confirmButtonText: 'Khôi phục' }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const res = await fetch(`http://127.0.0.1:8000/api/admin/users/${id}/restore`, { method: 'POST', headers: getHeaders() });
        if (res.ok) { Swal.fire('Thành công', 'Đã khôi phục', 'success'); fetchData(); }
      } catch (err) {}
    }
  });
};

onMounted(() => fetchData());
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
.btn-brand { background-color: #009981; border: none; transition: 0.2s; } .btn-brand:hover { background-color: #007a67; }
.btn-outline-brand { color: #009981; border-color: #009981; transition: 0.2s; } .btn-outline-brand:hover { background-color: #009981; color: white; }

/* Scrollbar tùy chỉnh cho danh sách địa chỉ */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
.border-dashed { border-style: dashed !important; }
</style>