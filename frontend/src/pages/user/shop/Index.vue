<template>
  <div class="shop-page min-vh-100 bg-white" v-if="!isPageLoading">
    
    <!-- HEADER BREADCRUMB -->
    <div class="container-fluid px-4 py-3 border-bottom sora-border-light">
      <div class="d-flex justify-content-between align-items-center text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.15em;">
       
        <div class="d-flex gap-4 align-items-center d-none d-md-flex">
          <div class="text-muted fw-light">
            {{ pagination.total }} SẢN PHẨM
          </div>
          <div class="d-flex gap-3 align-items-center border-start sora-border-light ps-4">
            <span class="fw-medium text-dark cursor-pointer hover-text-gold transition-colors">LƯỚI <i class="fa-solid fa-border-all ms-1"></i></span>
            <span class="text-muted cursor-pointer hover-text-dark transition-colors">CHO ẨN <i class="fa-regular fa-square ms-1"></i></span>
          </div>
        </div>
      </div>
    </div>

    <!-- DANH MỤC NỔI BẬT (CATEGORY SECTION) -->
    <section class="category-section py-5 border-bottom sora-border-light" style="background-color: #faf9f7;">
      <div class="container-fluid px-4 py-md-4">
        
        <div class="d-flex flex-column align-items-center text-center mb-5">
          <p class="text-uppercase text-gold mb-2" style="font-size: 0.75rem; letter-spacing: 0.2em; color: var(--sora-secondary); font-weight: 600;">Bộ Sưu Tập</p>
          <h2 class="playfair-font text-dark fw-normal mb-3" style="font-size: 2.5rem; letter-spacing: 0.02em;">Khám Phá Danh Mục</h2>
          <div class="divider-gold mb-4"></div>
          <p class="text-muted mb-4" style="font-size: 0.95rem; max-width: 500px;">Những tuyệt tác trang sức tinh xảo được chế tác thủ công dành riêng cho sự tỏa sáng của bạn.</p>
          <a href="#" class="text-dark text-uppercase fw-medium text-decoration-none ls-wider hover-text-gold transition-colors pb-1 border-bottom border-dark sora-link-explore" style="font-size: 0.75rem;">
            Xem Tất Cả <i class="fa-solid fa-arrow-right ms-1" style="font-size: 0.7rem;"></i>
          </a>
        </div>

        <div v-if="isLoadingCategories" class="d-flex justify-content-center py-5">
          <div class="spinner-border" style="color: var(--sora-secondary); width: 2rem; height: 2rem; border-width: 0.1em;" role="status"></div>
        </div>

        <div v-else class="row g-4">
          <!-- Render danh mục thật từ DB -->
          <div class="col-6 col-md-3" v-for="cat in categories.slice(0, 4)" :key="cat.id">
            <div class="category-card group position-relative overflow-hidden cursor-pointer bg-white shadow-sm" @click="filterByCategory(cat.slug)">
              <div class="img-wrapper w-100 p-2" style="aspect-ratio: 3/4;">
                <div class="w-100 h-100 position-relative overflow-hidden">
                  <img :src="getImageUrl(cat.thumbnail)" :alt="cat.name" class="w-100 h-100 object-fit-cover transition-transform duration-700 group-hover-scale">
                  <div class="overlay-gradient position-absolute top-0 start-0 w-100 h-100 pointer-events-none"></div>
                  <!-- Viền trang trí bên trong khi hover -->
                  <div class="inner-frame position-absolute pointer-events-none transition-all duration-500"></div>
                </div>
              </div>
              <div class="position-absolute bottom-0 start-0 w-100 p-4 z-2 text-center text-white pb-5">
                <h1 class="playfair-font fs-4 mb-2 group-hover-translate-y transition-all duration-500 text-shadow-sm">{{ cat.name }}</h1>
                <span class="text-uppercase ls-widest mt-1 d-inline-block text-gold opacity-0 group-hover-opacity-100 transition-all duration-500" style="font-size: 0.65rem; color: var(--sora-secondary);">
                  Chi Tiết <i class="fa-solid fa-chevron-right ms-1" style="font-size: 0.55rem;"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- MAIN CONTENT: BỘ LỌC VÀ LƯỚI SẢN PHẨM -->
    <div class="container-fluid px-4 py-5 mt-3">
      <div class="row">
        
        <!-- SIDEBAR BỘ LỌC (LEFT) -->
        <div class="col-lg-2 col-md-3 d-none d-md-block sidebar-filter pe-4 border-end sora-border-light">
          
          <!-- SẮP XẾP THEO -->
          <div class="filter-group mb-5">
            <h5 class="filter-title playfair-font mb-4 fs-5 fw-normal text-dark">Sắp xếp theo</h5>
            <div class="filter-options">
              <label class="custom-radio">
                <input type="radio" v-model="filters.sort" value="price_asc" @change="applyFilters">
                <span class="checkmark transition-colors"></span>
                <span class="label-text">Giá: Thấp đến Cao</span>
              </label>
              <label class="custom-radio">
                <input type="radio" v-model="filters.sort" value="price_desc" @change="applyFilters">
                <span class="checkmark transition-colors"></span>
                <span class="label-text">Giá: Cao đến Thấp</span>
              </label>
              <label class="custom-radio">
                <input type="radio" v-model="filters.sort" value="recommended" @change="applyFilters">
                <span class="checkmark transition-colors"></span>
                <span class="label-text">Nổi bật nhất</span>
              </label>
            </div>
          </div>

          <!-- CÁC NHÓM LỌC COLLAPSE -->
          <div class="accordion-filter mb-2" v-for="(group, index) in filterGroups" :key="index">
            <div class="filter-header d-flex justify-content-between align-items-center py-3 cursor-pointer hover-text-gold transition-colors" @click="toggleGroup(index)">
              <h5 class="filter-title playfair-font mb-0 text-capitalize fs-6 fw-normal text-dark">{{ group.title }}</h5>
              <i class="fa-solid fa-chevron-down transition-transform duration-300 text-muted" :class="{'rotate-180': group.isOpen}" style="font-size: 0.7rem;"></i>
            </div>
            
            <div class="filter-body overflow-hidden transition-all duration-500" :style="{ maxHeight: group.isOpen ? '500px' : '0', opacity: group.isOpen ? 1 : 0 }">
              <div class="filter-options pb-4 pt-1">
                
                <!-- BỘ LỌC ĐÃ ĐƯỢC LÀM LẠI: HIỂN THỊ TRỰC QUAN & TỰ ĐỘNG RESET CÁI CŨ -->
                <div class="custom-filter-item d-flex align-items-center cursor-pointer mb-3" 
                     v-for="(option, optIdx) in group.options" :key="optIdx"
                     @click="toggleFilter(group.key, option.value)">
                  
                  <!-- Ô Vuông Checkbox (Tự Custom Bằng CSS để chắc chắn hoạt động) -->
                  <div class="status-box me-3 d-flex align-items-center justify-content-center transition-all duration-300 shadow-sm"
                       :class="filters[group.key] === option.value ? 'bg-primary-custom border-primary-custom' : 'bg-white border-muted'">
                    <!-- Icon Check (Chỉ hiện khi isActive) -->
                    <i class="fa-solid fa-check text-white" 
                       :class="filters[group.key] === option.value ? 'opacity-100' : 'opacity-0'" 
                       style="font-size: 0.65rem;"></i>
                  </div>

                  <!-- Text Tên Danh Mục (Sẽ in đậm khi được chọn) -->
                  <span class="label-text transition-colors" 
                        :class="filters[group.key] === option.value ? 'text-primary-custom fw-bold' : 'text-muted fw-normal'"
                        style="font-size: 0.95rem;">
                    {{ option.label }}
                  </span>
                </div>

              </div>
            </div>
            <hr class="my-0 sora-border-light">
          </div>
        </div>

        <!-- MAIN PRODUCT GRID (RIGHT) -->
        <div class="col-lg-10 col-md-9 ps-lg-5">
          
          <div v-if="isLoadingProducts" class="d-flex justify-content-center align-items-center" style="min-height: 400px;">
            <div class="spinner-border" style="color: var(--sora-primary); width: 2.5rem; height: 2.5rem; border-width: 0.1em;" role="status"></div>
          </div>

          <div v-else class="product-grid">
            <!-- Render danh sách sản phẩm từ DB -->
            <template v-for="(product, index) in allProducts" :key="product.id">
              
              <!-- EDITORIAL BLOCK 1: Xen kẽ nội dung -->
              <div v-if="index === 3" class="editorial-block bg-white border sora-border-light d-flex flex-column flex-md-row align-items-stretch w-100 overflow-hidden mb-5 mt-3 shadow-sm">
                <div class="ed-text d-flex flex-column justify-content-center align-items-center align-items-md-start text-center text-md-start p-5" style="flex: 1; background-color: #faf9f7;">
                  <p class="text-uppercase mb-2" style="font-size: 0.7rem; letter-spacing: 0.2em; color: var(--sora-primary);">Hơi Thở Thời Đại</p>
                  <h2 class="playfair-font fw-normal text-dark mb-3" style="font-size: 2.25rem;">Bộ sưu tập Mới</h2>
                  <p class="text-muted mb-4 pe-md-4 fw-light" style="font-size: 0.95rem; line-height: 1.8;">Khám phá những thiết kế độc bản mang dấu ấn riêng, tôn vinh vẻ đẹp kiêu sa và đẳng cấp của phái đẹp.</p>
                  <div>
                    <button class="btn sora-btn-outline px-5 py-3 text-uppercase fw-medium ls-wider transition-colors" style="font-size: 0.75rem;">
                      Khám Phá Ngay
                    </button>
                  </div>
                </div>
                <div class="ed-image" style="flex: 1;">
                  <img src="https://images.unsplash.com/photo-1605100804763-247f66126e28?q=80&w=800&auto=format&fit=crop" class="w-100 h-100 object-fit-cover" alt="BST Mới">
                </div>
              </div>
              
              <!-- PRODUCT CARD -->
              <div class="product-card group cursor-pointer d-flex flex-column mb-4 bg-white position-relative" @click="goToProductDetail(product.slug)">
                
                <!-- CONTAINER ẢNH VÀ NÚT OVERLAY -->
                <div class="img-wrapper position-relative mb-3 overflow-hidden d-flex align-items-center justify-content-center sora-img-container bg-light" 
                     style="aspect-ratio: 1/1;"
                     :class="{'has-hover-image': hasHoverImage(product)}">
                  
                  <!-- Tags -->
                  <div class="position-absolute top-0 start-0 w-100 p-3 d-flex justify-content-between z-3 pointer-events-none">
                    <span v-if="product.is_new" class="badge-custom text-dark shadow-sm bg-white border border-light">MỚI</span>
                    <span v-if="product.promotional_price" class="badge-custom text-white shadow-sm" style="background-color: var(--sora-accent);">SALE</span>
                  </div>
                  
                  <!-- Ảnh Chính -->
                  <img :src="getImageUrl(product.thumbnail_image)" 
                       :alt="product.name" 
                       class="sora-main-img w-100 h-100 object-fit-cover bg-white mix-blend-multiply">
                  
                  <!-- Ảnh Phụ -->
                  <img v-if="hasHoverImage(product)"
                       :src="getImageUrl(product.hover_image)" 
                       :alt="product.name + ' hover'" 
                       class="sora-hover-img position-absolute top-0 start-0 w-100 h-100 object-fit-cover">

                  <!-- GIAO DIỆN NÚT THÊM VÀO GIỎ -->
                  <div class="sora-add-cart-overlay position-absolute bottom-0 start-0 w-100 p-3 z-3 d-flex justify-content-center transition-all duration-400">
                    <button class="btn w-100 text-uppercase fw-medium ls-wider py-2 sora-add-cart-btn shadow" 
                            @click.stop="() => { /* Viết hàm addToCart vào đây */ }">
                      <i class="fa-solid fa-cart-plus me-2"></i> Thêm Vào Giỏ
                    </button>
                  </div>
                </div>
                
                <!-- THÔNG TIN SẢN PHẨM -->
                <div class="product-info text-center d-flex flex-column flex-grow-1 px-2 pb-2">
                  <p class="product-desc text-muted mb-1 text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.1em;">
                    {{ product.category?.name || 'Trang sức' }}
                  </p>
                  <h3 class="product-name playfair-font text-dark fw-normal mb-2" style="font-size: 1.1rem; letter-spacing: 0.03em;">{{ product.name }}</h3>
                  
                  <div class="mt-auto pt-1">
                    <div class="d-flex align-items-center justify-content-center gap-3">
                      <p v-if="product.promotional_price" class="text-muted text-decoration-line-through mb-0 fw-light" style="font-size: 0.85rem;">
                        {{ formatPrice(product.base_price) }}
                      </p>
                      <p class="product-price mb-0" style="color: var(--sora-primary); font-size: 0.95rem; font-weight: 500;">
                        {{ formatPrice(product.promotional_price || product.base_price) }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

            </template>
          </div>

          <!-- Không có sản phẩm -->
          <div v-if="allProducts.length === 0 && !isLoadingProducts" class="text-center py-5 my-5 bg-light sora-border-light border">
            <i class="fa-solid fa-gem fs-1 mb-3" style="color: var(--sora-secondary);"></i>
            <p class="text-dark playfair-font fs-4 mb-2">Không tìm thấy kiệt tác nào</p>
            <p class="text-muted mb-4 fw-light">Vui lòng thử thay đổi bộ lọc hoặc tìm kiếm khác.</p>
            <button @click="resetFilters" class="btn text-uppercase ls-widest px-5 py-3 transition-colors sora-btn-primary" style="font-size: 0.8rem;">Xóa Bộ Lọc</button>
          </div>

          <!-- PAGINATION -->
          <div v-if="pagination.last_page > 1" class="d-flex justify-content-center align-items-center gap-4 mt-5 pt-5 border-top sora-border-light">
            <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="btn rounded-0 px-4 py-2 text-uppercase fw-medium ls-wider transition-colors sora-btn-outline" style="font-size: 0.75rem;">
              <i class="fa-solid fa-angle-left me-2"></i> Trước
            </button>
            <span class="px-3 playfair-font fs-5 text-dark">
              <span class="fw-bold">{{ pagination.current_page }}</span> 
              <span class="text-muted mx-2 fw-light">/</span> 
              <span class="text-muted fw-light">{{ pagination.last_page }}</span>
            </span>
            <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="btn rounded-0 px-4 py-2 text-uppercase fw-medium ls-wider transition-colors sora-btn-outline" style="font-size: 0.75rem;">
              Sau <i class="fa-solid fa-angle-right ms-2"></i>
            </button>
          </div>

        </div>
      </div>
    </div>
  </div>
  
  <div v-else class="vh-100 w-100 d-flex flex-column align-items-center justify-content-center bg-white">
      <div class="spinner-border mb-4" style="color: var(--sora-primary); width: 3rem; height: 3rem; border-width: 0.1em;" role="status"></div>
      <h2 class="playfair-font fw-normal ls-widest text-uppercase fs-6 text-dark" style="letter-spacing: 0.2em;">Đang Chuẩn Bị...</h2>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const shopSlug = ref(route.params.shop_slug || 'aurora-jewelry');
const API_BASE_URL = 'http://127.0.0.1:8000';

const isPageLoading = ref(true);
const isLoadingCategories = ref(true);
const isLoadingProducts = ref(true);
const categories = ref([]);
const allProducts = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });

