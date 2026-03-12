<template>
  <div class="user-edit-wrapper">
    <div class="container-fluid py-4" v-if="isLoaded">
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-md-8 d-flex align-items-center">
          <router-link :to="{ name: 'admin-users' }" class="btn btn-light shadow-sm me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
            <i class="bi bi-arrow-left fw-bold"></i>
          </router-link>
          <div>
            <h3 class="fw-bold text-dark mb-0">Hồ sơ Khách Hàng</h3>
            <p class="text-muted mb-0 small">Mã tài khoản: #{{ route.params.id }}</p>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <!-- ================= CỘT TRÁI: AVATAR & TRẠNG THÁI ================= -->
        <div class="col-md-4 col-lg-3">
          <div class="card border-0 shadow-sm rounded-4 text-center p-4 h-100">
            <div class="position-relative d-inline-block mx-auto mb-3">
              <img :src="previewAvatar" class="rounded-circle shadow-sm border border-3 border-white object-fit-cover" style="width: 140px; height: 140px;" alt="Avatar">
              <label for="avatarUpload" class="position-absolute bottom-0 end-0 bg-brand rounded-circle shadow-sm p-2 text-white cursor-pointer" title="Đổi ảnh đại diện">
                <i class="bi bi-camera-fill fs-6"></i>
              </label>
              <input type="file" id="avatarUpload" class="d-none" accept="image/png, image/jpeg" @change="handleAvatarChange">
            </div>
            
            <h5 class="fw-bold text-dark mb-1">{{ form.fullName || 'Khách hàng' }}</h5>
            <span class="badge mb-3 px-3 py-2 rounded-pill text-white" :class="form.status === 'active' ? 'bg-success' : 'bg-danger'">
              <i class="bi me-1" :class="form.status === 'active' ? 'bi-check-circle' : 'bi-lock-fill'"></i>
              {{ form.status === 'active' ? 'Đang hoạt động' : 'Đã bị khóa' }}
            </span>
            
            <div class="mb-3" v-if="previewAvatar && !previewAvatar.includes('avatar1.png') && !selectedFile">
              <button type="button" @click="removeAvatar" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold w-100 shadow-sm">
                <i class="bi bi-trash me-1"></i> Xóa ảnh hiện tại
              </button>
            </div>

            <hr class="text-muted opacity-25 my-3">
            
            <!-- Đã sửa size dropdown nhỏ lại và thêm màu nền tương phản -->
            <div class="text-start">
              <label class="form-label fw-bold text-muted small text-uppercase mb-2">Cập nhật Trạng thái</label>
              <select class="form-select fw-bold shadow-sm" v-model="form.status" required :class="form.status === 'active' ? 'text-success border-success bg-success bg-opacity-10' : 'text-danger border-danger bg-danger bg-opacity-10'">
                <option value="active" class="text-success fw-bold">Hoạt động (Active)</option>
                <option value="locked" class="text-danger fw-bold">Khóa (Locked)</option>
              </select>
            </div>
          </div>
        </div>

        <!-- ================= CỘT PHẢI: TABS THÔNG TIN & ĐỊA CHỈ ================= -->
        <div class="col-md-8 col-lg-9">
          <div class="card border-0 shadow-sm rounded-4 h-100">
            
            <!-- Tabs Header (Đã sửa CSS để hiện rõ gạch chân) -->
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0 px-4">
              <ul class="nav nav-tabs-custom mb-3">
                <li class="nav-item">
                  <a class="nav-link d-flex align-items-center" :class="{ 'active': activeTab === 'info' }" href="#" @click.prevent="activeTab = 'info'">
                     <i class="bi bi-person-lines-fill me-2 fs-5"></i> Thông tin chung
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link d-flex align-items-center" :class="{ 'active': activeTab === 'address' }" href="#" @click.prevent="activeTab = 'address'">
                     <i class="bi bi-geo-alt-fill me-2 fs-5"></i> Sổ địa chỉ
                     <span class="badge ms-2 rounded-pill" :class="activeTab === 'address' ? 'bg-brand text-white' : 'bg-light text-secondary border'">{{ userAddresses.length }}</span>
                  </a>
                </li>
              </ul>
            </div>

            <div class="card-body p-4 pt-2">
              
              <!-- ================= TAB 1: THÔNG TIN CHUNG ================= -->
              <form v-if="activeTab === 'info'" @submit.prevent="updateUser">
                <div class="row">
                  <!-- Bổ sung viền border-secondary-subtle để form rõ ràng hơn -->
                  <div class="col-md-6 mb-4">
                    <label class="form-label fw-bold text-dark">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg bg-white border border-secondary-subtle" v-model="form.fullName" required>
                  </div>
                  <div class="col-md-6 mb-4">
                    <label class="form-label fw-bold text-dark">Số điện thoại</label>
                    <input type="text" class="form-control form-control-lg bg-white border border-secondary-subtle" v-model="form.phone">
                  </div>
                  
                  <div class="col-md-6 mb-4">
                    <label class="form-label fw-bold text-muted">Email đăng nhập</label>
                    <div class="input-group input-group-lg shadow-sm">
                      <span class="input-group-text bg-light text-muted border-secondary-subtle"><i class="bi bi-envelope"></i></span>
                      <input type="email" class="form-control bg-light cursor-not-allowed text-muted border-secondary-subtle" v-model="form.email" readonly disabled>
                    </div>
                  </div>
                  
                  <div class="col-md-6 mb-4">
                    <label class="form-label fw-bold text-dark">Đổi mật khẩu <span class="text-muted fw-normal small">(Bỏ trống nếu không đổi)</span></label>
                    <div class="input-group input-group-lg shadow-sm">
                      <span class="input-group-text bg-white text-muted border-secondary-subtle"><i class="bi bi-key"></i></span>
                      <input type="text" class="form-control bg-white border-secondary-subtle" v-model="form.password" placeholder="Nhập mật khẩu mới">
                    </div>
                  </div>

                  <div class="col-md-6 mb-4">
                    <label class="form-label fw-bold text-dark">Giới tính</label>
                    <select class="form-select form-select-lg bg-white border border-secondary-subtle" v-model="form.gender">
                      <option value="">-- Chưa cập nhật --</option>
                      <option value="Nam">Nam</option>
                      <option value="Nữ">Nữ</option>
                      <option value="Khác">Khác</option>
                    </select>
                  </div>
                  <div class="col-md-6 mb-4">
                    <label class="form-label fw-bold text-dark">Ngày sinh</label>
                    <input type="date" class="form-control form-control-lg bg-white border border-secondary-subtle" v-model="form.birthday">
                  </div>
                </div>

                <div class="text-end mt-2 pt-4 border-top">
                  <button type="button" class="btn btn-light me-2 px-4 fw-bold shadow-sm" @click="fetchUser">Khôi phục</button>
                  <button type="submit" class="btn btn-brand px-5 fw-bold text-white shadow-sm" :disabled="isSavingUser">
                    <span v-if="isSavingUser" class="spinner-border spinner-border-sm me-2"></span> 
                    {{ isSavingUser ? 'ĐANG LƯU...' : 'LƯU THAY ĐỔI' }}
                  </button>
                </div>
              </form>

              <!-- ================= TAB 2: QUẢN LÝ SỔ ĐỊA CHỈ ================= -->
              <div v-if="activeTab === 'address'">
                <div class="d-flex justify-content-between align-items-center mb-4">
                  <p class="text-muted mb-0">Quản lý danh sách địa chỉ nhận hàng của khách hàng này.</p>
                  <button type="button" class="btn btn-brand rounded-pill px-4 py-2 fw-bold text-white shadow-sm" @click="openAddressModal('add')">
                    <i class="bi bi-plus-lg me-1"></i> Thêm Địa Chỉ
                  </button>
                </div>
                
                <div class="address-list custom-scrollbar pe-2" style="max-height: 500px; overflow-y: auto;">
                  
                  <!-- Empty State -->
                  <div v-if="userAddresses.length === 0" class="text-center py-5 bg-light rounded-4 border border-dashed">
                    <div class="bg-white rounded-circle d-inline-flex justify-content-center align-items-center shadow-sm mb-3" style="width: 60px; height: 60px;">
                      <i class="bi bi-geo-alt text-brand fs-3"></i>
                    </div>
                    <h6 class="fw-bold text-dark">Chưa có địa chỉ nào</h6>
                    <p class="text-muted small">Khách hàng chưa lưu địa chỉ nhận hàng.</p>
                    <button class="btn btn-brand text-white btn-sm rounded-pill px-4 py-2 fw-bold mt-2 shadow-sm" @click="openAddressModal('add')">
                      Thêm địa chỉ ngay
                    </button>
                  </div>
                  
                  <!-- Address Cards -->
                  <div v-else v-for="addr in userAddresses" :key="addr.id" class="address-card p-4 mb-3 rounded-4 border position-relative transition-all" :class="addr.is_default ? 'bg-brand bg-opacity-10 border-brand shadow-sm' : 'bg-white border-secondary-subtle'">
                    
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start mb-3">
                      <div>
                        <h6 class="fw-bold text-dark mb-1 d-flex align-items-center flex-wrap gap-2">
                          {{ addr.customer_name }}
                          <span class="text-muted fw-normal">|</span>
                          <span class="text-muted fw-normal">{{ addr.customer_phone }}</span>
                          <span v-if="addr.is_default" class="badge bg-brand text-white ms-md-2 px-2 py-1"><i class="bi bi-check-circle me-1"></i>Mặc định</span>
                        </h6>
                      </div>
                      
                      <button v-if="!addr.is_default" @click="setDefaultAddress(addr.id)" class="btn btn-sm btn-outline-secondary fw-bold rounded-pill mt-2 mt-md-0 px-3 shadow-sm hover-brand-btn">
                        Đặt làm mặc định
                      </button>
                    </div>

                    <div class="mb-4">
                      <p class="text-dark mb-1 "><i class="bi bi-house-door text-muted me-2 fs-5 align-middle"></i>{{ addr.shipping_address }}</p>
                      <p class="text-muted mb-0" ><i class="bi bi-map text-muted me-2 fs-5 align-middle"></i>{{ [addr.ward, addr.district, addr.city].filter(Boolean).join(', ') }}</p>
                    </div>
                    
                    <div class="d-flex gap-2 border-top border-light-subtle pt-3">
                      <button class="btn btn-sm btn-primary text-white rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center" @click="openAddressModal('edit', addr)">
                        <i class="bi bi-pencil-square me-2"></i> Sửa địa chỉ
                      </button>
                      <button v-if="!addr.is_default" class="btn btn-sm btn-danger text-white rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center" @click="deleteAddress(addr.id)">
                        <i class="bi bi-trash me-2"></i> Xóa
                      </button>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div v-else class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ThinkHub</h1>
      <p class="text-muted fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải dữ liệu...</p>
    </div>

    <!-- MODAL THÊM/SỬA ĐỊA CHỈ (ĐÃ NÂNG CẤP DROPDOWN API) -->
    <div class="modal fade" id="addressModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="fw-bold text-dark">{{ addrModalMode === 'add' ? 'Thêm Địa Chỉ Mới' : 'Cập Nhật Địa Chỉ' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <form @submit.prevent="saveAddress">
              <div class="row g-3">
                <div class="col-md-6 mb-2">
                  <label class="form-label fw-bold text-dark small">Tên người nhận <span class="text-danger">*</span></label>
                  <input type="text" class="form-control bg-white border border-secondary-subtle shadow-none" v-model="addrForm.customer_name" required>
                </div>
                <div class="col-md-6 mb-2">
                  <label class="form-label fw-bold text-dark small">Số điện thoại <span class="text-danger">*</span></label>
                  <input type="text" class="form-control bg-white border border-secondary-subtle shadow-none" v-model="addrForm.customer_phone" required>
                </div>
                
                <!-- DROPDOWN 3 CẤP CHỌN ĐỊA CHỈ -->
                <div class="col-md-4 mb-2">
                  <label class="form-label fw-bold text-dark small">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                  <select class="form-select bg-white border border-secondary-subtle shadow-none fw-semibold" v-model="selectedCityId" @change="onCityChange" required>
                    <option value="" disabled>-- Chọn Tỉnh/Thành --</option>
                    <option v-for="p in provinces" :key="p.id" :value="p.id">{{ p.full_name }}</option>
                  </select>
                </div>
                <div class="col-md-4 mb-2">
                  <label class="form-label fw-bold text-dark small">Quận/Huyện <span class="text-danger">*</span></label>
                  <select class="form-select bg-white border border-secondary-subtle shadow-none fw-semibold" v-model="selectedDistrictId" @change="onDistrictChange" required :disabled="!selectedCityId">
                    <option value="" disabled>-- Chọn Quận/Huyện --</option>
                    <option v-for="d in districts" :key="d.id" :value="d.id">{{ d.full_name }}</option>
                  </select>
                </div>
                <div class="col-md-4 mb-2">
                  <label class="form-label fw-bold text-dark small">Phường/Xã <span class="text-danger">*</span></label>
                  <select class="form-select bg-white border border-secondary-subtle shadow-none fw-semibold" v-model="selectedWardId" @change="onWardChange" required :disabled="!selectedDistrictId">
                    <option value="" disabled>-- Chọn Phường/Xã --</option>
                    <option v-for="w in wards" :key="w.id" :value="w.id">{{ w.full_name }}</option>
                  </select>
                </div>

                <div class="col-md-12 mb-2">
                  <label class="form-label fw-bold text-dark small">Địa chỉ cụ thể (Số nhà, đường) <span class="text-danger">*</span></label>
                  <input type="text" class="form-control bg-white border border-secondary-subtle shadow-none" v-model="addrForm.shipping_address" placeholder="VD: Số 12, Đường ABCD" required>
                </div>
                
                <div class="col-12 mt-3" v-if="!addrForm.is_default">
                  <div class="form-check form-switch p-3 bg-light rounded-3 d-flex align-items-center gap-3 border border-secondary-subtle">
                    <input class="form-check-input fs-5 m-0" type="checkbox" id="flexSwitchCheckDefault" v-model="addrForm.set_as_default">
                    <label class="form-check-label fw-bold text-dark m-0 cursor-pointer" for="flexSwitchCheckDefault">Đặt làm địa chỉ mặc định</label>
                  </div>
                </div>
              </div>
              <div class="text-end mt-4 pt-3 border-top">
                <button type="button" class="btn btn-light px-4 fw-bold me-2 shadow-sm" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="submit" class="btn btn-brand px-5 fw-bold text-white shadow-sm" :disabled="isSavingAddr">
                  <span v-if="isSavingAddr" class="spinner-border spinner-border-sm me-1"></span> LƯU ĐỊA CHỈ
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
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import defaultAvatar from '../../../../assets/images/defaults/avatar1.png';

