<template>
  <div class="profile-page bg-light-custom font-sans pb-5 min-vh-100 position-relative">
    
    <!-- Tiêu đề trang -->
    <section class="py-5 bg-white text-center shadow-sm mb-5">
      <div class="container py-3">
        <h1 class="display-6 font-serif text-main mb-3">Tài Khoản Của Tôi</h1>
        <div class="divider bg-accent mx-auto"></div>
      </div>
    </section>

    <div class="container">
      <div v-if="!isLoggedIn" class="text-center py-5 bg-white shadow-sm p-5 border border-light mb-5">
        <h4 class="text-danger-custom mb-3">Bạn chưa đăng nhập!</h4>
        <p class="text-secondary mb-4">Vui lòng đăng nhập để xem và chỉnh sửa thông tin cá nhân.</p>
        <router-link to="/login" class="btn btn-main px-5 py-2 text-uppercase">Đăng nhập ngay</router-link>
      </div>

      <div v-else class="row g-4 g-lg-5">
        
        <!-- SIDEBAR MENU BÊN TRÁI -->
        <div class="col-lg-3">
          <div class="bg-white p-4 shadow-sm border border-light text-center mb-4 rounded-3">
            
            <!-- Avatar (Có thể click để đổi ảnh) -->
            <div class="avatar-wrapper mx-auto mb-4 position-relative rounded-circle overflow-hidden bg-light" 
                 style="width: 130px; height: 130px; cursor: pointer; border: 4px solid #fff; box-shadow: 0 0 0 2px #e7ce7d, 0 8px 16px rgba(0,0,0,0.1);" 
                 @click="triggerFileInput" title="Click để thay đổi ảnh đại diện">
              
              <!-- ĐÃ FIX: Xóa padding, ảnh lấp đầy vòng tròn, không bị bóp méo -->
              <img :src="previewAvatar || getImageUrl(form.avatar_url) || 'https://ui-avatars.com/api/?name=' + form.fullName + '&background=9f273b&color=fff'" 
                   alt="Avatar" class="w-100 h-100 object-fit-cover" style="object-position: center;">
              
              <!-- Lớp phủ khi hover -->
              <div class="avatar-upload-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="small mt-1 fw-medium" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đổi ảnh</span>
              </div>
            </div>
            
            <!-- Thẻ input file ẩn -->
            <input type="file" ref="fileInput" class="d-none" accept="image/*" @change="handleFileChange">

            <!-- ĐÃ FIX: Làm font chữ tên người dùng to và nổi bật hơn -->
            <h5 class="font-serif text-dark mb-1 fw-bold" style="font-size: 1.25rem;">{{ form.fullName || 'Thành viên SORA' }}</h5>
            <p class="text-muted small fw-light mb-0">{{ form.email }}</p>
          </div>

          <div class="bg-white shadow-sm border border-light overflow-hidden rounded-3">
            <ul class="list-unstyled mb-0 profile-menu">
              <li>
                <router-link to="/profile" class="d-flex align-items-center p-3 text-decoration-none active-menu">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-3"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                  Hồ sơ cá nhân
                </router-link>
              </li>
              <li>
                <router-link to="/order" class="d-flex align-items-center p-3 text-decoration-none text-secondary">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-3"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                  Đơn mua của tôi
                </router-link>
              </li>
              <li>
                <router-link to="/favourite" class="d-flex align-items-center p-3 text-decoration-none text-secondary">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-3"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                  Sản phẩm yêu thích
                </router-link>
              </li>
              <li class="border-top">
                <a href="#" @click.prevent="logout" class="d-flex align-items-center p-3 text-decoration-none text-danger-custom">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-3"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                  Đăng xuất
                </a>
              </li>
            </ul>
          </div>
        </div>

        <!-- CÁC FORM CẬP NHẬT BÊN PHẢI -->
        <div class="col-lg-9">
          <div class="bg-white p-4 p-md-5 shadow-sm border border-light mb-4 rounded-3">
            
            <!-- FORM 1: THÔNG TIN CÁ NHÂN -->
            <h3 class="h4 font-serif text-dark mb-1">Hồ Sơ Của Tôi</h3>
            <p class="text-secondary fw-light border-bottom pb-3 mb-4">Quản lý thông tin hồ sơ để bảo mật tài khoản</p>

            <div v-if="isLoading" class="text-center py-5">
              <div class="spinner-border text-accent" role="status"></div>
            </div>

            <form v-else @submit.prevent="updateProfile">
              <div class="row mb-4 align-items-center">
                <label class="col-sm-3 col-form-label text-sm-end text-secondary fw-medium">Tên Đăng Nhập / Email</label>
                <div class="col-sm-9 col-md-7">
                  <input type="email" class="form-control bg-light text-muted" :value="form.email" disabled>
                  <small class="text-muted fw-light mt-1 d-block">Email không thể thay đổi</small>
                </div>
              </div>

              <div class="row mb-4 align-items-center">
                <label for="fullName" class="col-sm-3 col-form-label text-sm-end text-secondary fw-medium">Họ Và Tên</label>
                <div class="col-sm-9 col-md-7">
                  <input type="text" class="form-control custom-input" id="fullName" v-model="form.fullName" required placeholder="Nhập họ và tên của bạn">
                </div>
              </div>

              <div class="row mb-4 align-items-center">
                <label for="phone" class="col-sm-3 col-form-label text-sm-end text-secondary fw-medium">Số Điện Thoại</label>
                <div class="col-sm-9 col-md-7">
                  <input type="tel" class="form-control custom-input" id="phone" v-model="form.phone" placeholder="Nhập số điện thoại liên hệ">
                </div>
              </div>

              <div class="row mb-4 align-items-center">
                <label class="col-sm-3 col-form-label text-sm-end text-secondary fw-medium">Giới Tính</label>
                <div class="col-sm-9 col-md-7 d-flex gap-4 pt-2">
                  <div class="form-check custom-radio">
                    <input class="form-check-input" type="radio" name="gender" id="genderMale" value="Nam" v-model="form.gender">
                    <label class="form-check-label text-secondary" for="genderMale">Nam</label>
                  </div>
                  <div class="form-check custom-radio">
                    <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Nữ" v-model="form.gender">
                    <label class="form-check-label text-secondary" for="genderFemale">Nữ</label>
                  </div>
                  <div class="form-check custom-radio">
                    <input class="form-check-input" type="radio" name="gender" id="genderOther" value="Khác" v-model="form.gender">
                    <label class="form-check-label text-secondary" for="genderOther">Khác</label>
                  </div>
                </div>
              </div>

              <div class="row mb-4 align-items-center">
                <label for="birthday" class="col-sm-3 col-form-label text-sm-end text-secondary fw-medium">Ngày Sinh</label>
                <div class="col-sm-9 col-md-7">
                  <input type="date" class="form-control custom-input" id="birthday" v-model="form.birthday">
                </div>
              </div>

              <div class="row">
                <div class="col-sm-9 offset-sm-3">
                  <button type="submit" class="btn btn-main px-5 py-2 text-uppercase fw-medium rounded-0" :disabled="isSaving" style="letter-spacing: 0.1em;">
                    <span v-if="isSaving" class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Lưu Thay Đổi
                  </button>
                </div>
              </div>
            </form>

            <!-- FORM 2: ĐỔI MẬT KHẨU -->
            <div class="mt-5 pt-4 border-top">
              <h3 class="h4 font-serif text-dark mb-1">Đổi Mật Khẩu</h3>
              <p class="text-secondary fw-light mb-4">Để bảo mật tài khoản, vui lòng không chia sẻ mật khẩu cho người khác</p>
              
              <form @submit.prevent="changePassword">
                <div class="row mb-4 align-items-center">
                  <label for="currentPassword" class="col-sm-3 col-form-label text-sm-end text-secondary fw-medium">Mật Khẩu Hiện Tại</label>
                  <div class="col-sm-9 col-md-7">
                    <input type="password" class="form-control custom-input" id="currentPassword" v-model="passwordForm.current_password" required placeholder="Nhập mật khẩu hiện tại">
                  </div>
                </div>
                
                <div class="row mb-4 align-items-center">
                  <label for="newPassword" class="col-sm-3 col-form-label text-sm-end text-secondary fw-medium">Mật Khẩu Mới</label>
                  <div class="col-sm-9 col-md-7">
                    <input type="password" class="form-control custom-input" id="newPassword" v-model="passwordForm.password" required minlength="6" placeholder="Nhập mật khẩu mới (ít nhất 6 ký tự)">
                  </div>
                </div>

                <div class="row mb-4 align-items-center">
                  <label for="confirmPassword" class="col-sm-3 col-form-label text-sm-end text-secondary fw-medium">Xác Nhận Mật Khẩu</label>
                  <div class="col-sm-9 col-md-7">
                    <input type="password" class="form-control custom-input" id="confirmPassword" v-model="passwordForm.password_confirmation" required placeholder="Nhập lại mật khẩu mới">
                  </div>
                </div>

                <div class="row">
                  <div class="col-sm-9 offset-sm-3">
                    <button type="submit" class="btn btn-outline-main px-5 py-2 text-uppercase fw-medium rounded-0" :disabled="isChangingPassword" style="letter-spacing: 0.1em;">
                      <span v-if="isChangingPassword" class="spinner-border spinner-border-sm me-2" role="status"></span>
                      Đổi Mật Khẩu
                    </button>
                  </div>
                </div>
              </form>
            </div>

          </div>
        </div>

      </div>
    </div>

    <!-- UI TOAST THÔNG BÁO CHUẨN XÁC -->
    <transition name="toast-slide">
      <div v-if="toast" class="custom-toast position-fixed bottom-0 end-0 m-4 p-3 shadow-lg d-flex align-items-center bg-white border-start border-4" 
           :class="toast.type === 'success' ? 'border-success' : 'border-danger'" 
           style="z-index: 9999; min-width: 320px; border-radius: 6px;">
        <div class="me-3 d-flex align-items-center justify-content-center">
          <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="#198754" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="#dc3545" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </div>
        <div class="flex-grow-1">
          <h6 class="mb-1 font-serif fw-bold" :class="toast.type === 'success' ? 'text-success' : 'text-danger'">
            {{ toast.type === 'success' ? 'Thành Công!' : 'Thất Bại!' }}
          </h6>
          <!-- Hiển thị nguyên nhân lỗi cực kỳ chi tiết -->
          <p class="mb-0 text-secondary small fw-light">{{ toast.message }}</p>
        </div>
        <button type="button" class="btn-close ms-2" @click="toast = null" style="font-size: 0.8rem;"></button>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();