// Filters State (Chuỗi rỗng '' đảm bảo chỉ chọn 1 lựa chọn mỗi nhóm, chọn mới tự ghi đè cái cũ)
const filters = reactive({
  sort: 'recommended',
  categories: '',
  collections: '',
  metals: ''
});

// Sidebar Data
const filterGroups = ref([
  {
    title: 'Danh Mục', key: 'categories', isOpen: true,
    options: [] 
  },
  {
    title: 'Bộ Sưu Tập', key: 'collections', isOpen: true,
    options: [
      { label: 'Solitaire Classic', value: 'solitaire' },
      { label: 'Halo Brilliance', value: 'halo' },
      { label: 'Royal Sapphire', value: 'royal' }
    ]
  }
]);

const toggleGroup = (index) => {
  filterGroups.value[index].isOpen = !filterGroups.value[index].isOpen;
};

// HÀM LỌC: Chịu trách nhiệm reset cái cũ để chạy danh mục khác
const toggleFilter = (key, value) => {
  if (filters[key] === value) {
    // Nếu đang bấm vào đúng cái đang chọn -> Huỷ bỏ lọc
    filters[key] = ''; 
  } else {
    // Nếu bấm mục KHÁC -> Gán bằng mục mới (Logic này tự động Reset bỏ chọn mục cũ)
    filters[key] = value; 
  }
  // Gửi thay đổi lên API lấy danh sách sản phẩm trang 1
  applyFilters();
};

