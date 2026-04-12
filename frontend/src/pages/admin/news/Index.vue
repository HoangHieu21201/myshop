<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';

// CONFIGURATION
const apiUrl = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';
const FRONTEND_URL = window.location.origin;
const BACKEND_URL = apiUrl.endsWith('/api') ? apiUrl.slice(0, -4) : apiUrl;

const router = useRouter();

// Hàm lấy Headers cho Axios
const getHeaders = () => {
    const token = localStorage.getItem('admin_token') || localStorage.getItem('adminToken');
    return {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    };
};

// AUTHENTICATION & PERMISSIONS
const currentUser = ref({});

const hasRole = (allowedRoles) => {
    const userRoleId = Number(currentUser.value?.role_id);
    let userRoleName = '';
    if (userRoleId === 1) userRoleName = 'admin';
    else if (userRoleId === 12) userRoleName = 'staff';
    else if (userRoleId === 13) userRoleName = 'blogger';

    if (!userRoleName) return false;
    if (userRoleName === 'admin') return true;
    return allowedRoles.includes(userRoleName);
};

const checkAuthState = async () => {
    const token = localStorage.getItem('admin_token') || localStorage.getItem('adminToken');
    const storedAdmin = localStorage.getItem('adminData');
    const storedUser = localStorage.getItem('user_info');
    let userData = null;

    try {
        if (storedAdmin) userData = JSON.parse(storedAdmin);
        else if (storedUser) userData = JSON.parse(storedUser);
    } catch (e) { console.error("Parse user error", e); }

    if (userData) {
        currentUser.value = { ...userData, role_id: Number(userData.role_id), name: userData.fullname || userData.full_name || userData.name || 'Admin' };
        return true;
    }

    if (token) {
        try {
            const response = await axios.get(`${apiUrl}/user`, { headers: getHeaders() });
            let data = response.data.data && !response.data.id ? response.data.data : response.data;
            currentUser.value = { ...data, role_id: Number(data.role_id), name: data.fullname || data.full_name || data.name || 'Admin' };
            localStorage.setItem('adminData', JSON.stringify(currentUser.value));
            return true;
        } catch (error) { return false; }
    }
    return false;
};

const requireLogin = () => {
    if (!currentUser.value.id) {
        Swal.fire({ icon: 'error', title: 'Truy cập bị từ chối', text: 'Phiên làm việc hết hạn hoặc chưa đăng nhập.', confirmButtonText: 'Đóng' });
        return false;
    }
    return true;
};

// STATE MANAGEMENT
const isLoading = ref(true);
const news = ref([]);
const searchQuery = ref('');
const currentTab = ref('all');
const sortOption = ref('newest');
const currentPage = ref(1);
const itemsPerPage = ref(5);

// COMPUTED & WATCHERS
const statusCounts = computed(() => {
    const list = news.value;
    return {
        all: list.length,
        pending: list.filter(i => i.status === 'pending').length,
        published: list.filter(i => i.status === 'published').length,
        draft: list.filter(i => i.status === 'draft').length
    };
});

const processedNews = computed(() => {
    let result = news.value;
    const query = searchQuery.value.toLowerCase().trim();
    
    if (query) result = result.filter(item => item.title.toLowerCase().includes(query));
    if (currentTab.value !== 'all') result = result.filter(item => item.status === currentTab.value);

    result = [...result].sort((a, b) => {
        if (sortOption.value === 'newest') return new Date(b.created_at) - new Date(a.created_at);
        else if (sortOption.value === 'oldest') return new Date(a.created_at) - new Date(b.created_at);
        else if (sortOption.value === 'a-z') return a.title.localeCompare(b.title);
        else if (sortOption.value === 'z-a') return b.title.localeCompare(a.title);
        return 0;
    });
    return result;
});

const totalPages = computed(() => Math.ceil(processedNews.value.length / itemsPerPage.value) || 1);

const paginatedNews = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    if (start >= processedNews.value.length && currentPage.value > 1) {
        currentPage.value = 1;
        return processedNews.value.slice(0, itemsPerPage.value);
    }
    return processedNews.value.slice(start, start + itemsPerPage.value);
});

