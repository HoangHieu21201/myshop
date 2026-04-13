<template>
  <div class="news-index-wrapper pb-5 mb-5">
    
    <!-- Loading Shimmer -->
    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ThinkHub</h1>
      <p class="text-muted fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải dữ liệu...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      <!-- Header chung 1 hàng -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">Quản Lý Tin Tức</h3>
            <p class="text-secondary mb-0 small mt-1">Thêm, sửa, xóa và xuất bản các bài viết.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
          <button @click="goToCreate" class="btn btn-brand btn-brand-solid px-4 py-2 fw-bold shadow-sm text-nowrap">
            <i class="bi bi-plus-circle-fill me-1"></i> Viết bài mới
          </button>
        </div>
      </div>

      <!-- Tabs, Sort và Search Box chung 1 hàng -->
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 border-bottom pb-2">
        <ul class="nav nav-underline border-0 mb-0 flex-nowrap overflow-hidden custom-scrollbar-x flex-grow-1">
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': currentTab === 'all' }" @click.prevent="switchTab('all')">
              <i class="bi bi-grid-fill me-2"></i> Tất cả
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': currentTab === 'all'}">{{ statusCounts.all }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': currentTab === 'published' }" @click.prevent="switchTab('published')">
              <i class="bi bi-check-circle-fill me-2 text-success"></i> Xuất bản
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': currentTab === 'published'}">{{ statusCounts.published }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': currentTab === 'pending' }" @click.prevent="switchTab('pending')">
              <i class="bi bi-hourglass-split me-2 text-warning"></i> Đợi duyệt
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': currentTab === 'pending'}">{{ statusCounts.pending }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': currentTab === 'draft' }" @click.prevent="switchTab('draft')">
              <i class="bi bi-eye-slash-fill me-2 text-secondary"></i> Đã ẩn
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': currentTab === 'draft'}">{{ statusCounts.draft }}</span>
            </a>
          </li>
        </ul>

        <!-- Sort & Search box -->
        <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm shadow-sm bg-white border py-2 rounded-pill px-3" style="width: 150px; font-size: 0.875rem;" v-model="sortOption">
                <option value="newest">Mới nhất</option>
                <option value="oldest">Cũ nhất</option>
                <option value="a-z">Tên (A-Z)</option>
                <option value="z-a">Tên (Z-A)</option>
            </select>

            <div class="search-box position-relative" style="width: 250px; max-width: 100%;">
                <input type="text" class="form-control form-control-sm rounded-pill pe-5 shadow-sm bg-white border py-2" v-model="searchQuery" @input="currentPage = 1" placeholder="Tìm tên bài viết...">
                <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
            </div>
        </div>
      </div>

      <!-- Bảng Dữ liệu -->
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-0 mt-2">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 1100px;">
              <thead class="bg-light">
                <tr>
                  <th class="py-3 px-4 text-secondary border-0" style="width: 8%;">ID</th>
                  <th class="py-3 px-4 text-secondary border-0" style="width: 32%;">Bài viết</th>
                  <th class="py-3 px-4 text-secondary border-0" style="width: 15%;">Tác giả</th>
                  <th class="py-3 px-4 text-secondary border-0 text-center" style="width: 10%;">Lượt xem</th>
                  <th class="py-3 px-4 text-secondary border-0 text-center text-nowrap" style="width: 15%;">Trạng thái</th>
                  <th class="py-3 px-4 text-secondary text-center border-0 text-nowrap" style="width: 20%;">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="isLoading && !isFirstLoad">
                  <td colspan="6" class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-brand mb-2" role="status"></div>
                    <div class="small fw-semibold">Đang tải dữ liệu...</div>
                  </td>
                </tr>
                <tr v-else-if="paginatedNews.length === 0">
                  <td colspan="6" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Không có dữ liệu.
                  </td>
                </tr>
                <tr v-else v-for="item in paginatedNews" :key="item.id" 
                    :class="{'bg-light opacity-75': item.status === 'draft', 'table-warning-custom': item.status === 'pending'}">
                  
                  <td class="px-4 fw-bold text-muted small">#{{ item.id }}</td>

                  <!-- Ảnh và Tiêu đề -->
                  <td class="px-4 py-3">
                    <div class="d-flex align-items-center">
                      <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm flex-shrink-0 me-3" style="width: 100px;">
                          <img :src="getFullImage(item.image_url)" class="object-fit-cover" onerror="this.src='https://placehold.co/100x56?text=Img'">
                      </div>
                      <div class="overflow-hidden">
                        <h6 class="mb-1 fw-bold text-dark text-truncate" :title="item.title">{{ item.title }}</h6>
                        <small class="text-muted d-block text-truncate">{{ item.excerpt || 'Chưa có mô tả ngắn...' }}</small>
                        <span class="badge bg-light text-secondary border mt-1 fw-normal" v-if="item.category">{{ item.category }}</span>
                      </div>
                    </div>
                  </td>

                  <!-- Tác giả -->
                  <td class="px-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar-circle text-white fw-bold me-2 shadow-sm" style="background-color: #009981;">
                            {{ getDisplayAuthor(item).charAt(0).toUpperCase() }}
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark small text-truncate" style="max-width: 100px;">{{ getDisplayAuthor(item) }}</span>
                            <span class="text-muted" style="font-size: 0.7rem;">{{ getFormattedDate(item.created_at) }}</span>
                        </div>
                    </div>
                  </td>

                  <!-- Lượt xem -->
                  <td class="px-4 text-center fw-bold text-secondary">
                    <i class="bi bi-eye me-1"></i> {{ item.views || 0 }}
                  </td>

                  <!-- Trạng thái -->
                  <td class="px-4 text-center text-nowrap">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                        <span class="badge rounded-pill fw-medium px-3 py-1 mb-1 border border-opacity-25" :class="getStatusInfo(item.status).badgeClass">
                            <i class="bi me-1" :class="getStatusInfo(item.status).icon"></i>{{ getStatusInfo(item.status).text }}
                        </span>
                        <!-- Switch Toggle nếu là admin -->
                        <div class="form-check form-switch m-0 d-flex align-items-center" v-if="hasRole(['admin'])" title="Bật/Tắt xuất bản">
                            <input class="form-check-input custom-switch" type="checkbox" role="switch" :checked="item.status === 'published'" @click.prevent="handleToggleStatus(item)">
                        </div>
                    </div>
                  </td>

                  <!-- Thao tác -->
                  <td class="px-4 text-center text-nowrap">
                    <button class="btn btn-sm btn-light text-info me-1 shadow-sm border" title="Xem bài viết" @click="viewOnFrontend(item.slug)">
                      <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-light text-primary me-1 shadow-sm border" title="Chỉnh sửa" @click="goToEdit(item.id)">
                      <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-sm btn-light text-danger shadow-sm border" title="Xóa" v-if="hasRole(['admin']) || item.author_name === currentUser.name" @click="handleDelete(item)">
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Phân trang -->
      <div class="d-flex justify-content-between align-items-center" v-if="!isLoading && totalPages > 1">
        <span class="text-muted small">Hiển thị {{ (currentPage - 1) * itemsPerPage + 1 }} đến {{ Math.min(currentPage * itemsPerPage, processedNews.length) }}</span>
        <nav>
          <ul class="pagination pagination-sm mb-0 shadow-sm">
            <li class="page-item" :class="{ disabled: currentPage === 1 }"><button class="page-link text-brand" @click="goToPage(currentPage - 1)"><i class="bi bi-chevron-left"></i></button></li>
            <li class="page-item" v-for="page in totalPages" :key="page" :class="{ active: currentPage === page }"><button class="page-link" :class="currentPage === page ? 'bg-brand border-brand text-white' : 'text-dark'" @click="goToPage(page)">{{ page }}</button></li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }"><button class="page-link text-brand" @click="goToPage(currentPage + 1)"><i class="bi bi-chevron-right"></i></button></li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';