const formatPrice = (price) => {
    if (!price || isNaN(price)) return 'Liên Hệ';
    return new Intl.NumberFormat('vi-VN').format(price) + '₫';
}

const getImageUrl = (path) => {
  if (!path) return 'https://images.unsplash.com/photo-1605100804763-247f66126e28?q=80&w=600&auto=format&fit=crop';
  if (path.startsWith('http')) return path;
  return `${API_BASE_URL}/storage/${path}`;
};

const hasHoverImage = (product) => {
  return product.hover_image && product.hover_image !== product.thumbnail_image;
};

const fetchCategories = async () => {
    isLoadingCategories.value = true;
    try {
        const response = await fetch(`${API_BASE_URL}/api/shop/${shopSlug.value}/categories`);
        const data = await response.json();
        if(data && data.success) {
            categories.value = data.data;
            const categoryGroupIndex = filterGroups.value.findIndex(g => g.key === 'categories');
            if (categoryGroupIndex !== -1) {
              filterGroups.value[categoryGroupIndex].options = data.data.map(cat => ({
                label: cat.name,
                value: cat.slug
              }));
            }
        }
    } catch (error) {
        console.error("Lỗi lấy danh mục:", error);
    } finally {
        isLoadingCategories.value = false;
    }
};

const buildQueryString = (page) => {
  const params = new URLSearchParams();
  params.append('page', page);
  if (filters.sort) params.append('sort', filters.sort);
  
  Object.keys(filters).forEach(key => {
    if (key !== 'sort' && filters[key]) {
      params.append(key, filters[key]);
    }
  });
  return params.toString();
};

