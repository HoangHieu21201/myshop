<template>
  <!-- header -->
  <header class="site-header bg-white sticky-top">
    <div class="container position-relative">

      <div class="header-tier-top d-flex justify-content-between align-items-center pt-3 pb-2">

        <div class="top-links-wrapper d-flex align-items-center" style="flex: 1;">
          <button class="btn border-0 d-lg-none fs-3 text-dark p-0 me-3" @click="toggleMobileMenu">
            <i class="bi bi-list"></i>
          </button>
          <div class="top-links d-none d-lg-flex gap-4">
            <router-link :to="{ name: 'about' }" class="top-link">VỀ SORA</router-link>
            <router-link :to="{ name: 'contact' }" class="top-link">LIÊN HỆ</router-link>
            <router-link :to="{ name: 'services' }" class="top-link">DỊCH VỤ</router-link>
            <router-link :to="{ name: 'gold-price' }" class="top-link">BẢNG GIÁ VÀNG</router-link>
          </div>
        </div>

        <div href="/" class="logo-wrapper d-flex justify-content-center" style="flex: 1;">
          <router-link :to="{ name: 'home' }">
            <img src="../../assets/images/logo1.png" alt="SORA Logo" class="logo-img" @error="handleLogoError">
          </router-link>
        </div>

        <div class="header-icons d-flex justify-content-end align-items-center gap-4" style="flex: 1;">
          <a href="#" @click.prevent="safeNavigate('favourite')" class="icon-link hover-primary transition-color">
            <i class="bi bi-heart"></i>
          </a>

          <div class="user-menu-wrapper position-relative" ref="userMenuContainer">
            <button @click="toggleUserMenu"
              class="btn border-0 p-0 bg-transparent icon-link hover-primary transition-color">
              <i class="bi bi-person"></i>
            </button>

            <transition name="fade">
              <div v-if="isUserMenuOpen"
                class="user-dropdown shadow-lg rounded-4 border bg-white position-absolute end-0 mt-3 py-2"
                style="width: 220px; z-index: 1050;">

                <template v-if="user">
                  <div class="px-4 py-2 border-bottom mb-2 bg-light">
                    <div class="fw-bold text-truncate">{{ user.fullName || 'Thành viên' }}</div>
                    <div class="small text-muted font-monospace text-truncate">{{ user.email }}</div>
                  </div>
                  <a href="#" @click.prevent="safeNavigate('profile')"
                    class="dropdown-item py-2 px-4 fw-medium text-decoration-none"><i
                      class="bi bi-person-circle me-2 text-muted"></i>Tài khoản của tôi
                  </a>
                  <a href="#" @click.prevent="safeNavigate('order')"
                    class="dropdown-item py-2 px-4 fw-medium text-decoration-none"><i
                      class="bi bi-box-seam me-2 text-muted"></i>Đơn mua
                  </a>
                  <a href="#" @click.prevent="safeNavigate('favourite')"
                    class="dropdown-item py-2 px-4 fw-medium text-decoration-none"><i
                      class="bi bi-heart text-danger me-2"></i>Yêu thích
                  </a>
                  <div class="dropdown-divider my-2"></div>
                  <a href="#" @click.prevent="handleLogout" class="dropdown-item py-2 px-4 fw-bold text-danger"><i
                      class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                  </a>
                </template>
                <template v-else>
                  <div class="p-3 text-center">
                    <p class="small text-muted mb-3">Đăng nhập để theo dõi đơn hàng và ưu đãi</p>
                    <a href="#" @click.prevent="safeNavigate('login')"
                      class="btn btn-brand w-100 fw-bold rounded-pill text-white mb-2 text-decoration-none">Đăng
                      Nhập</a>
                    <div class="small">Chưa có tài khoản? <a href="#" @click.prevent="safeNavigate('register')"
                        class="text-primary-custom fw-bold text-decoration-none">Đăng ký</a></div>
                  </div>
                </template>
              </div>
            </transition>
          </div>

          <router-link :to="{ name: 'cart' }" class="icon-link position-relative hover-primary transition-color">
            <i class="bi bi-bag"></i>
            <span v-if="cartItemCount > 0" class="cart-badge">{{ cartItemCount > 99 ? '99+' : cartItemCount }}</span>
          </router-link>
        </div>
      </div>

      <div
        class="header-tier-bottom d-none d-lg-flex justify-content-center align-items-center position-relative pb-2 mt-2">

        <nav class="main-nav">
          <ul class="d-flex align-items-center m-0 p-0 list-unstyled gap-5">
            <li><router-link :to="{ name: 'home' }" class="nav-item-link">XU HƯỚNG</router-link></li>


            <li class="position-relative" @mouseenter="isMegaMenuOpen = true" @mouseleave="isMegaMenuOpen = false">
              <a href="#" @click.prevent="safeNavigate('shop')" class="nav-item-link d-flex align-items-center">
                SẢN PHẨM
              </a>

              <transition name="fade-slide">
                <div v-show="isMegaMenuOpen"
                  class="mega-menu-wrapper shadow-lg border-top border-3 border-primary-custom">
                  <div class="d-flex text-start">
                    <div class="mega-category-list p-4 bg-light border-end">
                      <h6 class="fw-bold mb-3 text-uppercase text-muted font-oswald" style="letter-spacing: 1px;">Danh
                        Mục
                      </h6>
                      <ul class="list-unstyled m-0">
                        <li v-for="cat in categories" :key="cat.id" class="mb-2" @mouseenter="hoveredCategory = cat">
                          <a href="#" @click.prevent="safeNavigate('shop', { query: { category: cat.slug } })"
                            class="mega-cat-link d-flex justify-content-between align-items-center fw-semibold text-decoration-none"
                            :class="{ 'text-primary-custom': hoveredCategory?.id === cat.id }">
                            {{ cat.name }} <i class="bi bi-arrow-right-short opacity-50"></i>
                          </a>
                        </li>
                      </ul>
                    </div>

                    <div class="mega-products-preview p-4 flex-grow-1 bg-white">
                      <h6 class="fw-bold mb-3 text-uppercase text-muted font-oswald" style="letter-spacing: 1px;">
                        Nổi bật: {{ hoveredCategory ? hoveredCategory.name : 'Mới Nhất' }}
                      </h6>
                      <div class="row g-3" v-if="hoveredCategory && hoveredCategory.top_products">
                        <div class="col-3" v-for="prod in hoveredCategory.top_products" :key="prod.id">
                          <div class="mega-product-card cursor-pointer" @click="goToProduct(prod.slug)">
                            <div class="mega-img-wrap bg-light rounded-3 mb-2 overflow-hidden border">
                              <img :src="getImage(prod.thumbnail_image)"
                                class="w-100 h-100 object-fit-cover transition-transform" alt="Product">
                            </div>
                            <h6 class="small fw-bold text-truncate mb-1">{{ prod.name }}</h6>
                            <div class="text-danger fw-bold small">{{ formatCurrency(prod.promotional_price ||
                              prod.base_price) }}</div>
                          </div>
                        </div>
                      </div>
                      <div v-else class="text-muted small fst-italic">Không có sản phẩm nổi bật.</div>
                    </div>
                  </div>
                </div>
              </transition>
            </li>


            <router-link :to="{ name: 'client-combos' }" class="nav-item-link">
              BỘ SƯU TẬP
            </router-link>
            <li><a href="#" @click.prevent="safeNavigate('gifts')" class="nav-item-link">QUÀ TẶNG</a></li>
            <li><a href="#" @click.prevent="safeNavigate('blog')" class="nav-item-link">TIN TỨC</a></li>
          </ul>
        </nav>

        <div class="search-trigger-wrapper position-absolute end-0 d-flex align-items-center">
          <span class="text-muted fw-light opacity-50 me-3" style="font-size: 1.2rem;">|</span>

          <div class="search-box-luxury position-relative" ref="searchContainer">
            <form @submit.prevent="handleSearch" class="position-relative d-flex align-items-center">
              <input type="text" class="form-control bg-transparent border-0 shadow-none pe-4 font-oswald tracking-wide"
                placeholder="Tìm kiếm..." v-model="searchQuery" @input="onSearchInput"
                @focus="showSearchResults = true">
              <button type="submit" class="btn border-0 p-0 position-absolute end-0 text-dark hover-primary">
                <i v-if="isFetchingSearch" class="spinner-border spinner-border-sm text-muted"></i>
                <i v-else class="bi bi-search fs-6"></i>
              </button>
              <div class="search-underline"></div>
            </form>

            <transition name="fade">
              <div v-if="showSearchResults && searchQuery.length > 0"
                class="search-results-dropdown shadow-lg rounded-4 overflow-hidden border mt-2 bg-white position-absolute end-0"
                style="width: 320px; z-index: 1050;">

                <div v-if="categoryResults.length > 0" class="p-2 bg-light border-bottom text-start">
                  <div class="small fw-bold text-muted text-uppercase px-2 mb-1" style="font-size: 0.7rem;">Danh mục
                  </div>
                  <ul class="list-unstyled m-0">
                    <li v-for="cat in categoryResults" :key="cat.id">
                      <a href="#" @click.prevent="goToCategory(cat.slug)"
                        class="d-block px-3 py-2 text-dark text-decoration-none hover-bg-light rounded fw-semibold">
                        <i class="bi bi-folder2-open me-2 text-primary-custom"></i> {{ cat.name }}
                      </a>
                    </li>
                  </ul>
                </div>

                <div v-if="searchResults.length > 0" class="p-2 text-start">
                  <div class="small fw-bold text-muted text-uppercase px-2 mb-1 mt-1" style="font-size: 0.7rem;">
                    {{ isCategoryFallback ? 'Gợi ý từ danh mục' : 'Sản phẩm' }}
                  </div>
                  <ul class="list-unstyled m-0">
                    <li v-for="prod in searchResults" :key="prod.id">
                      <a href="#" @click.prevent="goToProduct(prod.slug)"
                        class="d-flex align-items-center px-2 py-2 text-dark text-decoration-none hover-bg-light rounded gap-3">
                        <img :src="getImage(prod.thumbnail_image)" class="rounded border object-fit-cover bg-white"
                          style="width: 40px; height: 40px;">
                        <div class="overflow-hidden">
                          <div class="small fw-bold text-truncate" v-html="highlightText(prod.name)"></div>
                          <div class="small fw-bold text-danger">{{ formatCurrency(prod.promotional_price ||
                            prod.base_price)
                            }}</div>
                        </div>
                      </a>
                    </li>
                  </ul>
                </div>

                <div v-if="!isFetchingSearch && categoryResults.length === 0 && searchResults.length === 0"
                  class="p-4 text-center text-muted">
                  <i class="bi bi-emoji-frown fs-3 d-block mb-2"></i>
                  <span class="small">Không tìm thấy kết quả cho "{{ searchQuery }}"</span>
                </div>

                <div v-if="searchResults.length > 0" class="p-2 border-top bg-light text-center">
                  <a href="#" @click.prevent="handleSearch"
                    class="small fw-bold text-primary-custom text-decoration-none">
                    Xem tất cả <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
              </div>
            </transition>
          </div>
        </div>

      </div>
    </div>

    <div class="border-bottom opacity-50"></div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

