<template>
  <aside class="main-sidebar sidebar-dark-primary d-flex flex-column shadow-lg"
    style="background-color: #2c3136; min-height: 100vh; width: 260px; transition: all 0.3s;">

    <!-- Brand Logo -->
    <router-link to="/admin"
      class="brand-link text-decoration-none text-white p-3 border-bottom border-secondary d-flex align-items-center"
      style="border-color: rgba(255,255,255,0.1) !important;">
      <div class="bg-white rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm"
        style="width: 38px; height: 38px;">
        <i class="bi bi-layers-fill fs-5" style="color: #009981;"></i>
      </div>
      <span class="brand-text fw-bold fs-5 tracking-wide" style="letter-spacing: 1px;">ThinkHub Admin</span>
    </router-link>

    <!-- Sidebar Menu -->
    <div class="sidebar p-3 flex-grow-1 overflow-auto custom-scrollbar">

      <div v-if="isLoading" class="text-center text-white-50 mt-4">
        <div class="spinner-border spinner-border-sm mb-2" role="status"></div>
        <p class="small">Đang tải phân quyền...</p>
      </div>

      <nav class="mt-2" v-else>
        <ul class="nav nav-pills nav-sidebar flex-column gap-2" data-widget="treeview" role="menu">

          <template v-for="(item, index) in menuItems" :key="index">

            <!-- Menu đơn -->
            <li class="nav-item position-relative" v-if="!item.children">
              <span v-if="getModuleLevel(item.moduleCode)"
                class="position-absolute badge rounded-pill shadow-sm level-badge"
                :class="hasAccess(item.moduleCode) ? 'bg-success' : 'bg-danger'">
                Cấp {{ getModuleLevel(item.moduleCode) }}
              </span>

              <router-link v-if="hasAccess(item.moduleCode)" :to="item.path"
                :active-class="item.path === '/admin' ? 'ignore-active' : 'router-link-active'"
                class="nav-link text-white py-2 px-3 rounded shadow-sm-hover transition-all">
                <i class="nav-icon bi me-3" :class="item.icon"></i>
                <p class="m-0 d-inline-block align-middle fw-semibold">{{ item.name }}</p>
              </router-link>

              <div v-else class="nav-link py-2 px-3 rounded disabled-menu"
                @click="showAccessDenied(item.name, getModuleLevel(item.moduleCode))">
                <i class="nav-icon bi me-3" :class="item.icon"></i>
                <p class="m-0 d-inline-block align-middle fw-semibold">{{ item.name }}</p>
                <i class="bi bi-lock-fill float-end opacity-50"></i>
              </div>
            </li>

            <!-- Menu Dropdown -->
            <li class="nav-item mt-2 bg-dark rounded shadow-sm position-relative"
              :class="{ 'menu-open': menuState[item.stateKey] }" v-else>

              <a href="#"
                class="nav-link text-white py-2 px-3 rounded d-flex justify-content-between align-items-center transition-all"
                @click.prevent="toggleMenu(item.stateKey)" :class="{ 'active-group': menuState[item.stateKey] }">
                <div>
                  <i class="nav-icon bi me-3" :class="item.icon"></i>
                  <p class="m-0 d-inline-block align-middle fw-semibold">{{ item.name }}</p>
                </div>
                <i class="bi bi-chevron-left transition-icon" :class="{ 'rotate-180': menuState[item.stateKey] }"></i>
              </a>

              <!-- Danh sách Menu Con -->
              <ul class="nav nav-treeview flex-column p-2 pt-1 gap-1" v-show="menuState[item.stateKey]"
                style="background-color: rgba(0,0,0,0.15); border-radius: 0 0 8px 8px;">
                <li class="nav-item position-relative" v-for="(subItem, subIndex) in item.children" :key="subIndex">

                  <span v-if="getModuleLevel(subItem.moduleCode)"
                    class="position-absolute badge rounded-pill shadow-sm level-badge-sub"
                    :class="hasAccess(subItem.moduleCode) ? 'bg-success opacity-75' : 'bg-danger opacity-75'">
                    Cấp {{ getModuleLevel(subItem.moduleCode) }}
                  </span>

                  <router-link v-if="hasAccess(subItem.moduleCode)" :to="subItem.path"
                    class="nav-link text-white-50 py-2 px-3 rounded sub-link d-flex align-items-center">
                    <i class="bi bi-circle-fill fs-xs me-3 opacity-50" style="font-size: 6px;"></i>
                    <p class="m-0 d-inline-block align-middle fw-medium">{{ subItem.name }}</p>
                  </router-link>

                  <div v-else
                    class="nav-link text-white-50 py-2 px-3 rounded sub-link d-flex align-items-center disabled-menu"
                    @click="showAccessDenied(subItem.name, getModuleLevel(subItem.moduleCode))">
                    <i class="bi bi-lock-fill fs-xs me-3 opacity-50" style="font-size: 10px;"></i>
                    <p class="m-0 d-inline-block align-middle fw-medium">{{ subItem.name }}</p>
                  </div>

                </li>
              </ul>
            </li>
          </template>

        </ul>
      </nav>
    </div>
  </aside>
</template>

<script setup>
import { ref, reactive, onMounted, inject, computed } from 'vue';
import { useRoute } from 'vue-router';
import Swal from 'sweetalert2';

const route = useRoute();
const isLoading = ref(true);
const systemModules = ref([]);

// Inject từ App.vue, cung cấp fallback ref(null) nếu Inject thất bại (chưa setup kĩ App.vue)
const currentUser = inject('currentUser', ref(null));