// === CALL API SẢN PHẨM (REAL DATA) ===
const fetchProducts = async (page = 1) => {
    isLoadingProducts.value = true;
    try {
        const query = buildQueryString(page);
        const response = await fetch(`${API_BASE_URL}/api/shop/${shopSlug.value}/products?${query}`);
        const data = await response.json();
        
        if(data && data.success) {
            allProducts.value = data.data.data;
            pagination.value = { 
              current_page: data.data.current_page, 
              last_page: data.data.last_page, 
              total: data.data.total 
            };
        } else {
             allProducts.value = [];
        }
    } catch (error) {
        console.error("Lỗi lấy sản phẩm:", error);
        allProducts.value = [];
    } finally {
        isLoadingProducts.value = false;
    }
};

const filterByCategory = (categorySlug) => {
  // Khi chọn từ danh mục banner to ở trên, tự động Reset mọi thứ và chỉ lọc theo danh mục này
  filters.categories = categorySlug;
  filters.collections = ''; 
  filters.metals = '';
  
  applyFilters();
  window.scrollTo({ top: document.querySelector('.product-grid')?.offsetTop || 500, behavior: 'smooth' });
};

const applyFilters = () => { fetchProducts(1); };

const resetFilters = () => {
    filters.sort = 'recommended';
    filters.categories = '';
    filters.collections = '';
    filters.metals = '';
    applyFilters();
};