watch([searchQuery, currentTab, sortOption], () => currentPage.value = 1);

// HELPER FUNCTIONS
const getFullImage = (path) => {
    if (!path) return 'https://placehold.co/800x400?text=No+Image';
    if (path.startsWith('blob:') || path.startsWith('http')) return path;
    return `${BACKEND_URL}${path.startsWith('/') ? '' : '/'}${path}`;
};

const getFormattedDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('vi-VN', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
};

const getDisplayAuthor = (item) => item.author_name || 'Không rõ';

const getStatusInfo = (status) => {
    const map = {
        'published': { text: 'Xuất bản', class: 'bg-success', icon: 'bi-check-circle' },
        'pending': { text: 'Đợi duyệt', class: 'bg-warning text-dark', icon: 'bi-hourglass-split' },
        'draft': { text: 'Đã ẩn', class: 'bg-secondary', icon: 'bi-eye-slash' }
    };
    return map[status] || { text: 'Không rõ', class: 'bg-light text-dark', icon: 'bi-question-circle' };
};

// NAVIGATION
const goToCreate = () => router.push('/admin/news/create');
const goToEdit = (id) => router.push(`/admin/news/edit/${id}`);
const viewOnFrontend = (slug) => window.open(`${FRONTEND_URL}/tin-tuc/${slug}`, '_blank');
const goToPage = (page) => { if (page >= 1 && page <= totalPages.value) currentPage.value = page; };

// ACTIONS (HANDLERS)
async function fetchNews() {
    isLoading.value = true;
    try {
        const response = await axios.get(`${apiUrl}/admin/news`, { headers: getHeaders() });
        news.value = response.data.data ? response.data.data : response.data;
    } catch (error) {
        if (error.response?.status === 401) Swal.fire('Hết phiên', 'Vui lòng đăng nhập lại.', 'warning');
        else Swal.fire('Lỗi', 'Không thể tải danh sách tin tức.', 'error');
    } finally {
        isLoading.value = false;
    }
}

async function handleToggleStatus(newsItem) {
    if (!requireLogin()) return;
    if (!hasRole(['admin'])) return Swal.fire('Quyền hạn', 'Bạn không có quyền duyệt bài.', 'warning');
    const newStatus = newsItem.status === 'published' ? 'draft' : 'published';
    const actionName = newStatus === 'published' ? 'XUẤT BẢN' : 'ẨN BÀI VIẾT';
    const result = await Swal.fire({ title: 'Thay đổi trạng thái?', text: `Bạn có muốn ${actionName} này?`, icon: 'question', showCancelButton: true, confirmButtonText: 'Đồng ý', cancelButtonText: 'Hủy' });
    if (result.isConfirmed) {
        try {
            await axios.patch(`${apiUrl}/admin/news/${newsItem.id}`, { status: newStatus }, { headers: getHeaders() });
            newsItem.status = newStatus;
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Cập nhật thành công', timer: 1500, showConfirmButton: false });
        } catch (e) { Swal.fire('Lỗi', 'Không thể cập nhật trạng thái.', 'error'); }
    }
}