const router = useRouter();
const BACKEND_URL = 'http://127.0.0.1:8000';

const sysConfig = ref({ phone: '12345678910', email: 'SORA@GMAIL.COM', facebook: '#', instagram: '#', twitter: '#' });
const user = ref(null);
const isUserMenuOpen = ref(false);
const userMenuContainer = ref(null);

const isMegaMenuOpen = ref(false);
const categories = ref([]);
const hoveredCategory = ref(null);

const searchQuery = ref('');
const searchResults = ref([]);
const categoryResults = ref([]);
const showSearchResults = ref(false);
const searchContainer = ref(null);
const isCategoryFallback = ref(false);
let searchDebounce = null;
const isFetchingSearch = ref(false);

const cartItemCount = ref(0);

const safeNavigate = (routeName, options = {}) => {
  if (router.hasRoute(routeName)) {
    router.push({ name: routeName, ...options });
  } else {
    Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Tính năng đang được phát triển!', showConfirmButton: false, timer: 2000 });
  }
};

const getImage = (path) => path ? `${BACKEND_URL}/storage/${path}` : 'https://placehold.co/100x100?text=No+Image';
const handleLogoError = (e) => { e.target.outerHTML = '<h2 class="font-oswald fw-bold text-dark m-0 tracking-wide">S O R A</h2>'; };
const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);
const highlightText = (text) => {
  if (!searchQuery.value) return text;
  return text.replace(new RegExp(`(${searchQuery.value})`, 'gi'), '<mark class="text-primary-custom bg-transparent p-0">$1</mark>');
};

