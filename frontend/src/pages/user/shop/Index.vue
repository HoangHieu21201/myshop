<template>
  <div class="shop-page min-vh-100 bg-white" v-if="!isPageLoading">
    
    <!-- HEADER BREADCRUMB -->
    <div class="container-fluid px-4 py-3 border-bottom border-light">
      <div class="d-flex justify-content-between align-items-center text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.1em;">
       
        <div class="d-flex gap-4 align-items-center d-none d-md-flex">
          <div class="text-muted">
            {{ pagination.total }} SẢN PHẨM
          </div>
          <div class="d-flex gap-3 align-items-center border-start ps-4">
            <span class="fw-bold text-dark cursor-pointer hover-text-gray transition-colors">LƯỚI <i class="fa-solid fa-border-all ms-1"></i></span>
            <span class="text-muted cursor-pointer hover-text-dark transition-colors">CHO ẨN <i class="fa-regular fa-square ms-1"></i></span>
          </div>
        </div>
      </div>
    </div>

    <!-- DANH MỤC NỔI BẬT (CATEGORY SECTION) -->
    <section class="category-section py-5 border-bottom" style="border-color: #f5f5f5 !important;">
      <div class="container-fluid px-4">
        
        <div class="d-flex flex-column align-items-center text-center mb-5">
          <h2 class="playfair-font text-dark fw-normal mb-2" style="font-size: 2.25rem; letter-spacing: -0.02em;">Khám Phá Danh Mục</h2>
          <p class="text-muted mb-3" style="font-size: 0.95rem;">Những tuyệt tác trang sức tinh xảo dành riêng cho bạn</p>
          <a href="#" class="text-dark text-uppercase fw-bold text-decoration-none ls-wider hover-text-gray transition-colors pb-1 border-bottom border-dark mt-2" style="font-size: 0.75rem;">
            Xem Tất Cả <i class="fa-solid fa-arrow-right ms-1"></i>
          </a>
        </div>

        <div v-if="isLoadingCategories" class="d-flex justify-content-center py-5">
          <div class="spinner-border" style="color: var(--sora-primary); width: 2rem; height: 2rem; border-width: 0.15em;" role="status"></div>
        </div>

        <div v-else class="row g-3 g-md-4">
          <!-- Render danh mục thật từ DB -->
          <div class="col-6 col-md-3" v-for="cat in categories.slice(0, 4)" :key="cat.id">
            <div class="category-card group position-relative overflow-hidden cursor-pointer bg-light" @click="filterByCategory(cat.slug)">
              <div class="img-wrapper w-100" style="aspect-ratio: 3/4;">
                <img :src="getImageUrl(cat.thumbnail)" :alt="cat.name" class="w-100 h-100 object-fit-cover transition-transform duration-700 group-hover-scale">
                <div class="overlay-gradient position-absolute top-0 start-0 w-100 h-100 pointer-events-none"></div>
              </div>
              <div class="position-absolute bottom-0 start-0 w-100 p-4 z-2 text-white">
                <h3 class="playfair-font fs-4 mb-0 group-hover-translate-y transition-all duration-500 text-shadow-sm">{{ cat.name }}</h3>
                <span class="text-uppercase ls-widest mt-2 d-inline-block fw-bold opacity-0 group-hover-opacity-100 transition-all duration-500" style="font-size: 0.65rem;">
                  Khám Phá <i class="fa-solid fa-chevron-right ms-1" style="font-size: 0.55rem;"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- MAIN CONTENT: BỘ LỌC VÀ LƯỚI SẢN PHẨM -->
    <div class="container-fluid px-4 py-5 mt-2">
      <div class="row">
        
        <!-- SIDEBAR BỘ LỌC (LEFT) -->
        <div class="col-lg-2 col-md-3 d-none d-md-block sidebar-filter pe-4 border-end border-light">
          
          <!-- SẮP XẾP THEO -->
          <div class="filter-group mb-5">
            <h5 class="filter-title playfair-font mb-4 fs-5 fw-bold">Sắp xếp theo</h5>
            <div class="filter-options">
              <label class="custom-radio">
                <input type="radio" v-model="filters.sort" value="price_asc" @change="applyFilters">
                <span class="checkmark transition-colors"></span>
                Giá: Thấp đến Cao
              </label>
              <label class="custom-radio">
                <input type="radio" v-model="filters.sort" value="price_desc" @change="applyFilters">
                <span class="checkmark transition-colors"></span>
                Giá: Cao đến Thấp
              </label>
              <label class="custom-radio">
                <input type="radio" v-model="filters.sort" value="recommended" @change="applyFilters">
                <span class="checkmark transition-colors"></span>
                Nổi bật nhất
              </label>
            </div>
          </div>

          <!-- CÁC NHÓM LỌC COLLAPSE -->
          <div class="accordion-filter mb-3" v-for="(group, index) in filterGroups" :key="index">
            <div class="filter-header d-flex justify-content-between align-items-center py-3 cursor-pointer hover-text-gray transition-colors" @click="toggleGroup(index)">
              <h5 class="filter-title playfair-font mb-0 text-capitalize fs-6 fw-bold">{{ group.title }}</h5>
              <i class="fa-solid fa-chevron-down transition-transform duration-300" :class="{'rotate-180': group.isOpen}" style="font-size: 0.8rem;"></i>
            </div>
            
            <div class="filter-body overflow-hidden transition-all duration-500" :style="{ maxHeight: group.isOpen ? '500px' : '0', opacity: group.isOpen ? 1 : 0 }">
              <div class="filter-options pb-3 pt-2">
                <label class="custom-checkbox" v-for="(option, optIdx) in group.options" :key="optIdx">
                  <input type="checkbox" :value="option.value" v-model="filters[group.key]" @change="applyFilters">
                  <span class="checkmark transition-all"></span>
                  {{ option.label }}
                </label>
              </div>
            </div>
            <hr class="my-0 border-light">
          </div>
        </div>

        <!-- MAIN PRODUCT GRID (RIGHT) -->
        <div class="col-lg-10 col-md-9 ps-lg-5">
          
          <div v-if="isLoadingProducts" class="d-flex justify-content-center align-items-center" style="min-height: 400px;">
            <div class="spinner-border" style="color: var(--sora-primary); width: 2.5rem; height: 2.5rem; border-width: 0.15em;" role="status"></div>
          </div>

          <div v-else class="product-grid">
            <!-- Render danh sách sản phẩm từ DB -->
            <template v-for="(product, index) in allProducts" :key="product.id">
              
              <!-- EDITORIAL BLOCK 1: Xen kẽ nội dung -->
              <div v-if="index === 3" class="editorial-block bg-light d-flex flex-column flex-md-row align-items-stretch w-100 overflow-hidden mb-4 mt-2">
                <div class="ed-text d-flex flex-column justify-content-center p-5 bg-white" style="flex: 1;">
                  <h2 class="playfair-font fw-normal text-dark mb-3" style="font-size: 2.5rem;">Bộ sưu tập Mới</h2>
                  <p class="text-muted mb-4 pe-lg-4" style="font-size: 0.95rem; line-height: 1.6;">Khám phá những thiết kế độc bản mang dấu ấn riêng.</p>
                  <div>
                    <button class="btn btn-outline-dark rounded-0 px-4 py-2 text-uppercase fw-bold ls-wider transition-colors sora-btn-explore" style="font-size: 0.75rem;">
                      Khám Phá
                    </button>
                  </div>
                </div>
                <div class="ed-image bg-light" style="flex: 1;">
                  <img src="https://images.unsplash.com/photo-1605100804763-247f66126e28?q=80&w=800&auto=format&fit=crop" class="w-100 h-100 object-fit-cover" alt="BST Mới">
                </div>
              </div>
              
              <!-- PRODUCT CARD: ĐÃ THÊM SỰ KIỆN @click="goToProductDetail" -->
              <div class="product-card group cursor-pointer d-flex flex-column mb-3 bg-white position-relative" @click="goToProductDetail(product.slug)">
                
                <!-- CONTAINER ẢNH - Gắn class has-hover-image nếu có ảnh hover -->
                <div class="img-wrapper position-relative bg-light mb-3 overflow-hidden d-flex align-items-center justify-content-center sora-img-container" 
                     style="aspect-ratio: 1/1;"
                     :class="{'has-hover-image': hasHoverImage(product)}">
                  
                  <!-- Tags -->
                  <div class="position-absolute top-0 start-0 w-100 p-3 d-flex justify-content-between z-3 pointer-events-none">
                    <span v-if="product.is_new" class="badge-custom text-white shadow-sm" style="background-color: var(--sora-primary);">MỚI</span>
                    <span v-if="product.promotional_price" class="badge-custom text-white shadow-sm ms-auto" style="background-color: var(--sora-accent);">SALE</span>
                  </div>
                  
                  <!-- Ảnh Chính -->
                  <img :src="getImageUrl(product.thumbnail_image)" 
                       :alt="product.name" 
                       class="sora-main-img w-100 h-100 object-fit-cover bg-white">
                  
                  <!-- Ảnh Phụ (Chỉ render và hiện khi di chuột nếu có) -->
                  <img v-if="hasHoverImage(product)"
                       :src="getImageUrl(product.hover_image)" 
                       :alt="product.name + ' hover'" 
                       class="sora-hover-img position-absolute top-0 start-0 w-100 h-100 object-fit-cover">
                </div>
                
                <!-- Thông tin sản phẩm -->
                <div class="product-info text-start d-flex flex-column flex-grow-1 px-1">
                  <h3 class="product-name text-dark fw-bold mb-1" style="font-size: 0.95rem;">{{ product.name }}</h3>
                  <p class="product-desc text-muted mb-2" style="font-size: 0.8rem;">
                    {{ product.category?.name || 'Trang sức cao cấp' }}
                  </p>
                  
                  <div class="mt-auto pt-1">
                    <div class="d-flex align-items-baseline gap-2">
                      <!-- Giá màu đỏ rượu sora-primary -->
                      <p class="product-price fw-bold fs-6 mb-0" style="color: var(--sora-primary);">
                        {{ formatPrice(product.promotional_price || product.base_price) }}
                      </p>
                      <p v-if="product.promotional_price" class="text-muted text-decoration-line-through mb-0" style="font-size: 0.8rem;">
                        {{ formatPrice(product.base_price) }}
                      </p>
                    </div>

                    <!-- Nút Thêm Vào Giỏ - Mới cập nhật (Mở rộng từ dưới giá tiền) -->
                    <div class="sora-add-cart-wrapper overflow-hidden transition-all duration-400">
                      <!-- ĐÃ THÊM @click.stop ĐỂ KHÔNG BỊ CHUYỂN TRANG KHI BẤM NÚT NÀY -->
                      <button class="btn w-100 text-uppercase fw-bold ls-wider py-2 shadow-sm rounded-0 sora-add-cart-btn" 
                              style="font-size: 0.75rem;"
                              @click.stop="() => { /* Viết hàm addToCart vào đây */ }">
                        Thêm Vào Giỏ
                      </button>
                    </div>
                  </div>
                </div>
              </div>

            </template>
          </div>

          <!-- Không có sản phẩm -->
          <div v-if="allProducts.length === 0 && !isLoadingProducts" class="text-center py-5">
            <i class="fa-regular fa-folder-open fs-1 text-muted mb-3"></i>
            <p class="text-muted playfair-font fs-5">Không tìm thấy sản phẩm nào phù hợp với bộ lọc.</p>
            <button @click="resetFilters" class="btn btn-dark text-uppercase ls-widest mt-2 px-4 py-2 hover-bg-light transition-colors" style="background-color: var(--sora-primary); border: none;">Xóa Bộ Lọc</button>
          </div>

          <!-- PAGINATION -->
          <div v-if="pagination.last_page > 1" class="d-flex justify-content-center align-items-center gap-3 mt-5 pt-4 border-top border-light">
            <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="btn btn-outline-dark rounded-0 px-4 py-2 text-uppercase fw-bold ls-wider transition-colors hover-bg-dark hover-text-white" style="font-size: 0.75rem;">
              <i class="fa-solid fa-angle-left me-2"></i> Trước
            </button>
            <span class="fw-bold px-3 playfair-font fs-5 text-dark">{{ pagination.current_page }} <span class="text-muted mx-1">/</span> {{ pagination.last_page }}</span>
            <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="btn btn-outline-dark rounded-0 px-4 py-2 text-uppercase fw-bold ls-wider transition-colors hover-bg-dark hover-text-white" style="font-size: 0.75rem;">
              Sau <i class="fa-solid fa-angle-right ms-2"></i>
            </button>
          </div>

        </div>
      </div>
    </div>
  </div>
  
  <div v-else class="vh-100 w-100 d-flex flex-column align-items-center justify-content-center bg-white">
      <div class="spinner-border mb-4" style="color: var(--sora-primary); width: 3rem; height: 3rem; border-width: 0.15em;" role="status"></div>
      <h2 class="playfair-font fw-bold ls-widest text-uppercase fs-5 text-dark">Đang Tải Dữ Liệu...</h2>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useRoute, useRouter } from 'vue-router'; // ĐÃ THÊM useRouter