// Trạng thái chung
const isLoggedIn = ref(false);
const isLoading = ref(true);
const isSaving = ref(false);
const isChangingPassword = ref(false);
const toast = ref(null);

// Khai báo quản lý upload Ảnh
const fileInput = ref(null);
const avatarFile = ref(null);
const previewAvatar = ref(null);

// Form data hồ sơ
const form = ref({
  fullName: '',
  email: '',
  phone: '',
  gender: '',
  birthday: '',
  avatar_url: ''
});

// Form data đổi mật khẩu
const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: ''
});

const apiBase = 'http://localhost:8000/api/client/profile'; 

// Quét Token
const getToken = () => {
  const commonKeys = ['access_token', 'token', 'auth_token', 'userToken', 'user_token'];
  for (const k of commonKeys) {
    const val = localStorage.getItem(k) || sessionStorage.getItem(k);
    if (val && val.length > 15) return val; 
  }
  for (let i = 0; i < localStorage.length; i++) {
    const key = localStorage.key(i);
    try {
      const parsed = JSON.parse(localStorage.getItem(key));
      if (parsed && typeof parsed === 'object') {
        if (parsed.access_token) return parsed.access_token;
        if (parsed.token) return parsed.token;
      }
    } catch(e) {}
  }
  return '';
};

