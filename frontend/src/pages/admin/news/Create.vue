<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';

// CONFIGURATION
const apiUrl = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';
const BACKEND_URL = apiUrl.endsWith('/api') ? apiUrl.slice(0, -4) : apiUrl;
const router = useRouter();

const getHeaders = (isMultipart = false) => {
    const token = localStorage.getItem('admin_token') || localStorage.getItem('adminToken');
    return {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
        ...(isMultipart ? { 'Content-Type': 'multipart/form-data' } : { 'Content-Type': 'application/json' })
    };
};

const CATEGORIES = [
    { id: 'tech', name: 'Xu hướng trang sức' },
    { id: 'review', name: 'Bí quyết chọn trang sức' },
    { id: 'tips', name: 'Trang sức theo dịp' },
    { id: 'promo', name: 'Kiến thức đá quý & kim loại' },
    { id: 'other', name: 'Khác' }
];

const currentUser = ref({});
const isLoading = ref(false);
const selectedFile = ref(null);
const previewImage = ref(null);

const formData = reactive({
    title: '', excerpt: '', content: '', slug: '',
    status: 'pending', author_name: '', category: '',
    meta_title: '', meta_description: '', meta_keywords: ''
});

const errors = reactive({ title: '', slug: '', content: '', author_name: '' });

const hasRole = (allowedRoles) => {
    const userRoleId = Number(currentUser.value?.role_id);
    if (userRoleId === 1) return true;
    const roleName = userRoleId === 12 ? 'staff' : (userRoleId === 13 ? 'blogger' : '');
    return allowedRoles.includes(roleName);
};

const checkAuthState = () => {
    const data = localStorage.getItem('adminData') || localStorage.getItem('user_info');
    if (data) {
        const parsed = JSON.parse(data);
        currentUser.value = { ...parsed, name: parsed.fullname || parsed.full_name || parsed.name || 'Admin' };
        formData.author_name = currentUser.value.name;
    }
};

watch(() => formData.title, (newTitle) => {
    if (newTitle) {
        formData.slug = slugify(newTitle);
        if(!formData.meta_title) formData.meta_title = newTitle;
    }
});

watch(() => formData.excerpt, (newExcerpt) => {
     if (newExcerpt && !formData.meta_description) formData.meta_description = newExcerpt;
});

const slugify = (text) => text ? text.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\s+/g, '-').replace(/[^\w\-]+/g, '').replace(/\-\-+/g, '-').replace(/^-+/, '').replace(/-+$/, '') : '';

const validateImageFile = async (file) => {
    if (file.size > 10 * 1024 * 1024) return { valid: false, msg: 'Dung lượng tối đa 10MB.' };
    return { valid: true };
};

const handleFileChange = async (event) => {
    const file = event.target.files[0];
    if (file) {
        const checkResult = await validateImageFile(file);
        if (!checkResult.valid) { Swal.fire('Lỗi File', checkResult.msg, 'error'); event.target.value = null; return; }
        selectedFile.value = file; 
        previewImage.value = URL.createObjectURL(file);
    }
};

const resetImageSelect = () => {
    selectedFile.value = null;
    previewImage.value = null;
    const fileInput = document.getElementById('imageInput');
    if (fileInput) fileInput.value = '';
};

const validateForm = () => {
    Object.assign(errors, { title: '', slug: '', content: '', author_name: '' });
    let isValid = true;
    if (!formData.title.trim()) { errors.title = 'Tiêu đề là bắt buộc.'; isValid = false; }
    if (!formData.slug.trim()) { errors.slug = 'Slug là bắt buộc.'; isValid = false; }
    if (!formData.author_name.trim()) { errors.author_name = 'Tên tác giả bắt buộc.'; isValid = false; }
    const strippedContent = formData.content.replace(/<[^>]*>/g, '').trim();
    if (!strippedContent && !formData.content.includes('<img')) { errors.content = 'Nội dung trống.'; isValid = false; }
    return isValid;
};

const handleSave = async () => {
    if (!formData.excerpt?.trim() && formData.content) {
        const plainText = formData.content.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
        formData.excerpt = plainText.slice(0, 160) + (plainText.length > 160 ? '...' : '');
    }
    if (!validateForm()) return;
    
    isLoading.value = true;
    const payload = new FormData();
    Object.keys(formData).forEach(key => payload.append(key, formData[key] || ''));
    if (selectedFile.value) payload.append('image', selectedFile.value);
    
    try {
        await axios.post(`${apiUrl}/admin/news`, payload, { headers: getHeaders(true) });
        Swal.fire({ icon: 'success', title: 'Đăng bài thành công!', timer: 1500, showConfirmButton: false });
        router.push('/admin/news');
    } catch (error) {
        if (error.response?.status === 422) {
            const errs = error.response.data.errors;
            let html = '<ul>' + Object.values(errs).map(e => `<li class="text-danger text-start">${e[0]}</li>`).join('') + '</ul>';
            Swal.fire({ title: 'Dữ liệu lỗi', html: html, icon: 'warning' });
        } else {
            Swal.fire('Lỗi Server', error.response?.data?.message || 'Có lỗi xảy ra', 'error');
        }
    } finally { isLoading.value = false; }
};

onMounted(() => {
    checkAuthState();
});
</script>