const fetchHeaderData = async () => {
  try {
    const res = await axios.get(`${BACKEND_URL}/api/client/header-data`);
    if (res.data.success) {
      categories.value = res.data.data.categories;
      if (categories.value.length > 0) hoveredCategory.value = categories.value[0];
      if (res.data.data.config) sysConfig.value = { ...sysConfig.value, ...res.data.data.config };
    }
  } catch (error) { console.error('Lỗi tải Menu:', error); }
};

const fetchUserProfile = async () => {
  const token = localStorage.getItem('auth_token');
  if (!token) return;

  try {
    const res = await axios.get(`${BACKEND_URL}/api/user`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    user.value = res.data;
    localStorage.setItem('userData', JSON.stringify(res.data));
  } catch (error) {
    console.error("Token hết hạn hoặc chưa đăng nhập hợp lệ:", error);
    localStorage.removeItem('auth_token');
    localStorage.removeItem('userData');
    user.value = null;
  }
};

const performSearch = async (query) => {
  if (!query) {
    searchResults.value = []; categoryResults.value = []; isCategoryFallback.value = false;
    return;
  }
  isFetchingSearch.value = true;
  try {
    const res = await axios.get(`${BACKEND_URL}/api/client/search`, { params: { keyword: query } });
    if (res.data.success) {
      searchResults.value = res.data.data.products;
      categoryResults.value = res.data.data.categories;
      isCategoryFallback.value = res.data.data.is_category_fallback;
    }
  } catch (e) { console.error(e); } finally { isFetchingSearch.value = false; }
};

const onSearchInput = (e) => {
  const query = e.target.value.trim();
  if (!query) {
    showSearchResults.value = false; searchResults.value = []; categoryResults.value = [];
    return;
  }
  showSearchResults.value = true;
  if (searchDebounce) clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => { performSearch(query); }, 300);
};

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    showSearchResults.value = false;
    safeNavigate('shop', { query: { q: searchQuery.value } });
  }
};