// Hiển thị thông báo
const showToast = (message, type = 'success') => {
  toast.value = { message, type };
  // Kéo dài thời gian hiển thị lỗi để kịp đọc
  setTimeout(() => { toast.value = null; }, type === 'error' ? 5000 : 3000);
};

// Hàm lấy URL ảnh đầy đủ
const getImageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith('http')) return path;
  return `http://localhost:8000/storage/${path}`;
};

// Lấy thông tin Profile từ API
const fetchProfile = async () => {
  try {
    const response = await axios.get(apiBase, {
      headers: { Authorization: `Bearer ${getToken()}`, Accept: 'application/json' }
    });
    
    if (response.data.status) {
      const userData = response.data.data;
      form.value = {
        fullName: userData.fullName || '',
        email: userData.email || '',
        phone: userData.phone || '',
        gender: userData.gender || '',
        birthday: userData.birthday || '',
        avatar_url: userData.avatar_url || ''
      };
    }
  } catch (error) {
    console.error('Lỗi lấy profile:', error);
    if (error.response && error.response.status === 401) {
      isLoggedIn.value = false;
    }
  } finally {
    isLoading.value = false;
  }
};

// XỬ LÝ UPLOAD ẢNH (Giao diện)
const triggerFileInput = () => {
  fileInput.value.click();
};