const changePage = (page) => {
    if(page >= 1 && page <= pagination.value.last_page) {
        fetchProducts(page);
        window.scrollTo({ top: document.querySelector('.category-section')?.offsetHeight || 0, behavior: 'smooth' });
    }
};

const goToProductDetail = (slug) => {
  if (!slug) return;
  router.push({ 
    name: 'productDetail', 
    params: { 
      shop_slug: shopSlug.value,
      slug: slug 
    } 
  });
};

onMounted(() => { 
  Promise.all([fetchCategories(), fetchProducts(1)]).then(() => {
      isPageLoading.value = false;
  });
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&display=swap');

/* --- HỆ THỐNG MÀU SẮC LUXURY --- */
:root {
  --sora-primary: #9f273b;   /* Đỏ rượu chính */
  --sora-secondary: #e7ce7d; /* Vàng cát phụ */
  --sora-accent: #cc1e2e;    /* Đỏ tươi Sale */
  --sora-text: #2c2c2c;
  --sora-border: #eaeaea;
}

.shop-page { font-family: 'Inter', sans-serif; color: var(--sora-text); }
.playfair-font { font-family: 'Playfair Display', serif; }
.ls-widest { letter-spacing: 0.15em; }
.ls-wider { letter-spacing: 0.08em; }
.fw-light { font-weight: 300; }
.fw-medium { font-weight: 500; }

.cursor-pointer { cursor: pointer; }
.transition-all { transition: all 0.3s ease; }
.transition-colors { transition: color 0.3s ease, background-color 0.3s ease, border-color 0.3s ease; }
.transition-transform { transition: transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94); }
.duration-300 { transition-duration: 0.3s; }
.duration-400 { transition-duration: 0.4s; }
.duration-500 { transition-duration: 0.5s; }
.duration-700 { transition-duration: 0.7s; }

.rotate-180 { transform: rotate(180deg); }
.opacity-0 { opacity: 0 !important; }
.opacity-100 { opacity: 1 !important; }
.pointer-events-none { pointer-events: none; }
.text-shadow-sm { text-shadow: 0 2px 15px rgba(0,0,0,0.4); }

/* Custom Colors & Borders */
.sora-border-light { border-color: var(--sora-border) !important; }
.hover-text-dark:hover { color: #000 !important; }
.hover-text-gold:hover { color: var(--sora-secondary) !important; }
.divider-gold { width: 40px; height: 1px; background-color: var(--sora-secondary); margin: 0 auto; }

/* Buttons */
.sora-btn-primary { background-color: var(--sora-primary); color: #fff; border: 1px solid var(--sora-primary); }
.sora-btn-primary:hover { background-color: #831f30; border-color: #831f30; color: #fff; }
.sora-btn-outline { background-color: transparent; color: var(--sora-primary); border: 1px solid var(--sora-primary); }
.sora-btn-outline:hover { background-color: var(--sora-primary); color: var(--sora-secondary); }
.sora-link-explore:hover { border-bottom-color: var(--sora-secondary) !important; }

/* Category Section */
.overlay-gradient { background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0) 50%); transition: background 0.5s ease; }
.group:hover .overlay-gradient { background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.1) 100%); }
.group:hover .group-hover-scale { transform: scale(1.05); }
.group-hover-translate-y { transform: translateY(15px); }
.group:hover .group-hover-translate-y { transform: translateY(0); }
.inner-frame { top: 15px; left: 15px; right: 15px; bottom: 15px; border: 1px solid rgba(231, 206, 125, 0); z-index: 3; }
.group:hover .inner-frame { border-color: rgba(231, 206, 125, 0.6); transform: scale(0.96); }