async function handleDelete(newsItem) {
    if (!requireLogin()) return;
    const isAuthor = newsItem.author_name === currentUser.value.name;
    if (!hasRole(['admin']) && !isAuthor) return Swal.fire('Quyền hạn', 'Bạn không có quyền xóa bài này.', 'error');
    const result = await Swal.fire({ title: 'Xóa bài viết?', text: "Hành động này không thể hoàn tác!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Xóa ngay', cancelButtonText: 'Hủy' });
    if (result.isConfirmed) {
        try {
            await axios.delete(`${apiUrl}/admin/news/${newsItem.id}`, { headers: getHeaders() });
            Swal.fire('Đã xóa!', 'Bài viết đã bị xóa.', 'success');
            if (paginatedNews.value.length === 1 && currentPage.value > 1) currentPage.value--;
            fetchNews();
        } catch (e) { Swal.fire('Lỗi', 'Không thể xóa bài viết.', 'error'); }
    }
}

onMounted(async () => {
    await checkAuthState();
    if (!requireLogin()) { isLoading.value = false; return; }
    fetchNews();
});
</script>

<template>
    <div class="news-admin-page p-4 min-vh-100">
        <!-- Tiêu đề & Thống kê & Bộ lọc -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="h3 font-serif mb-2 fw-bold">Quản lý Tin tức</h2>
                <div class="d-flex align-items-center gap-3">
                    <p class="text-secondary mb-0 small">Thêm, sửa, xóa và xuất bản các bài viết.</p>
                </div>
            </div>

            <!-- KHU VỰC BỘ LỌC VÀ TÁC VỤ NHANH -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- Bộ lọc tìm kiếm -->
                <div class="input-group input-group-sm shadow-sm border-0 rounded-3 overflow-hidden bg-white px-2 py-1 w-auto align-items-center">
                    <span class="border-0 bg-transparent text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-0 shadow-none bg-transparent form-control-sm" placeholder="Tìm kiếm bài viết..." v-model="searchQuery">
                </div>

                <!-- Bộ lọc trạng thái -->
                <select class="form-select form-select-sm shadow-sm border-0 rounded-3 w-auto px-3 py-2" v-model="currentTab">
                    <option value="all">Tất cả ({{ statusCounts.all }})</option>
                    <option value="pending">Đợi duyệt ({{ statusCounts.pending }})</option>
                    <option value="published">Đã xuất bản ({{ statusCounts.published }})</option>
                    <option value="draft">Đã ẩn ({{ statusCounts.draft }})</option>
                </select>

                <!-- Bộ lọc sắp xếp -->
                <select class="form-select form-select-sm shadow-sm border-0 rounded-3 w-auto px-3 py-2" v-model="sortOption">
                    <option value="newest">⏱️ Mới nhất</option>
                    <option value="oldest">⏳ Cũ nhất</option>
                    <option value="a-z">🔤 Tên (A-Z)</option>
                    <option value="z-a">🔤 Tên (Z-A)</option>
                </select>

                <!-- Nút Viết bài mới -->
                <button @click="goToCreate" class="btn btn-sm btn-primary px-3 py-2 shadow-sm rounded-3 d-inline-flex align-items-center">
                    <i class="bi bi-plus-lg me-2"></i> Viết bài mới
                </button>
            </div>
        </div>

        <!-- Bảng danh sách tin tức -->
        <div class="card shadow-sm rounded-4 border-0 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 custom-table">
                        <thead>
                            <tr class="table-dark">
                                <th scope="col" class="ps-4 py-3" style="width: 60px;">ID</th>
                                <th scope="col" class="py-3" style="width: 100px;">Ảnh</th>
                                <th scope="col" class="py-3 text-uppercase small">Tiêu đề / Danh mục</th>
                                <th scope="col" class="py-3 text-uppercase small d-none d-md-table-cell" style="width: 150px;">Tác giả</th>
                                <th scope="col" class="py-3 text-uppercase small text-center" style="width: 100px;">Lượt xem</th>
                                <th scope="col" class="py-3 text-uppercase small text-center" style="width: 120px;">Trạng thái</th>
                                <th scope="col" class="py-3 text-uppercase small d-none d-lg-table-cell" style="width: 130px;">Ngày tạo</th>
                                <th scope="col" class="pe-4 py-3 text-uppercase small text-end" style="width: 180px;">Tác Vụ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="isLoading">
                                <td colspan="8" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                </td>
                            </tr>
                            <tr v-else-if="paginatedNews.length === 0">
                                <td colspan="8" class="text-center py-5 text-muted small">Không tìm thấy bài viết nào phù hợp.</td>
                            </tr>
                            <tr v-else v-for="item in paginatedNews" :key="item.id" :class="{'table-warning-custom': item.status === 'pending'}">
                                <td class="ps-4 fw-bold text-muted">{{ item.id }}</td>
                                <td>
                                    <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm" style="width: 80px;">
                                        <img :src="getFullImage(item.image_url)" class="object-fit-cover" onerror="this.src='https://placehold.co/80x45?text=Img'">
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border mb-1" v-if="item.category">{{ item.category }}</span>
                                    <span class="fw-bold d-block text-dark text-truncate" style="max-width: 280px;" :title="item.title">{{ item.title }}</span>
                                    <small class="text-muted d-block text-truncate" style="max-width: 280px;">{{ item.excerpt || 'Chưa có mô tả ngắn...' }}</small>
                                </td>
                                <td class="d-none d-md-table-cell small">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle text-white fw-bold me-2" style="background-color: #009981;">
                                            {{ getDisplayAuthor(item).charAt(0).toUpperCase() }}
                                        </div>
                                        <span class="fw-medium text-truncate" style="max-width: 100px;">{{ getDisplayAuthor(item) }}</span>
                                    </div>
                                </td>
                                <td class="text-center fw-bold text-secondary small">
                                    <i class="bi bi-eye me-1"></i> {{ item.views || 0 }}
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill text-white fw-normal px-3 py-2" :class="getStatusInfo(item.status).class">
                                        <i class="bi me-1" :class="getStatusInfo(item.status).icon"></i>{{ getStatusInfo(item.status).text }}
                                    </span>
                                </td>
                                <td class="d-none d-lg-table-cell small text-secondary">{{ getFormattedDate(item.created_at) }}</td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end align-items-center gap-2">
                                        <div class="form-check form-switch m-0 d-flex align-items-center" v-if="hasRole(['admin'])" title="Bật/Tắt xuất bản">
                                            <input class="form-check-input custom-switch" type="checkbox" role="switch" :checked="item.status === 'published'" @click.prevent="handleToggleStatus(item)">
                                        </div>
                                        <!-- Mở trên tab mới bên Frontend thay vì Modal -->
                                        <button class="btn btn-sm btn-outline-info shadow-sm d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" title="Xem bài viết" @click="viewOnFrontend(item.slug)">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary shadow-sm d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" title="Sửa" @click="goToEdit(item.id)">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger shadow-sm d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" title="Xóa" v-if="hasRole(['admin']) || item.author_name === currentUser.name" @click="handleDelete(item)">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Phân trang -->
            <div class="card-footer bg-white border-top-0 py-3" v-if="!isLoading && totalPages > 1">
                <div class="d-flex justify-content-between align-items-center px-2">
                    <small class="text-muted">Hiển thị {{ paginatedNews.length }} / {{ processedNews.length }} bài viết</small>
                    <ul class="pagination pagination-sm m-0 gap-1">
                        <li class="page-item" :class="{ disabled: currentPage === 1 }"><button class="page-link border-0 rounded" @click="goToPage(currentPage - 1)"><i class="bi bi-chevron-left"></i></button></li>
                        <li v-for="page in totalPages" :key="page" class="page-item" :class="{ active: currentPage === page }"><button class="page-link border-0 rounded mx-1" @click="goToPage(page)">{{ page }}</button></li>
                        <li class="page-item" :class="{ disabled: currentPage === totalPages }"><button class="page-link border-0 rounded" @click="goToPage(currentPage + 1)"><i class="bi bi-chevron-right"></i></button></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.font-serif { font-family: "Playfair Display", serif; }

.avatar-circle {
    width: 30px; 
    height: 30px; 
    border-radius: 50%; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 0.8rem; 
}

.table-warning-custom { background-color: rgba(255, 193, 7, 0.05) !important; }
.table-warning-custom td { font-weight: 500; }

.custom-table thead th { border: none; font-size: 0.75rem; letter-spacing: 0.5px; }
.custom-table tbody td { border-bottom: 1px solid var(--bs-border-color); }

.btn-primary { background-color: #009981 !important; border-color: #009981 !important; color: white !important; }
.btn-primary:hover, .btn-primary:active { background-color: #007a67 !important; border-color: #007a67 !important; }

.custom-switch { cursor: pointer; }
.custom-switch:checked { background-color: #009981; border-color: #009981; }

.page-item.active .page-link { background-color: #009981 !important; color: white !important; border-color: #009981 !important; }
.page-link { color: #666; }
</style>