<template>
  <div class="role-index-wrapper">
    
    <div class="container-fluid py-4" v-if="!isPageLoading">
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-md-6">
          <h3 class="fw-bold text-dark mb-0">Quản lý Phân Quyền & Cấp Độ</h3>
        </div>
        
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end align-items-center gap-3">
          <div class="border rounded px-3 py-1 bg-white shadow-sm text-muted small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>

          <button v-if="activeTab === 'roles'" class="btn btn-brand px-4 py-2 fw-bold shadow-sm text-white" @click="openRoleModal('create')">
            <i class="bi bi-plus-circle me-1"></i> Thêm Role
          </button>
        </div>
      </div>

      <div class="mb-4">
        <ul class="nav nav-underline border-bottom mb-2 flex-nowrap overflow-hidden">
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab"
               href="#"
               :class="{ 'active-tab': activeTab === 'roles' }"
               @click.prevent="switchTab('roles')">
              <i class="bi bi-person-badge me-2"></i> Danh sách Chức vụ
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab"
               href="#"
               :class="{ 'active-tab': activeTab === 'modules' }"
               @click.prevent="switchTab('modules')">
              <i class="bi bi-shield-lock me-2"></i> Cài đặt Cấp độ Trang
            </a>
          </li>
        </ul>
      </div>

      <div v-if="activeTab === 'roles'" class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-list-ul me-2"></i>Danh sách Roles</h6>
          <div class="search-box position-relative" style="width: 250px;">
            <input type="text" class="form-control rounded-pill pe-5 shadow-sm bg-light border-0" v-model="searchQuery" placeholder="Tìm kiếm role...">
            <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
          </div>
        </div>
        <div class="card-body p-0 mt-2">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="py-3 px-4 text-secondary border-0">ID</th>
                  <th class="py-3 px-4 text-secondary border-0">Tên hiển thị</th>
                  <th class="py-3 px-4 text-secondary border-0">Mã hệ thống</th>
                  <th class="py-3 px-4 text-secondary border-0">Quyền Hạn (Level)</th>
                  <th class="py-3 px-4 text-secondary text-center border-0">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="isLoadingRoles">
                  <td colspan="5" class="text-center py-5 text-muted">
                    <span class="spinner-border spinner-border-sm me-2"></span> Đang tải dữ liệu Roles...
                  </td>
                </tr>
                <tr v-else-if="filteredRoles.length === 0">
                  <td colspan="5" class="text-center py-5 text-muted">Không tìm thấy Role nào.</td>
                </tr>
                <tr v-else v-for="role in filteredRoles" :key="role.id">
                  <td class="px-4 fw-bold text-muted">#{{ role.id }}</td>
                  <td class="px-4 fw-semibold">
                    <span class="badge rounded-pill" :class="role.badgeClass || 'bg-secondary'">{{ role.label }}</span>
                  </td>
                  <td class="px-4 text-muted">{{ role.value }}</td>
                  <td class="px-4">
                    <span class="badge border" :class="getLevelColor(role.level)">
                      <i class="bi bi-star-fill me-1" v-if="role.level === 1"></i>
                      Cấp {{ role.level || 5 }}
                    </span>
                  </td>
                  <td class="px-4 text-center">
                    <button class="btn btn-sm btn-light text-primary me-2 shadow-sm" @click="openRoleModal('edit', role)">
                      <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-sm btn-light text-danger shadow-sm" @click="confirmDeleteRole(role.id)" :disabled="role.id === 1">
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div v-if="activeTab === 'modules'" class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-hdd-network me-2"></i>Yêu cầu cấp độ truy cập</h6>
            <div>
              <button v-if="isSuperAdmin" class="btn btn-sm btn-outline-primary fw-semibold me-2 rounded-pill px-3 shadow-sm" @click="syncModules" :disabled="isSyncing">
                <i class="bi bi-arrow-repeat me-1" :class="{'bi-spin': isSyncing}"></i> 
                {{ isSyncing ? 'Đang đồng bộ...' : 'Đồng bộ cấu hình' }}
              </button>
              <span v-if="!isSuperAdmin" class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                <i class="bi bi-lock-fill me-1"></i> Chỉ Super Admin mới được cấu hình
              </span>
            </div>
          </div>
        </div>
        <div class="card-body p-0 mt-2">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="py-3 px-4 text-secondary border-0" style="width: 30%;">Tên Trang (Module)</th>
                  <th class="py-3 px-4 text-secondary border-0" style="width: 20%;">Mã Route (Code)</th>
                  <th class="py-3 px-4 text-secondary border-0" style="width: 25%;">Cấp tối thiểu yêu cầu</th>
                  <th class="py-3 px-4 text-secondary text-center border-0" style="width: 25%;">Cấu hình</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="isLoadingModules">
                  <td colspan="4" class="text-center py-5 text-muted">
                    <span class="spinner-border spinner-border-sm me-2"></span> Đang tải cấu hình trang...
                  </td>
                </tr>
                <tr v-else-if="systemModules.length === 0">
                  <td colspan="4" class="text-center py-5 text-muted">Chưa có dữ liệu. Vui lòng nhấn "Đồng bộ cấu hình".</td>
                </tr>
                <tr v-else v-for="module in systemModules" :key="module.id">
                  <td class="px-4 fw-semibold text-dark">{{ module.module_name }}</td>
                  <td class="px-4 text-muted font-monospace small bg-light rounded">{{ module.module_code }}</td>
                  <td class="px-4">
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge" :class="getLevelColor(module.required_level)">Cấp {{ module.required_level }}</span>
                      <input v-if="isSuperAdmin && editingModuleId === module.id" 
                             type="number" min="1"
                             class="form-control form-control-sm w-auto shadow-sm border-primary" 
                             v-model="editLevelValue" style="max-width: 80px;">
                    </div>
                  </td>
                  <td class="px-4 text-center">
                    <button v-if="isSuperAdmin && editingModuleId !== module.id" 
                            class="btn btn-sm btn-outline-brand fw-semibold rounded-pill px-3 shadow-sm" 
                            @click="startEditModule(module)">
                      <i class="bi bi-sliders"></i> Đổi cấp
                    </button>
                    
                    <div v-if="isSuperAdmin && editingModuleId === module.id" class="d-flex justify-content-center gap-2">
                      <button class="btn btn-sm btn-success fw-bold px-3 rounded-pill shadow-sm" @click="saveModuleLevel(module.id)" :disabled="isSavingLevel">
                        <i class="bi bi-check-lg"></i> Lưu
                      </button>
                      <button class="btn btn-sm btn-light border text-danger rounded-pill px-3 shadow-sm" @click="editingModuleId = null">
                        Hủy
                      </button>
                    </div>

                    <span v-if="!isSuperAdmin" class="text-muted small"><i class="bi bi-dash-lg"></i> Không có quyền</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ThinkHub</h1>
      <p class="text-muted fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">
        Đang tải dữ liệu...
      </p>
    </div>

    <div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="fw-bold">{{ modalMode === 'create' ? 'Thêm Role Mới' : 'Cập Nhật Role' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <form @submit.prevent="saveRole">
              <div class="mb-3">
                <label class="form-label fw-semibold">Tên hiển thị (Label) <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="roleForm.label" required>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Mã hệ thống (Value) <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="roleForm.value" required>
              </div>

              <!-- CỘT CHỌN CẤP ĐỘ -->
              <div class="mb-3 p-3 bg-light rounded border border-light-subtle">
                <label class="form-label fw-bold text-dark mb-2">Định vị Cấp độ (Level) <span class="text-danger">*</span></label>
                <input type="number" class="form-control form-control-lg fw-bold text-primary" v-model="roleForm.level" min="1" required :disabled="roleForm.id === 1">
                <small class="text-danger mt-1 d-block" v-if="roleForm.id === 1">Không thể thay đổi Cấp độ của Super Admin gốc.</small>
              </div>

              <div class="mb-4">
                <label class="form-label fw-semibold">Màu sắc Nhãn</label>
                <select class="form-select" v-model="roleForm.badgeClass">
                  <option value="">Mặc định (Xám)</option>
                  <option value="bg-primary">Xanh dương</option>
                  <option value="bg-success">Xanh lá</option>
                  <option value="bg-danger">Đỏ</option>
                  <option value="bg-warning text-dark">Vàng</option>
                  <option value="bg-info text-dark">Xanh ngọc</option>
                  <option value="bg-dark">Đen</option>
                </select>
              </div>
              <div class="text-end">
                <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-brand text-white px-4 fw-bold" :disabled="isSaving">
                  <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> Lưu
                </button>
              </div>
            </form>
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

const route = useRoute();

// Kiểm tra quyền Super Admin
const isSuperAdmin = computed(() => {
  return localStorage.getItem('admin_role') == 1;
});

// Trạng thái Loading tổng (cho Splash Screen)
const isPageLoading = ref(true);

const activeTab = ref('roles');
const searchQuery = ref('');
const currentPageLevel = ref(null); 

// State Dữ liệu
const roles = ref([]);
const isLoadingRoles = ref(false);
const isSaving = ref(false);

const systemModules = ref([]);
const isLoadingModules = ref(false);
const editingModuleId = ref(null);
const editLevelValue = ref(1);
const isSavingLevel = ref(false);
const isSyncing = ref(false);

// Form Role
const modalMode = ref('create');
let bRoleModal = null;
const roleForm = ref({ id: null, label: '', value: '', badgeClass: '', level: 5 });

const getHeaders = () => {
  return {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${localStorage.getItem('admin_token')}`
  };
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

// ================= LỌC ROLES =================
const filteredRoles = computed(() => {
  if (!searchQuery.value) return roles.value;
  const q = searchQuery.value.toLowerCase();
  return roles.value.filter(r => 
    r.label.toLowerCase().includes(q) || r.value.toLowerCase().includes(q)
  );
});

// ================= API CALLS =================
const fetchRoles = async () => {
  isLoadingRoles.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/admin/roles', { headers: getHeaders() });
    const data = await res.json();
    if (res.ok) roles.value = data.data;
  } catch (err) { console.error(err); } finally { isLoadingRoles.value = false; }
};

const fetchModules = async () => {
  isLoadingModules.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/admin/modules', { headers: getHeaders() });
    const data = await res.json();
    if (res.ok) {
      systemModules.value = data.data;
      const currentCode = route.meta.moduleCode;
      if (currentCode) {
        const currentModule = systemModules.value.find(m => m.module_code === currentCode);
        if (currentModule) currentPageLevel.value = currentModule.required_level;
      }
    }
  } catch (err) { console.error(err); } finally { isLoadingModules.value = false; }
};

// ================= ROLE ACTIONS =================
const openRoleModal = (mode, role = null) => {
  modalMode.value = mode;
  roleForm.value = mode === 'edit' && role 
    ? { id: role.id, label: role.label, value: role.value, badgeClass: role.badgeClass || '', level: role.level || 5 }
    : { id: null, label: '', value: '', badgeClass: '', level: 5 };
  
  if(!bRoleModal) bRoleModal = new window.bootstrap.Modal(document.getElementById('roleModal'));
  bRoleModal.show();
};

const saveRole = async () => {
  isSaving.value = true;
  const url = modalMode.value === 'create' ? 'http://127.0.0.1:8000/api/admin/roles' : `http://127.0.0.1:8000/api/admin/roles/${roleForm.value.id}`;
  const method = modalMode.value === 'create' ? 'POST' : 'PUT';

  try {
    const res = await fetch(url, { method, headers: getHeaders(), body: JSON.stringify(roleForm.value) });
    const data = await res.json();
    if (res.ok) {
      Swal.fire({ icon: 'success', title: 'Thành công', text: data.message, timer: 1500, showConfirmButton: false });
      bRoleModal.hide();
      fetchRoles(); 
    } else {
      Swal.fire('Lỗi', data.message || Object.values(data.errors).flat().join('\n'), 'error');
    }
  } catch (err) { Swal.fire('Lỗi', 'Mất kết nối', 'error'); } finally { isSaving.value = false; }
};

const confirmDeleteRole = (id) => {
  Swal.fire({
    title: 'Xóa Role này?', text: "Hành động này không thể hoàn tác!", icon: 'warning',
    showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'Xóa ngay'
  }).then(async (resAlert) => {
    if (resAlert.isConfirmed) {
      try {
        const res = await fetch(`http://127.0.0.1:8000/api/admin/roles/${id}`, { method: 'DELETE', headers: getHeaders() });
        const data = await res.json();
        if (res.ok) { 
          Swal.fire({ icon: 'success', title: 'Đã xóa', text: data.message, timer: 1500, showConfirmButton: false }); 
          fetchRoles();
        } else Swal.fire('Lỗi', data.message, 'error');
      } catch (err) { Swal.fire('Lỗi', 'Mất kết nối', 'error'); }
    }
  });
};

// ================= MODULE ACTIONS =================
const syncModules = async () => {
  isSyncing.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/admin/modules/sync', { method: 'POST', headers: getHeaders() });
    const data = await res.json();
    if (res.ok) {
      Swal.fire({ icon: 'success', title: 'Hoàn tất', text: data.message, timer: 2000, showConfirmButton: false });
      fetchModules(); 
    } else {
      Swal.fire('Lỗi', data.message, 'error');
    }
  } catch (err) { Swal.fire('Lỗi', 'Mất kết nối', 'error'); } finally { isSyncing.value = false; }
};

const startEditModule = (module) => {
  editingModuleId.value = module.id;
  editLevelValue.value = module.required_level;
};

const saveModuleLevel = async (moduleId) => {
  isSavingLevel.value = true;
  try {
    const res = await fetch(`http://127.0.0.1:8000/api/admin/modules/${moduleId}/level`, {
      method: 'PUT',
      headers: getHeaders(),
      body: JSON.stringify({ required_level: editLevelValue.value })
    });
    
    const data = await res.json();
    if(res.ok) {
      const target = systemModules.value.find(m => m.id === moduleId);
      if(target) target.required_level = editLevelValue.value;
      
      const currentCode = route.meta.moduleCode;
      if(target.module_code === currentCode) currentPageLevel.value = editLevelValue.value;

      Swal.fire({ icon: 'success', title: 'Đã cập nhật', text: data.message, timer: 1500, showConfirmButton: false });
      editingModuleId.value = null;
    } else {
      Swal.fire('Lỗi', data.message, 'error');
    }
  } catch (err) { Swal.fire('Lỗi', 'Không thể lưu', 'error'); } finally { isSavingLevel.value = false; }
};

const switchTab = (tab) => {
  activeTab.value = tab;
};

// ================= CHẠY LẦN ĐẦU =================
onMounted(async () => {
  isPageLoading.value = true; // Bật màn hình Logo Shimmer
  
  await Promise.all([fetchRoles(), fetchModules()]);
  
  isPageLoading.value = false; 
});
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

/* Buttons & Colors */
.bg-brand { background-color: #009981 !important; }
.text-brand { color: #009981 !important; }
.border-brand { border-color: #009981 !important; }
.btn-brand { background-color: #009981; border: none; transition: 0.2s; }
.btn-brand:hover { background-color: #007a67; }
.btn-outline-brand { color: #009981; border-color: #009981; transition: 0.2s; }
.btn-outline-brand:hover { background-color: #009981; color: white; }
.form-control:focus, .form-select:focus { border-color: #009981; box-shadow: 0 0 0 0.25rem rgba(0, 153, 129, 0.25); }

/* Nút xoay Sync */
.bi-spin { display: inline-block; animation: bi-spin 2s infinite linear; }
@keyframes bi-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(359deg); } }
</style>