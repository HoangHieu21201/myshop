<template>
    <div class="user-create-wrapper">
        <div class="container-fluid py-4">
            <div class="d-flex align-items-center mb-4">
                <router-link :to="{ name: 'admin-users' }"
                    class="btn btn-light shadow-sm me-3 rounded-circle d-flex align-items-center justify-content-center"
                    style="width: 40px; height: 40px;">
                    <i class="bi bi-arrow-left fw-bold"></i>
                </router-link>
                <div>
                    <h3 class="fw-bold text-dark mb-0">Thêm Khách Hàng Mới</h3>
                    <p class="text-muted mb-0 small">Tạo tài khoản người dùng mua sắm cho hệ thống ThinkHub</p>
                </div>
            </div>

            <!-- FIX 1: Tắt autocomplete ở cấp độ form -->
            <form @submit.prevent="saveUser" autocomplete="off">
                <div class="row g-4">
                    <!-- Cột Trái: Avatar & Trạng thái -->
                    <div class="col-md-4 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 text-center p-4 h-100">
                            <label class="form-label fw-bold mb-3">Ảnh đại diện</label>
                            <div class="position-relative d-inline-block mx-auto mb-4">
                                <img :src="previewAvatar"
                                    class="rounded-circle shadow-sm border border-2 border-white object-fit-cover"
                                    style="width: 150px; height: 150px;" alt="Avatar">
                                <label for="avatarUpload"
                                    class="position-absolute bottom-0 end-0 bg-brand rounded-circle shadow-sm p-2 text-white"
                                    style="cursor: pointer;">
                                    <i class="bi bi-camera-fill fs-6"></i>
                                </label>
                                <input type="file" id="avatarUpload" class="d-none" accept="image/png, image/jpeg"
                                    @change="handleAvatarChange">
                            </div>

                            <div class="mb-3 text-start">
                                <label class="form-label fw-bold">Trạng thái <span class="text-danger">*</span></label>
                                <select class="form-select" v-model="form.status" :class="{'is-invalid': errors.status}">
                                    <option value="active">Hoạt động (Active)</option>
                                    <option value="locked">Khóa (Locked)</option>
                                </select>
                                <div class="invalid-feedback">{{ errors.status?.[0] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Cột Phải: Thông tin chi tiết -->
                    <div class="col-md-8 col-lg-9">
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4 text-brand"><i class="bi bi-person-lines-fill me-2"></i>Thông tin cơ bản</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" v-model="form.fullName" :class="{'is-invalid': errors.fullName}"
                                            placeholder="Nhập họ tên" autocomplete="off">
                                        <div class="invalid-feedback">{{ errors.fullName?.[0] }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Số điện thoại</label>
                                        <input type="text" class="form-control" v-model="form.phone" :class="{'is-invalid': errors.phone}"
                                            placeholder="Nhập SĐT" autocomplete="off">
                                        <div class="invalid-feedback">{{ errors.phone?.[0] }}</div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-bold">Email đăng nhập <span class="text-danger">*</span></label>
                                        <!-- FIX 1: Hack readonly để lừa trình duyệt không auto-fill, khi click vào sẽ gỡ readonly -->
                                        <input type="email" class="form-control bg-white" v-model="form.email" :class="{'is-invalid': errors.email}"
                                            placeholder="name@domain.com" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                                        <div class="invalid-feedback">{{ errors.email?.[0] }}</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Mật khẩu khởi tạo <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control bg-white" v-model="form.password" :class="{'is-invalid': errors.password}"
                                            placeholder="Tối thiểu 8 ký tự" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');">
                                        <div class="invalid-feedback">{{ errors.password?.[0] }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control bg-white" v-model="form.password_confirmation" 
                                            placeholder="Nhập lại mật khẩu" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Giới tính</label>
                                        <select class="form-select" v-model="form.gender">
                                            <option value="">-- Chưa cập nhật --</option>
                                            <option value="Nam">Nam</option>
                                            <option value="Nữ">Nữ</option>
                                            <option value="Khác">Khác</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Ngày sinh</label>
                                        <input type="date" class="form-control" v-model="form.birthday">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4 text-brand"><i class="bi bi-geo-alt-fill me-2"></i>Địa chỉ mặc định (Tùy chọn)</h5>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-bold">Địa chỉ chi tiết</label>
                                        <input type="text" class="form-control" v-model="form.shipping_address" placeholder="Số nhà, tên đường...">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Tỉnh/Thành phố</label>
                                        <input type="text" class="form-control" v-model="form.city" placeholder="Ví dụ: Hà Nội">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Quận/Huyện</label>
                                        <input type="text" class="form-control" v-model="form.district" placeholder="Ví dụ: Cầu Giấy">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Phường/Xã</label>
                                        <input type="text" class="form-control" v-model="form.ward" placeholder="Ví dụ: Dịch Vọng">
                                    </div>
                                </div>

                                <hr class="text-muted opacity-25 my-4">
                                <div class="text-end">
                                    <router-link :to="{ name: 'admin-users' }"
                                        class="btn btn-light me-2 px-4 shadow-sm fw-bold">Hủy</router-link>
                                    <button type="submit" class="btn btn-brand px-5 fw-bold text-white shadow-sm"
                                        :disabled="isSaving">
                                        <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> 
                                        {{ isSaving ? 'ĐANG TẠO...' : 'XÁC NHẬN THÊM' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import defaultAvatar from '../../../../assets/images/defaults/avatar1.png';

const router = useRouter();
const isSaving = ref(false);
const previewAvatar = ref(defaultAvatar);
const selectedFile = ref(null);
const errors = ref({});

const form = ref({
    fullName: '', email: '', password: '', password_confirmation: '', phone: '', status: 'active', gender: '', birthday: '',
    shipping_address: '', city: '', district: '', ward: ''
});

const handleAvatarChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        selectedFile.value = file;
        previewAvatar.value = URL.createObjectURL(file);
    }
};

const saveUser = async () => {
    isSaving.value = true;
    errors.value = {}; 
    
    const formData = new FormData();
    Object.keys(form.value).forEach(key => {
        if (form.value[key] !== null && form.value[key] !== '') {
            formData.append(key, form.value[key]);
        }
    });
    
    if (selectedFile.value) formData.append('avatar', selectedFile.value);

    try {
        const res = await fetch('http://127.0.0.1:8000/api/admin/users', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await res.json();
        
        if (res.ok) {
            Swal.fire({ icon: 'success', title: 'Thành công', text: data.message, timer: 1500, showConfirmButton: false });
            router.push({ name: 'admin-users' });
        } else if (res.status === 422) {
            errors.value = data.errors;
            Swal.fire('Chú ý', 'Vui lòng kiểm tra lại các thông tin nhập liệu.', 'warning');
        } else {
            Swal.fire('Lỗi', data.message || 'Có lỗi xảy ra', 'error');
        }
    } catch (err) { 
        Swal.fire('Lỗi', 'Mất kết nối server', 'error'); 
    } finally { 
        isSaving.value = false; 
    }
};
</script>

<style scoped>
.btn-brand,
.bg-brand { background-color: #009981; transition: 0.2s; border: none; }
.btn-brand:hover { background-color: #007a67; }
.btn-brand:disabled { background-color: #a5d6cd; }
.form-control:focus, .form-select:focus { border-color: #009981; box-shadow: 0 0 0 0.25rem rgba(0, 153, 129, 0.25); }
.text-brand { color: #009981; }
.invalid-feedback { font-size: 0.8rem; font-weight: 500; }
</style>