const route = useRoute();
const router = useRouter(); // ĐÃ KHỞI TẠO ROUTER
const shopSlug = ref(route.params.shop_slug || 'aurora-jewelry');
const API_BASE_URL = 'http://127.0.0.1:8000';

const isPageLoading = ref(true);
const isLoadingCategories = ref(true);
const isLoadingProducts = ref(true);
const categories = ref([]);
const allProducts = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });

const filters = reactive({
  sort: 'recommended',
  categories: [],
  collections: [],
  metals: []
});

// Sidebar Data (Dynamic)
const filterGroups = ref([
  {
    title: 'Danh Mục', key: 'categories', isOpen: true,
    options: [] 
  },
  {
    title: 'Bộ Sưu Tập', key: 'collections', isOpen: true,
    options: [
      { label: 'Solitaire Classic', value: 'solitaire' },
      { label: 'Halo Brilliance', value: 'halo' }
    ]
  }
]);

const toggleGroup = (index) => {
  filterGroups.value[index].isOpen = !filterGroups.value[index].isOpen;
};

const formatPrice = (price) => {
    if (!price || isNaN(price)) return 'Liên hệ';
    return new Intl.NumberFormat('vi-VN').format(price) + '₫';
}

const getImageUrl = (path) => {
  if (!path) return 'https://images.unsplash.com/photo-1605100804763-247f66126e28?q=80&w=600&auto=format&fit=crop';
  if (path.startsWith('http')) return path;
  return `${API_BASE_URL}/storage/${path}`;
};