const goToProduct = (slug) => {
  showSearchResults.value = false; isMegaMenuOpen.value = false;
  safeNavigate('ProductDetail', { params: { slug } });
};

const goToCategory = (slug) => {
  showSearchResults.value = false; isMegaMenuOpen.value = false;
  safeNavigate('shop', { query: { category: slug } });
};

const toggleUserMenu = () => { isUserMenuOpen.value = !isUserMenuOpen.value; };
const toggleMobileMenu = () => { };

const handleClickOutside = (e) => {
  if (userMenuContainer.value && !userMenuContainer.value.contains(e.target)) isUserMenuOpen.value = false;
  if (searchContainer.value && !searchContainer.value.contains(e.target)) showSearchResults.value = false;
};

const handleLogout = () => {
  Swal.fire({
    title: 'Đăng xuất?', text: 'Bạn có chắc muốn đăng xuất khỏi tài khoản?', icon: 'question',
    showCancelButton: true, confirmButtonColor: '#9f273b', cancelButtonColor: '#6c757d', confirmButtonText: 'Đăng xuất'
  }).then((result) => {
    if (result.isConfirmed) {
      localStorage.removeItem('userData');
      localStorage.removeItem('auth_token');
      user.value = null;
      isUserMenuOpen.value = false;
      safeNavigate('home');
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã đăng xuất', showConfirmButton: false, timer: 1500 });
    }
  });
};

