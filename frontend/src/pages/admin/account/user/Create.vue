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

            <form @submit.prevent="saveUser">
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
                                <select class="form-select" v-model="form.status" required>
                                    <option value="active">Hoạt động (Active)</option>
                                    <option value="locked">Khóa (Locked)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Cột Phải: Thông tin chi tiết -->
                    <div class="col-md-8 col-lg-9">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4 p-lg-5">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Họ và tên <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" v-model="form.fullName"
                                            placeholder="Nhập họ tên" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Số điện thoại</label>
                                        <input type="text" class="form-control" v-model="form.phone"
                                            placeholder="Nhập SĐT">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Email đăng nhập <span
                                                class="text-danger">*</span></label>
                                        <input type="email" class="form-control" v-model="form.email"
                                            placeholder="name@domain.com" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Mật khẩu khởi tạo <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" v-model="form.password"
                                            placeholder="Tối thiểu 8 ký tự" required minlength="8">
                                    </div>

                                    <div class="col-md-6 mb-3 mt-2">
                                        <label class="form-label fw-bold">Giới tính</label>
                                        <select class="form-select" v-model="form.gender">
                                            <option value="">-- Chưa cập nhật --</option>
                                            <option value="Nam">Nam</option>
                                            <option value="Nữ">Nữ</option>
                                            <option value="Khác">Khác</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3 mt-2">
                                        <label class="form-label fw-bold">Ngày sinh</label>
                                        <input type="date" class="form-control" v-model="form.birthday">
                                    </div>
                                </div>

                                <hr class="text-muted opacity-25 my-4">
                                <div class="text-end">
                                    <router-link :to="{ name: 'admin-users' }"
                                        class="btn btn-light me-2 px-4">Hủy</router-link>
                                    <button type="submit" class="btn btn-brand px-5 fw-bold text-white shadow-sm"
                                        :disabled="isSaving">
                                        <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> XÁC
                                        NHẬN THÊM
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
// Đường dẫn chuẩn file ảnh mặc định
import defaultAvatar from '../../../../assets/images/defaults/avatar1.png';

const router = useRouter();
const isSaving = ref(false);
const previewAvatar = ref(defaultAvatar);
const selectedFile = ref(null);

const form = ref({
    fullName: '', email: '', password: '', phone: '', status: 'active', gender: '', birthday: ''
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
    const formData = new FormData();
    Object.keys(form.value).forEach(key => formData.append(key, form.value[key]));
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
        } else {
            Swal.fire('Lỗi', data.message || Object.values(data.errors).flat().join('\n'), 'error');
        }
    } catch (err) { Swal.fire('Lỗi', 'Mất kết nối', 'error'); } finally { isSaving.value = false; }
};
</script>

<style scoped>
.btn-brand,
.bg-brand {
    background-color: #009981;
    transition: 0.2s;
    border: none;
}

.btn-brand:hover {
    background-color: #007a67;
}

.form-control:focus,
.form-select:focus {
    border-color: #009981;
    box-shadow: 0 0 0 0.25rem rgba(0, 153, 129, 0.25);
}
</style>