// === KIỂM TRA ẢNH HOVER TỪ DATABASE ===
const hasHoverImage = (product) => {
  return product.hover_image && product.hover_image !== product.thumbnail_image;
};

// === CALL API DANH MỤC (REAL DATA) ===
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
    if (Array.isArray(filters[key]) && filters[key].length > 0) {
      params.append(key, filters[key].join(','));
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
  if (!filters.categories.includes(categorySlug)) {
    filters.categories.push(categorySlug);
    applyFilters();
  }
  window.scrollTo({ top: document.querySelector('.product-grid')?.offsetTop || 500, behavior: 'smooth' });
};

const applyFilters = () => { fetchProducts(1); };

const resetFilters = () => {
    filters.sort = 'recommended';
    filters.categories = [];
    filters.collections = [];
    filters.metals = [];
    applyFilters();
};

const changePage = (page) => {
    if(page >= 1 && page <= pagination.value.last_page) {
        fetchProducts(page);
        window.scrollTo({ top: document.querySelector('.category-section')?.offsetHeight || 0, behavior: 'smooth' });
    }
};

// === HÀM CHUYỂN TRANG CHI TIẾT SẢN PHẨM ===
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
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap');

/* --- HỆ THỐNG MÀU SẮC YÊU CẦU --- */
:root {
  --sora-primary: #9f273b;   /* Đỏ rượu chính */
  --sora-secondary: #e7ce7d; /* Vàng cát phụ */
  --sora-accent: #cc1e2e;    /* Đỏ tươi Sale/Alert */
}