onMounted(() => {
  fetchHeaderData();
  const userData = localStorage.getItem('userData');
  if (userData) {
    user.value = JSON.parse(userData);
  }
  fetchUserProfile();
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap');

:root {
  --primary: #9f273b;
  --secondary: #e7ce7d;
  --accent: #cc1e2e;
}

.text-primary-custom {
  color: #9f273b !important;
}

.bg-primary-custom {
  background-color: #9f273b !important;
}

.border-primary-custom {
  border-color: #9f273b !important;
}

.btn-brand {
  background-color: #9f273b;
  border: none;
  transition: 0.2s;
  color: white !important;
}

.btn-brand:hover {
  background-color: #801f2f;
  color: white !important;
}

.hover-primary:hover {
  color: #9f273b !important;
}

.transition-color {
  transition: color 0.2s ease;
}

.hover-bg-light:hover {
  background-color: #f8f9fa;
}

.font-oswald {
  font-family: 'Oswald', sans-serif !important;
}

.tracking-wide {
  letter-spacing: 0.5px;
}

.site-header {
  z-index: 1040;
  background-color: #fff;
}

.top-link {
  font-family: 'Oswald', sans-serif;
  font-size: 0.8rem;
  font-weight: 500;
  color: #333;
  text-decoration: none;
  letter-spacing: 1px;
  transition: color 0.2s ease;
}

.top-link:hover {
  color: #9f273b;
}

.icon-link {
  color: #333;
  font-size: 1.3rem;
  text-decoration: none;
}

.cart-badge {
  position: absolute;
  top: -4px;
  right: -8px;
  background-color: #9f273b;
  color: white;
  font-size: 0.6rem;
  font-weight: bold;
  padding: 2px 5px;
  border-radius: 50px;
  border: 1px solid #fff;
}

.logo-img {
  height: 80px;
  width: auto;
  object-fit: contain;
  display: block;
}

.nav-item-link {
  color: #333;
  text-decoration: none;
  font-family: 'Oswald', sans-serif;
  font-weight: 500;
  font-size: 1rem;
  letter-spacing: 1.5px;
  padding: 5px 0;
  position: relative;
  transition: color 0.2s ease;
}

.nav-item-link::after {
  content: '';
  position: absolute;
  width: 0;
  height: 2px;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  background-color: #9f273b;
  transition: width 0.3s ease;
}

.nav-item-link:hover {
  color: #9f273b;
}

.nav-item-link:hover::after {
  width: 100%;
}

.search-box-luxury {
  width: 220px;
}

.search-box-luxury input {
  font-size: 0.85rem;
  color: #333;
}

.search-box-luxury input:focus {
  outline: none;
  box-shadow: none;
}

.search-underline {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 1px;
  background-color: #ddd;
  transition: background-color 0.3s ease;
}

.search-box-luxury input:focus~.search-underline {
  background-color: #9f273b;
  height: 2px;
}

.mega-menu-wrapper {
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  width: 800px;
  background: #fff;
  border-radius: 0 0 8px 8px;
  overflow: hidden;
  margin-top: 10px;
  cursor: default;
}

.mega-cat-link {
  color: #333;
  padding: 8px 12px;
  border-radius: 6px;
  transition: all 0.2s;
}

.mega-cat-link:hover {
  background-color: #f9f9f9;
  color: #9f273b;
  padding-left: 16px;
}

.mega-product-card {
  transition: transform 0.2s ease;
}

.mega-product-card:hover {
  transform: translateY(-5px);
}

.mega-product-card:hover h6 {
  color: #9f273b;
}

.mega-img-wrap {
  aspect-ratio: 1;
}

.search-results-dropdown,
.user-dropdown {
  border-color: #eee !important;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08) !important;
}

.dropdown-item {
  font-size: 0.9rem;
  transition: background 0.2s;
  color: #333;
}

.dropdown-item:hover {
  background-color: #f8f9fa;
  color: #9f273b;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translate(-50%, 10px);
}
</style>