// Khai báo Component name
defineOptions({
  name: 'NewsIndex'
});

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
        } catch (error) { return error; }
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
const isFirstLoad = ref(true); // Thêm biến cho hiệu ứng Shimmer
const news = ref([]);
const searchQuery = ref('');
const currentTab = ref('all');
const sortOption = ref('newest');
const currentPage = ref(1);
const itemsPerPage = ref(10);

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
    return new Date(dateString).toLocaleDateString('vi-VN', { year: 'numeric', month: '2-digit', day: '2-digit' });
};

const getDisplayAuthor = (item) => item.author_name || 'Không rõ';

const getStatusInfo = (status) => {
    const map = {
        'published': { text: 'Xuất bản', badgeClass: 'bg-success bg-opacity-10 text-success border-success', icon: 'bi-check-circle-fill' },
        'pending': { text: 'Đợi duyệt', badgeClass: 'bg-warning bg-opacity-10 text-warning border-warning', icon: 'bi-hourglass-split' },
        'draft': { text: 'Đã ẩn', badgeClass: 'bg-secondary bg-opacity-10 text-secondary border-secondary', icon: 'bi-eye-slash-fill' }
    };
    return map[status] || { text: 'Không rõ', badgeClass: 'bg-light text-dark', icon: 'bi-question-circle' };
};