const route = useRoute();
const router = useRouter();
const isLoaded = ref(false);
const isSavingUser = ref(false);

const activeTab = ref('info'); 

const previewAvatar = ref(defaultAvatar);
const selectedFile = ref(null);
const isRemoveAvatar = ref(false);

const form = ref({ fullName: '', email: '', password: '', phone: '', status: '', gender: '', birthday: '' });

// STATE API TỈNH THÀNH (ESGOO)
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);
const selectedCityId = ref('');
const selectedDistrictId = ref('');
const selectedWardId = ref('');

// QUẢN LÝ SỔ ĐỊA CHỈ
const userAddresses = ref([]);
const isSavingAddr = ref(false);
const addrModalMode = ref('add');
let addressModalInstance = null;
const addrForm = ref({ id: null, customer_name: '', customer_phone: '', shipping_address: '', city: '', district: '', ward: '', is_default: 0, set_as_default: false });

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const getHeadersJson = () => ({ 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

// Helper tìm địa điểm theo tên (Phục vụ cho việc Edit khớp lại ID)
const findLocationByName = (list, name) => {
  if (!name || !list) return null;
  return list.find(item => item.full_name === name || item.name === name || name.includes(item.name));
};

// FETCH TỈNH/THÀNH
const fetchProvinces = async () => {
  try {
    const res = await fetch('https://esgoo.net/api-tinhthanh/1/0.htm');
    const data = await res.json();
    if(data.error === 0) provinces.value = data.data;
  } catch(e) { console.error("Lỗi tải Tỉnh Thành", e); }
};

// SỰ KIỆN KHI CHỌN DROPDOWN
const onCityChange = async () => {
  districts.value = []; wards.value = [];
  selectedDistrictId.value = ''; selectedWardId.value = '';
  addrForm.value.city = provinces.value.find(p => p.id === selectedCityId.value)?.full_name || '';
  addrForm.value.district = ''; addrForm.value.ward = '';

  if (selectedCityId.value) {
    try {
      const res = await fetch(`https://esgoo.net/api-tinhthanh/2/${selectedCityId.value}.htm`);
      const data = await res.json();
      if(data.error === 0) districts.value = data.data;
    } catch(e) {}
  }
};

const onDistrictChange = async () => {
  wards.value = []; selectedWardId.value = '';
  addrForm.value.district = districts.value.find(d => d.id === selectedDistrictId.value)?.full_name || '';
  addrForm.value.ward = '';

  if (selectedDistrictId.value) {
    try {
      const res = await fetch(`https://esgoo.net/api-tinhthanh/3/${selectedDistrictId.value}.htm`);
      const data = await res.json();
      if(data.error === 0) wards.value = data.data;
    } catch(e) {}
  }
};

const onWardChange = () => {
  addrForm.value.ward = wards.value.find(w => w.id === selectedWardId.value)?.full_name || '';
};

// LẤY DỮ LIỆU USER VÀ ĐỊA CHỈ
const fetchUser = async () => {
  try {
    const res = await fetch(`http://127.0.0.1:8000/api/admin/users/${route.params.id}`, { headers: getHeaders() });
    if (res.ok) {
      const u = (await res.json()).data;
      form.value = { fullName: u.fullName, email: u.email, phone: u.phone, status: u.status, gender: u.gender || '', birthday: u.birthday || '', password: '' };
      previewAvatar.value = u.avatar_url ? `http://127.0.0.1:8000/storage/${u.avatar_url}` : defaultAvatar;
      
      // Load địa chỉ và sắp xếp mặc định lên đầu
      userAddresses.value = (u.addresses || []).sort((a, b) => b.is_default - a.is_default);
    } else {
      router.push({ name: 'admin-users' });
    }
  } catch (err) { console.error(err); } finally { isLoaded.value = true; }
};

// ========================
// LOGIC CỦA USER FORM
// ========================
const handleAvatarChange = (e) => {
  const file = e.target.files[0];
  if (file) { selectedFile.value = file; previewAvatar.value = URL.createObjectURL(file); isRemoveAvatar.value = false; }
};

const removeAvatar = () => { selectedFile.value = null; previewAvatar.value = defaultAvatar; isRemoveAvatar.value = true; };

const updateUser = async () => {
  isSavingUser.value = true;
  const formData = new FormData();
  formData.append('_method', 'PUT'); 
  Object.keys(form.value).forEach(key => {
    if (key === 'password' && !form.value.password) return; 
    if (key === 'email') return;
    formData.append(key, form.value[key] || '');
  });
  if (selectedFile.value) formData.append('avatar', selectedFile.value);
  if (isRemoveAvatar.value) formData.append('remove_avatar', 'true');

  try {
    const res = await fetch(`http://127.0.0.1:8000/api/admin/users/${route.params.id}`, { method: 'POST', headers: getHeaders(), body: formData });
    const data = await res.json();
    if (res.ok) {
      Swal.fire({ icon: 'success', title: 'Thành công', text: data.message, timer: 1500, showConfirmButton: false });
    } else { Swal.fire('Lỗi', data.message || Object.values(data.errors).flat().join('\n'), 'error'); }
  } catch (err) { Swal.fire('Lỗi', 'Mất kết nối', 'error'); } finally { isSavingUser.value = false; }
};

// ========================
// LOGIC MỞ MODAL SỔ ĐỊA CHỈ
// ========================
const openAddressModal = async (mode, addr = null) => {
  addrModalMode.value = mode;
  
  if (mode === 'add') {
    addrForm.value = { id: null, customer_name: form.value.fullName, customer_phone: form.value.phone, shipping_address: '', city: '', district: '', ward: '', is_default: 0, set_as_default: false };
    selectedCityId.value = '';
    selectedDistrictId.value = '';
    selectedWardId.value = '';
    districts.value = [];
    wards.value = [];
  } else {
    addrForm.value = { ...addr, set_as_default: false };
    
    // Khớp tên địa chỉ cũ trong DB với ID của API để set dropdown
    selectedCityId.value = '';
    selectedDistrictId.value = '';
    selectedWardId.value = '';
    districts.value = [];
    wards.value = [];

    if (addr.city && provinces.value.length > 0) {
      const cityObj = findLocationByName(provinces.value, addr.city);
      if (cityObj) {
        selectedCityId.value = cityObj.id;
        try {
          const res = await fetch(`https://esgoo.net/api-tinhthanh/2/${selectedCityId.value}.htm`);
          const data = await res.json();
          if(data.error === 0) {
            districts.value = data.data;
            const distObj = findLocationByName(districts.value, addr.district);
            if (distObj) {
              selectedDistrictId.value = distObj.id;
              const res2 = await fetch(`https://esgoo.net/api-tinhthanh/3/${selectedDistrictId.value}.htm`);
              const data2 = await res2.json();
              if(data2.error === 0) {
                wards.value = data2.data;
                const wardObj = findLocationByName(wards.value, addr.ward);
                if (wardObj) selectedWardId.value = wardObj.id;
              }
            }
          }
        } catch(e) {}
      }
    }
  }
  
  if (!addressModalInstance) addressModalInstance = new window.bootstrap.Modal(document.getElementById('addressModal'));
  addressModalInstance.show();
};

const saveAddress = async () => {
  isSavingAddr.value = true;
  const url = addrModalMode.value === 'add' 
    ? `http://127.0.0.1:8000/api/admin/users/${route.params.id}/addresses`
    : `http://127.0.0.1:8000/api/admin/addresses/${addrForm.value.id}`;
  
  const method = addrModalMode.value === 'add' ? 'POST' : 'PUT';
  const payload = { ...addrForm.value, is_default: addrForm.value.set_as_default ? 1 : addrForm.value.is_default };

  try {
    const res = await fetch(url, { method, headers: getHeadersJson(), body: JSON.stringify(payload) });
    if (res.ok) {
      addressModalInstance.hide();
      fetchUser(); // Load lại toàn bộ data để update giao diện
      Swal.fire({ icon: 'success', title: 'Thành công', text: 'Đã lưu địa chỉ', timer: 1500, showConfirmButton: false });
    }
  } catch (err) {} finally { isSavingAddr.value = false; }
};

const deleteAddress = (id) => {
  Swal.fire({ title: 'Xóa địa chỉ?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Xóa' }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const res = await fetch(`http://127.0.0.1:8000/api/admin/addresses/${id}`, { method: 'DELETE', headers: getHeadersJson() });
        const data = await res.json();
        if (res.ok) fetchUser();
        else Swal.fire('Lỗi', data.message, 'error');
      } catch (err) {}
    }
  });
};