.shop-page { font-family: 'Inter', sans-serif; color: #333; }
.playfair-font { font-family: 'Playfair Display', serif; }
.ls-widest { letter-spacing: 0.15em; }
.ls-wider { letter-spacing: 0.08em; }

.cursor-pointer { cursor: pointer; }
.transition-all { transition: all 0.4s ease; }
.transition-colors { transition: color 0.3s ease, background-color 0.3s ease, border-color 0.3s ease; }
.transition-transform { transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94); }
.duration-300 { transition-duration: 0.3s; }
.duration-400 { transition-duration: 0.4s; }
.duration-500 { transition-duration: 0.5s; }
.duration-700 { transition-duration: 0.7s; }

.rotate-180 { transform: rotate(180deg); }
.opacity-0 { opacity: 0; }
.pointer-events-none { pointer-events: none; }
.text-shadow-sm { text-shadow: 0 2px 10px rgba(0,0,0,0.5); }
.hover-text-dark:hover { color: #000 !important; }
.hover-text-gray:hover { color: #666 !important; }
.hover-bg-light:hover { background-color: #f8f9fa !important; color: #000 !important; border-color: #f8f9fa !important; }

/* Category Section */
.category-section { background-color: #fafafa; }
.overlay-gradient { background: linear-gradient(to top, rgba(0,0,0,0.65) 0%, rgba(0,0,0,0) 45%); transition: background 0.5s ease; }
.group:hover .overlay-gradient { background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 60%); }
.group:hover .group-hover-scale { transform: scale(1.08); }
.group-hover-translate-y { transform: translateY(10px); }
.group:hover .group-hover-translate-y { transform: translateY(0); }

/* Sidebar Filter */
.filter-title { color: #111; letter-spacing: 0.02em; }
.filter-options label { display: block; position: relative; padding-left: 32px; margin-bottom: 16px; cursor: pointer; font-size: 0.95rem; color: #444; user-select: none; font-weight: 400; }
.filter-options label:hover { color: var(--sora-primary); } 
.filter-options input { position: absolute; opacity: 0; cursor: pointer; height: 0; width: 0; }

.custom-checkbox .checkmark { position: absolute; top: 2px; left: 0; height: 18px; width: 18px; border: 1px solid #ccc; background-color: transparent; border-radius: 2px; }
.custom-checkbox:hover input ~ .checkmark { border-color: var(--sora-primary); }
.custom-checkbox input:checked ~ .checkmark { background-color: var(--sora-primary); border-color: var(--sora-primary); }
.custom-checkbox input:checked ~ .checkmark:after { content: ""; position: absolute; display: block; left: 6px; top: 2px; width: 5px; height: 10px; border: solid white; border-width: 0 2px 2px 0; transform: rotate(45deg); }

.custom-radio .checkmark { position: absolute; top: 3px; left: 0; height: 18px; width: 18px; border: 1px solid #ccc; border-radius: 50%; background-color: transparent; }
.custom-radio:hover input ~ .checkmark { border-color: var(--sora-primary); }
.custom-radio input:checked ~ .checkmark { border: 5px solid var(--sora-primary); }

/* Lưới Sản Phẩm */
.product-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2.5rem 1.5rem; }
.badge-custom { font-size: 0.65rem; font-weight: 600; letter-spacing: 0.1em; padding: 5px 12px; border-radius: 0; }
.editorial-block { grid-column: 1 / -1; min-height: 450px; }

/* === LOGIC HOVER HÌNH ẢNH MỚI (CHỐNG LỖI CSS TOÀN CỤC) === */
.sora-main-img, .sora-hover-img {
  transition: opacity 0.5s ease;
}
.sora-main-img {
  z-index: 1;
}
.sora-hover-img {
  z-index: 2;
  opacity: 0;
}
/* CHỈ LÀM MỜ ảnh chính NẾU wrapper báo hiệu có ảnh phụ (has-hover-image) */
.group:hover .sora-img-container.has-hover-image .sora-main-img {
  opacity: 0;
}
.group:hover .sora-img-container.has-hover-image .sora-hover-img {
  opacity: 1;
}

/* === NÚT THÊM VÀO GIỎ DƯỚI GIÁ TIỀN === */
.sora-add-cart-wrapper {
  max-height: 0;
  opacity: 0;
  margin-top: 0;
}
.group:hover .sora-add-cart-wrapper {
  max-height: 50px; 
  opacity: 1;
  margin-top: 10px; 
}
.sora-add-cart-btn { 
  background-color: transparent; 
  color: var(--sora-primary); 
  border: 1px solid var(--sora-primary); 
  transition: all 0.3s; 
}
.sora-add-cart-btn:hover { 
  background-color: var(--sora-primary) !important; 
  color: #fff !important; 
  border-color: var(--sora-primary) !important; 
}

.sora-btn-explore {
  border-color: var(--sora-primary);
  color: var(--sora-primary);
}
.sora-btn-explore:hover {
  background-color: var(--sora-primary) !important;
  color: #fff !important;
}

/* Responsive */
@media (max-width: 1200px) { .product-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px) { .product-grid { grid-template-columns: repeat(1, 1fr); gap: 1.5rem; } .editorial-block { flex-direction: column !important; min-height: auto; } .editorial-block > div { min-height: 300px; } }
</style>