const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    avatarFile.value = file;
    previewAvatar.value = URL.createObjectURL(file);
  }
};

// CẬP NHẬT PROFILE
const updateProfile = async () => {
  isSaving.value = true;
  try {
    const formData = new FormData();
    
    // Lưu ý: Đã xóa mọi đoạn set method ngầm. Chúng ta xài chuẩn POST.
    
    formData.append('fullName', form.value.fullName || '');
    if (form.value.phone) formData.append('phone', form.value.phone);
    if (form.value.gender) formData.append('gender', form.value.gender);
    if (form.value.birthday) formData.append('birthday', form.value.birthday);
    
    if (avatarFile.value) {
      formData.append('avatar', avatarFile.value);
    }

    const response = await axios.post(apiBase, formData, {
      headers: { 
        Authorization: `Bearer ${getToken()}`
      }
    });

    if (response.data.status) {
      showToast(response.data.message, 'success');
      
      if (response.data.data.avatar_url) {
        form.value.avatar_url = response.data.data.avatar_url;
      }
      
      previewAvatar.value = null;
      avatarFile.value = null;
      
      updateLocalAuthData(response.data.data);
    }
  } catch (error) {
    console.error('Lỗi cập nhật:', error);
    
    // NÂNG CẤP BẮT LỖI: Hiện chính xác dòng chữ Laravel chửi ra màn hình!
    if (error.response && error.response.status === 422) {
      const errors = error.response.data.errors;
      // Trích xuất lỗi đầu tiên từ mảng lỗi để hiển thị
      const firstErrorMsg = Object.values(errors)[0][0]; 
      showToast(`Lỗi dữ liệu: ${firstErrorMsg}`, 'error');
    } else {
      showToast('Có lỗi máy chủ xảy ra, vui lòng thử lại sau.', 'error');
    }
  } finally {
    isSaving.value = false;
  }
};