/* Custom Radio (Cho phần sắp xếp) */
.custom-radio { position: relative; display: flex; align-items: center; cursor: pointer; font-size: 0.9rem; color: #555; transition: color 0.3s; }
.custom-radio input { position: absolute; opacity: 0; cursor: pointer; height: 0; width: 0; }
.custom-radio .checkmark { position: relative; display: inline-block; width: 16px; height: 16px; border: 1px solid #b0b0b0; border-radius: 50%; margin-right: 12px; flex-shrink: 0; transition: all 0.3s; }
.custom-radio:hover .checkmark { border-color: var(--sora-secondary); }
.custom-radio:hover .label-text { color: var(--sora-primary); }
.custom-radio input:checked ~ .checkmark { border-color: var(--sora-primary); border-width: 4px; }
.custom-radio input:checked ~ .label-text { color: var(--sora-primary); font-weight: 500; }

/* ----------------------------------------------------
   UI BỘ LỌC BÊN TRÁI ĐÃ FIX LẠI GIAO DIỆN KIỂM CHỨNG TRỰC QUAN 
   ---------------------------------------------------- */
.filter-options { display: flex; flex-direction: column; gap: 4px; }

/* Màu đỏ của trạng thái Active */
.bg-primary-custom { background-color: var(--sora-primary) !important; }
.border-primary-custom { border: 1px solid var(--sora-primary) !important; }
.text-primary-custom { color: var(--sora-primary) !important; }

/* Màu xám của trạng thái Inactive */
.border-muted { border: 1px solid #ced4da !important; }

/* Ô Vuông tự thiết kế, không dùng thẻ input default để tránh lỗi UI */
.status-box {
  width: 18px;
  height: 18px;
  border-radius: 4px;
}

/* Hover hiệu ứng cho dòng danh mục */
.custom-filter-item:hover .status-box:not(.bg-primary-custom) {
  border-color: var(--sora-secondary) !important;
}
.custom-filter-item:hover .label-text:not(.fw-bold) {
  color: var(--sora-primary) !important;
}

/* Lưới Sản Phẩm */
.product-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 3rem 1.5rem; }
.badge-custom { font-size: 0.6rem; font-weight: 500; letter-spacing: 0.15em; padding: 6px 14px; border-radius: 0; }
.mix-blend-multiply { mix-blend-mode: multiply; }

/* HÌNH ẢNH SẢN PHẨM */
.sora-main-img, .sora-hover-img { transition: opacity 0.6s ease; }
.sora-main-img { z-index: 1; }
.sora-hover-img { z-index: 2; opacity: 0; }
.group:hover .sora-img-container.has-hover-image .sora-main-img { opacity: 0; }
.group:hover .sora-img-container.has-hover-image .sora-hover-img { opacity: 1; }

/* NÚT THÊM VÀO GIỎ (OVERLAY TRƯỢT LÊN TỪ ẢNH) */
.sora-add-cart-overlay {
  transform: translateY(15px); /* Trượt từ dưới */
  opacity: 0;
  pointer-events: none;
}
.group:hover .sora-add-cart-overlay {
  transform: translateY(0);
  opacity: 1;
  pointer-events: auto;
}
.sora-add-cart-btn { 
  background-color: rgba(255, 255, 255, 0.95); /* Trắng mờ */
  color: var(--sora-text); 
  border: none;
  font-size: 0.75rem;
  border-radius: 4px;
  transition: all 0.3s ease; 
}
.sora-add-cart-btn:hover { 
  background-color: var(--sora-primary); 
  color: #ffffff; 
  transform: translateY(-2px); /* Nhấn nhẹ khi hover */
}

/* Responsive */
@media (max-width: 1200px) { .product-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px) { 
  .product-grid { grid-template-columns: repeat(1, 1fr); gap: 2rem; } 
  .editorial-block { flex-direction: column !important; } 
  .editorial-block > div { min-height: 350px; } 
}
</style>