// NAVIGATION
const goToCreate = () => router.push('/admin/news/create');
const goToEdit = (id) => router.push(`/admin/news/edit/${id}`);
const viewOnFrontend = (slug) => window.open(`${FRONTEND_URL}/tin-tuc/${slug}`, '_blank');
const goToPage = (page) => { if (page >= 1 && page <= totalPages.value) currentPage.value = page; };
const switchTab = (tabId) => { currentTab.value = tabId; currentPage.value = 1; };

// ACTIONS (HANDLERS)
async function fetchNews() {
    if (!isFirstLoad.value) isLoading.value = true;
    try {
        const response = await axios.get(`${apiUrl}/admin/news`, { headers: getHeaders() });
        news.value = response.data.data ? response.data.data : response.data;
    } catch (error) {
        if (error.response?.status === 401) Swal.fire('Hết phiên', 'Vui lòng đăng nhập lại.', 'warning');
        else Swal.fire('Lỗi', 'Không thể tải danh sách tin tức.', 'error');
    } finally {
        isLoading.value = false;
        isFirstLoad.value = false;
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
        } catch (e) { Swal.fire('Lỗi', 'Không thể cập nhật trạng thái.', e); }
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
            Swal.fire({icon: 'success', title: 'Đã xóa', timer: 1500, showConfirmButton: false});
            if (paginatedNews.value.length === 1 && currentPage.value > 1) currentPage.value--;
            fetchNews();
        } catch (e) { Swal.fire('Lỗi', 'Không thể xóa bài viết.', e); }
    }
}

onMounted(async () => {
    await checkAuthState();
    if (!requireLogin()) { isLoading.value = false; isFirstLoad.value = false; return; }
    fetchNews();
});
</script>

<style scoped>
/* Hiệu ứng Logo Shimmer lúc Load */
.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, #009981 30%, #4dffdf 50%, #009981 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }

/* Cấu hình Tabs giống mã giảm giá */
.custom-tab { font-weight: 600 !important; color: #6c757d; border-bottom: 2px solid transparent !important; margin-bottom: -1px; transition: color 0.2s ease; }
.custom-tab:hover { color: #009981; }
.custom-tab.active-tab { color: #009981 !important; border-bottom: 2px solid #009981 !important; }
.tab-badge { font-size: 0.75rem; font-weight: 600; background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; transition: all 0.2s ease; }
.active-badge { background-color: #e6f5f2 !important; color: #009981 !important; border-color: #009981 !important; }

/* Colors & Buttons */
.bg-brand { background-color: #009981 !important; } 
.text-brand { color: #009981 !important; } 
.border-brand { border-color: #009981 !important; }
.btn-brand-solid { background-color: #009981 !important; color: white !important; transition: all 0.2s ease; border: none; }
.btn-brand-solid:hover { background-color: #007a67 !important; color: white !important; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }

/* Switch custom */
.custom-switch { cursor: pointer; }
.custom-switch:checked { background-color: #009981; border-color: #009981; }

/* Avatar Tác giả */
.avatar-circle {
    width: 36px; 
    height: 36px; 
    border-radius: 50%; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 1rem; 
}

/* Row warning cho bài đang chờ */
.table-warning-custom { background-color: rgba(255, 193, 7, 0.05) !important; }

/* Scrollbar */
.custom-scrollbar-x::-webkit-scrollbar { height: 4px; }
.custom-scrollbar-x::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: #e0e0e0; border-radius: 10px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb:hover { background: #c0c0c0; }
</style>