// ĐỔI MẬT KHẨU
const changePassword = async () => {
  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    showToast('Mật khẩu xác nhận không khớp!', 'error');
    return;
  }

  isChangingPassword.value = true;
  try {
    const response = await axios.post(`${apiBase}/password`, passwordForm.value, {
      headers: { Authorization: `Bearer ${getToken()}`, Accept: 'application/json' }
    });

    if (response.data.status) {
      showToast(response.data.message, 'success');
      passwordForm.value = {
        current_password: '',
        password: '',
        password_confirmation: ''
      };
    }
  } catch (error) {
    console.error('Lỗi đổi mật khẩu:', error);
    
    // BẮT LỖI TẬN RĂNG CHO ĐỔI MẬT KHẨU
    if (error.response && error.response.status === 400) {
      showToast(error.response.data.message, 'error'); 
    } else if (error.response && error.response.status === 422) {
      const errors = error.response.data.errors;
      const firstErrorMsg = Object.values(errors)[0][0]; 
      showToast(`Lỗi: ${firstErrorMsg}`, 'error');
    } else {
      showToast('Có lỗi xảy ra khi đổi mật khẩu. Vui lòng thử lại.', 'error');
    }
  } finally {
    isChangingPassword.value = false;
  }
};

// Hàm phụ trợ: Cập nhật tên mới vào LocalStorage
const updateLocalAuthData = (newData) => {
  try {
    let authState = JSON.parse(localStorage.getItem('auth') || '{}');
    if (authState.user) {
      authState.user.fullName = newData.fullName;
      if (newData.avatar_url) authState.user.avatar_url = newData.avatar_url;
      localStorage.setItem('auth', JSON.stringify(authState));
    }
  } catch(e) {}
};

// Đăng xuất
const logout = () => {
  localStorage.clear();
  sessionStorage.clear();
  window.location.href = '/login'; 
};

onMounted(() => {
  const token = getToken();
  if (token) {
    isLoggedIn.value = true;
    fetchProfile();
  } else {
    isLoggedIn.value = false;
    isLoading.value = false;
  }
});
</script>

<style scoped>
/* Màu sắc thương hiệu SORA */
.bg-light-custom { background-color: #faf9f8 !important; }
.bg-main { background-color: #9f273b !important; }
.text-main { color: #9f273b !important; }
.bg-accent { background-color: #e7ce7d !important; }
.text-accent { color: #e7ce7d !important; }
.text-danger-custom { color: #cc1e2e !important; }

.font-serif { font-family: "Playfair Display", "Merriweather", serif; }
.divider { width: 4rem; height: 2px; }
.object-fit-cover { object-fit: cover !important; }

/* Menu Sidebar */
.profile-menu a {
  transition: all 0.3s ease;
}
.profile-menu a:hover {
  background-color: #faf9f8;
  color: #9f273b !important;
}
.active-menu {
  color: #9f273b !important;
  background-color: #f8eaec;
  font-weight: 500;
  border-left: 3px solid #9f273b;
}

/* Avatar Hover Effect */
.avatar-upload-overlay {
  background-color: rgba(0, 0, 0, 0.45);
  opacity: 0;
  transition: opacity 0.3s ease;
}
.avatar-wrapper:hover .avatar-upload-overlay {
  opacity: 1;
}

/* Custom Input */
.custom-input {
  border-radius: 4px;
  border: 1px solid #ced4da;
  padding: 0.6rem 1rem;
  transition: all 0.3s ease;
}
.custom-input:focus {
  border-color: #9f273b;
  box-shadow: 0 0 0 0.2rem rgba(159, 39, 59, 0.15);
}

/* Custom Radio */
.custom-radio .form-check-input:checked {
  background-color: #9f273b;
  border-color: #9f273b;
}

/* Nút bấm (Button) */
.btn-main {
  background-color: #9f273b;
  color: white;
  border: 1px solid #9f273b;
  transition: all 0.3s ease;
}
.btn-main:hover {
  background-color: #cc1e2e;
  border-color: #cc1e2e;
  color: white;
}
.btn-outline-main {
  background-color: transparent;
  color: #9f273b;
  border: 1px solid #9f273b;
  transition: all 0.3s ease;
}
.btn-outline-main:hover {
  background-color: #9f273b;
  color: white;
}

/* Toast Slide Animation */
.toast-slide-enter-active,
.toast-slide-leave-active { transition: all 0.4s ease; }
.toast-slide-enter-from,
.toast-slide-leave-to { opacity: 0; transform: translateX(100px); }
</style>