<template>
    <div class="news-create-page p-4 min-vh-100">
        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h3 font-serif mb-1 fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i> Viết bài mới</h2>
                <p class="text-muted small mb-0">Tạo nội dung hấp dẫn để thu hút khách hàng.</p>
            </div>
            <button @click="router.push('/admin/news')" class="btn btn-light border shadow-sm rounded-3">
                <i class="bi bi-arrow-left me-1"></i> Quay lại
            </button>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm rounded-4 border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label required fw-bold">Tiêu đề bài viết</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0" :class="{ 'is-invalid': errors.title }" v-model="formData.title" placeholder="Nhập tiêu đề hấp dẫn...">
                            <div class="invalid-feedback">{{ errors.title }}</div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Danh mục</label>
                                <select class="form-select border-0 bg-light" v-model="formData.category">
                                    <option value="">-- Chọn danh mục --</option>
                                    <option v-for="cat in CATEGORIES" :key="cat.id" :value="cat.name">{{ cat.name }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required fw-bold text-muted">Slug (Đường dẫn)</label>
                                <input type="text" class="form-control border-0 bg-light text-muted" :class="{ 'is-invalid': errors.slug }" v-model="formData.slug" readonly>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Mô tả ngắn (Trích dẫn)</label>
                            <textarea class="form-control border-0 bg-light" rows="3" v-model="formData.excerpt" placeholder="Tóm tắt nội dung chính..."></textarea>
                            <div class="form-text small mt-1">Hệ thống sẽ tự động lấy 160 ký tự đầu của bài viết nếu bạn để trống.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required fw-bold">Nội dung chi tiết</label>
                            <textarea class="form-control border-0 bg-light" :class="{ 'is-invalid': errors.content }" rows="15" v-model="formData.content" placeholder="Nhập nội dung bài viết ở đây... (Hỗ trợ mã HTML)"></textarea>
                            <div class="invalid-feedback" v-if="errors.content">{{ errors.content }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CỘT RIGHT: CẤU HÌNH & XUẤT BẢN -->
            <div class="col-lg-4">
                <div class="card shadow-sm rounded-4 border-0 mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 fw-bold">Trạng thái & Tác giả</div>
                    <div class="card-body">
                        <div class="mb-3" v-if="hasRole(['admin'])">
                            <label class="form-label text-muted small">Trạng thái hiển thị</label>
                            <select class="form-select border-0 bg-light" v-model="formData.status">
                                <option value="pending">Đợi duyệt</option>
                                <option value="published">Xuất bản ngay</option>
                                <option value="draft">Lưu nháp (Ẩn)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required text-muted small">Bút danh tác giả</label>
                            <input type="text" class="form-control border-0 bg-light" :class="{ 'is-invalid': errors.author_name }" v-model="formData.author_name">
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm rounded-4 border-0 mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 fw-bold">Ảnh đại diện</div>
                    <div class="card-body">
                        <div class="text-center position-relative border border-dashed rounded-3 p-2 bg-light mb-3" style="min-height: 180px; display: flex; align-items: center; justify-content: center;">
                            <img v-if="previewImage" :src="previewImage" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                            <span v-else class="text-muted"><i class="bi bi-image fs-1 d-block opacity-50"></i> Chưa chọn ảnh</span>
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 rounded-circle shadow" v-if="selectedFile" @click="resetImageSelect" title="Xóa ảnh"><i class="bi bi-x"></i></button>
                        </div>
                        <input type="file" class="form-control border-0 bg-light" id="imageInput" accept="image/*" @change="handleFileChange">
                    </div>
                </div>

                <div class="card shadow-sm rounded-4 border-0 mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 fw-bold text-primary"><i class="bi bi-google me-1"></i> Tối ưu SEO (Tùy chọn)</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Meta Title</label>
                            <input type="text" class="form-control form-control-sm border-0 bg-light" v-model="formData.meta_title" placeholder="Mặc định lấy tiêu đề bài viết">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Meta Keywords</label>
                            <input type="text" class="form-control form-control-sm border-0 bg-light" v-model="formData.meta_keywords" placeholder="Ví dụ: trang sức, kim cương...">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small text-muted">Meta Description</label>
                            <textarea class="form-control form-control-sm border-0 bg-light" rows="3" v-model="formData.meta_description" placeholder="Mặc định lấy mô tả ngắn..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER ACTIONS -->
        <div class="card shadow-sm rounded-4 border-0 mt-2 sticky-bottom" style="bottom: 20px; z-index: 10;">
            <div class="card-body p-3 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light border px-4" @click="router.push('/admin/news')">Hủy bỏ</button>
                <button type="button" class="btn btn-primary px-5 fw-bold" @click="handleSave" :disabled="isLoading">
                    <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
                    <i v-else class="bi bi-send-fill me-2"></i> Lưu bài viết
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.font-serif { font-family: "Playfair Display", serif; }
.required::after { content: " *"; color: #dc3545; }
.border-dashed { border-style: dashed !important; border-width: 2px !important; border-color: #dee2e6 !important; }

.btn-primary { background-color: #009981 !important; border-color: #009981 !important; color: white !important; }
.btn-primary:hover { background-color: #007a67 !important; border-color: #007a67 !important; }
.text-primary { color: #009981 !important; }

textarea.form-control { resize: vertical; }
</style>