const setDefaultAddress = async (id) => {
  try {
    const res = await fetch(`http://127.0.0.1:8000/api/admin/addresses/${id}/default`, { method: 'PUT', headers: getHeadersJson() });
    if (res.ok) {
      fetchUser();
      Swal.fire({ icon: 'success', title: 'Thành công', text: 'Đã thay đổi địa chỉ mặc định', timer: 1500, showConfirmButton: false });
    }
  } catch (err) {}
};

onMounted(() => {
  fetchProvinces(); // Load API Tỉnh Thành ngay từ đầu
  fetchUser();
});
</script>

<style scoped>
.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, #009981 30%, #4dffdf 50%, #009981 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }

.bg-brand { background-color: #009981 !important; } 
.text-brand { color: #009981 !important; } 
.border-brand { border-color: #009981 !important; }

.btn-brand { background-color: #009981; transition: 0.2s; border: none; } 
.btn-brand:hover { background-color: #007a67; }

.hover-brand-btn:hover { color: #009981 !important; border-color: #009981 !important; background-color: #f8f9fa !important; }

.form-control:focus, .form-select:focus { border-color: #009981; box-shadow: 0 0 0 0.25rem rgba(0, 153, 129, 0.25); }
.cursor-not-allowed { cursor: not-allowed; opacity: 0.7; }
.cursor-pointer { cursor: pointer; transition: transform 0.2s; }
.cursor-pointer:hover { transform: scale(1.1); }

/* THIẾT KẾ TAB MỚI: CHUẨN XÁC, RÕ RÀNG & KHÔNG BỊ MẤT GẠCH CHÂN */
.nav-tabs-custom { border-bottom: 2px solid #dee2e6; display: flex; gap: 10px; padding-left: 0; list-style: none; }
.nav-tabs-custom .nav-link { color: #6c757d; border: none; border-bottom: 3px solid transparent; padding: 12px 20px; font-weight: 600; transition: all 0.3s ease; background: transparent; cursor: pointer; }
.nav-tabs-custom .nav-link:hover { color: #009981; }
.nav-tabs-custom .nav-link.active { color: #009981; border-bottom: 3px solid #009981; }

.tab-badge { font-size: 0.75rem; font-weight: 600; transition: all 0.2s ease; }

/* Styling Sổ địa chỉ */
.address-card:hover { box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,0.05) !important; transform: translateY(-2px); }
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
.border-dashed { border-style: dashed !important; border-color: #dee2e6 !important; }
.transition-all { transition: all 0.3s ease; }
</style>