// Computed thông minh: Xử lý cả 2 trường hợp (F5 và Vừa Login xong)
const userLevel = computed(() => {
  // 1. Ưu tiên lấy từ state Provide/Inject (Dành cho trường hợp reload trang F5)
  const user = currentUser?.value || currentUser;
  if (user && user.role && user.role.level) {
    return user.role.level;
  }

  // 2. Cứu cánh SPA: Nếu App.vue chưa kịp chạy lại do vừa chuyển từ trang Login
  // -> Móc tạm dữ liệu từ localStorage để giải nguy
  try {
    const localAdmin = JSON.parse(localStorage.getItem('admin_info') || '{}');
    const savedLevel = localStorage.getItem('admin_level') || localAdmin.role?.level;
    
    if (savedLevel) {
      return parseInt(savedLevel);
    }
  } catch (e) {
    console.warn("Không thể parse localStorage cho Sidebar");
  }

  return 999; // Mặc định là quyền thấp nhất nếu cả 2 cách trên đều thất bại
});

const menuItems = ref([
  {
    name: 'Tổng quan',
    path: '/admin',
    icon: 'bi-grid-1x2-fill',
    moduleCode: null
  },
  {
    name: 'Phân Quyền',
    path: '/admin/roles',
    icon: 'bi-shield-fill-check',
    moduleCode: 'admin_roles'
  },
  {
    name: 'Tài khoản',
    icon: 'bi-people-fill',
    stateKey: 'users',
    children: [
      { name: 'Nội bộ', path: '/admin/staff', moduleCode: 'admin_staff' },
      { name: 'Khách hàng', path: '/admin/users', moduleCode: 'admin_users' }
    ]
  },
]);

const menuState = reactive({
  users: false,
  products: false,
  orders: false,
});

const toggleMenu = (menuKey) => {
  menuState[menuKey] = !menuState[menuKey];
};

const getHeaders = () => ({
  'Accept': 'application/json',
  'Authorization': `Bearer ${localStorage.getItem('admin_token')}`
});

const fetchSidebarData = async () => {
  try {
    const resMod = await fetch('http://127.0.0.1:8000/api/admin/modules', { headers: getHeaders() });
    if (resMod.ok) {
      systemModules.value = (await resMod.json()).data;
    }
  } catch (err) {
    console.error("Lỗi tải dữ liệu Sidebar", err);
  } finally {
    isLoading.value = false;
  }
};

const getModuleLevel = (code) => {
  if (!code) return null;
  const mod = systemModules.value.find(m => m.module_code === code);
  return mod ? mod.required_level : null;
};

const hasAccess = (code) => {
  if (!code) return true;
  const requiredLevel = getModuleLevel(code);
  if (!requiredLevel) return false;

  // Level càng thấp thì quyền càng cao (1 là cao nhất)
  return userLevel.value <= requiredLevel;
};

const showAccessDenied = (menuName, reqLevel) => {
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon: 'error',
    title: 'Truy cập bị từ chối!',
    text: `Tính năng "${menuName}" yêu cầu Cấp ${reqLevel}. (Bạn đang ở Cấp ${userLevel.value})`,
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
  });
};

onMounted(() => {
  fetchSidebarData();

  const currentPath = route.path;
  menuItems.value.forEach(item => {
    if (item.children) {
      const isChildActive = item.children.some(subItem => {
        return currentPath === subItem.path || currentPath.startsWith(subItem.path + '/');
      });
      if (isChildActive) {
        menuState[item.stateKey] = true;
      }
    }
  });
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background-color: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.15);
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(255, 255, 255, 0.3);
}

.nav-link {
  transition: all 0.2s ease;
}

.shadow-sm-hover:hover {
  background-color: rgba(255, 255, 255, 0.08);
  transform: translateX(3px);
}

.disabled-menu {
  background-color: rgba(0, 0, 0, 0.2) !important;
  color: #6c757d !important;
  opacity: 0.6;
  cursor: not-allowed;
  filter: grayscale(100%);
}

.disabled-menu:hover {
  background-color: rgba(0, 0, 0, 0.3) !important;
  color: #dc3545 !important;
}

.level-badge {
  top: 6px;
  right: 8px;
  font-size: 0.65rem;
  padding: 3px 6px;
  z-index: 2;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.level-badge-sub {
  top: 8px;
  right: 12px;
  font-size: 0.6rem;
  padding: 2px 5px;
  z-index: 2;
}

.sub-link {
  transition: all 0.2s ease;
}

.sub-link:hover:not(.disabled-menu) {
  background-color: rgba(0, 153, 129, 0.1) !important;
  color: #00cba9 !important;
  transform: translateX(3px);
}

.sub-link:hover:not(.disabled-menu) i {
  color: #00cba9 !important;
}

.active-group {
  background-color: #009981 !important;
  color: #fff !important;
  box-shadow: 0 4px 10px rgba(0, 153, 129, 0.3);
}

.router-link-active,
.router-link-exact-active {
  background-color: #009981 !important;
  color: #fff !important;
  box-shadow: 0 4px 10px rgba(0, 153, 129, 0.3);
}

.sub-link.router-link-active {
  background-color: rgba(0, 153, 129, 0.15) !important;
  color: #00ebc4 !important;
  box-shadow: none;
  font-weight: 600;
}

.sub-link.router-link-active i {
  color: #00ebc4 !important;
  opacity: 1 !important;
}

/* Icons */
.transition-icon {
  transition: transform 0.3s ease;
  font-size: 12px;
  opacity: 0.8;
}

.rotate-180 {
  transform: rotate(-90deg);
}
</style>