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
                <div class="custom-filter-item d-flex align-items-center cursor-pointer mb-3" 
                     v-for="(option, optIdx) in group.options" :key="optIdx"
                     @click="toggleFilter(group.key, option.value)">
                  <div class="status-box me-3 d-flex align-items-center justify-content-center transition-all duration-300 shadow-sm"
                       :class="filters[group.key] === option.value ? 'bg-primary-custom border-primary-custom' : 'bg-white border-muted'">
                    <i class="fa-solid fa-check text-white" 
                       :class="filters[group.key] === option.value ? 'opacity-100' : 'opacity-0'" 
                       style="font-size: 0.65rem;"></i>
                  </div>
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
              
              <div class="luxury-related-card product-card bg-white d-flex flex-column group position-relative overflow-hidden border border-light-subtle h-100" @click="goToProductDetail(product.slug)">
                
                <div class="position-relative bg-light text-center border-bottom border-light-subtle sora-img-container" :class="{'has-hover-image': hasHoverImage(product)}">
                  <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-2 z-index-2 pointer-events-none text-start">
                    <span v-if="product.is_new" class="badge bg-white text-dark border border-light-subtle shadow-sm font-oswald tracking-widest px-2 py-1 rounded-0" style="font-size: 0.65rem;">MỚI</span>
                    <span v-if="product.promotional_price" class="badge text-white shadow-sm font-oswald tracking-widest px-2 py-1 rounded-0" style="background-color: var(--sora-accent); font-size: 0.65rem;">SALE</span>
                  </div>
                  
                  <div class="ratio ratio-1x1 w-100">
                    <img :src="getImageUrl(product.thumbnail_image)" 
                         :alt="product.name" 
                         class="sora-main-img object-fit-cover w-100 h-100 transition-transform duration-700 group-hover-scale bg-white" 
                         style="object-position: center;" 
                         @error="handleImageError">
                    <img v-if="hasHoverImage(product)"
                         :src="getImageUrl(product.hover_image)" 
                         :alt="product.name + ' hover'" 
                         class="sora-hover-img position-absolute top-0 start-0 w-100 h-100 object-fit-cover transition-transform duration-700 group-hover-scale" 
                         style="object-position: center;">
                  </div>
                  <div class="theme-bar position-absolute bottom-0 start-0 z-index-2"></div>
                </div>
                
                <div class="position-relative flex-grow-1 bg-white d-flex flex-column">
                  <div class="p-4 text-center d-flex flex-column flex-grow-1" style="padding-bottom: 64px !important;">
                    <h6 class="text-dark font-oswald text-uppercase tracking-widest fw-bold mb-2 text-truncate-2 fs-5 lh-base">{{ product.name }}</h6>
                    <p class="font-serif fst-italic text-muted fs-6 mb-3">{{ product.category?.name || 'Trang sức SORA' }}</p>
                    
                    <div class="mt-auto">
                      <div class="d-flex align-items-center justify-content-center gap-3">
                        <p v-if="product.promotional_price" class="text-muted text-decoration-line-through mb-0 fw-light font-serif" style="font-size: 0.95rem;">
                          {{ formatPrice(product.base_price) }}
                        </p>
                        <span class="text-sora-primary fw-bold font-serif fs-5">{{ formatPrice(product.promotional_price || product.base_price) }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- NÚT THÊM VÀO GIỎ -->
                  <div class="related-btn-add">
                    <button class="btn luxury-btn-solid w-100 rounded-0 py-3 font-oswald tracking-widest text-uppercase fw-bold shadow-none fs-6" 
                            @click.stop="openQuickAdd(product)">
                      Thêm vào giỏ
                    </button>
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
    
    <!-- ========================================== -->
    <!-- MODAL QUICK ADD (CHỐNG VỠ BỐ CỤC TUYỆT ĐỐI) -->
    <!-- LƯU Ý: style z-index: 9999 để đè lấp triệt để nút đỏ ở sau -->
    <!-- ========================================== -->
    <div v-if="quickAddModal.isOpen" class="modal-overlay position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" @click.self="closeQuickAdd" style="z-index: 9999 !important; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(2px);">
      <div class="bg-white rounded shadow-lg d-flex flex-column position-relative" style="width: 90%; max-width: 480px; max-height: 90vh; overflow: hidden; animation: slideUp 0.3s ease-out;">
        
        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center px-4 py-3" style="background-color: #9f273b;">
          <h5 class="mb-0 text-white fw-bold font-serif fs-5" style="letter-spacing: 0.5px;">Tùy chọn Sản phẩm</h5>
          <button @click="closeQuickAdd" class="btn text-white p-0 m-0 border-0" style="opacity: 0.8;">
            <i class="fa-solid fa-xmark fs-4"></i>
          </button>
        </div>

        <!-- BODY -->
        <div class="p-4 overflow-y-auto" style="flex-grow: 1;">
          
          <!-- Thông tin Sản phẩm Tóm tắt -->
          <div class="d-flex gap-3 mb-4 pb-4 border-bottom">
            <div class="flex-shrink-0 border rounded" style="width: 90px; height: 90px; overflow: hidden; border-color: #eaeaea;">
               <img :src="getImageUrl(currentVariant?.image_url || quickAddModal.product.thumbnail_image)" class="w-100 h-100 object-fit-cover bg-light" @error="handleImageError">
            </div>
            <div class="d-flex flex-column justify-content-center">
              <span class="text-uppercase fw-bold mb-1" style="font-size: 0.7rem; color: #e7ce7d; letter-spacing: 2px;">{{ quickAddModal.product.category?.name || 'SẢN PHẨM' }}</span>
              <h6 class="fs-5 mb-2 fw-bold text-dark font-serif">{{ quickAddModal.product.name }}</h6>
              <span class="fw-bold fs-5" style="color: #9f273b; font-family: 'Playfair Display', serif;">{{ displayPriceFormatted }}</span>
            </div>
          </div>

          <!-- Lặp qua các Thuộc tính (Chất liệu, Kích thước...) -->
          <div v-for="(options, attrName) in quickAddModal.attributes" :key="attrName" class="mb-4">
            <label class="d-block text-uppercase fw-bold mb-2 text-dark font-oswald" style="font-size: 0.85rem; letter-spacing: 1px;">
              {{ attrName }}:
            </label>
            <div class="d-flex flex-wrap gap-2">
              <button 
                v-for="opt in options" :key="opt"
                @click="quickAddModal.selectedOptions[attrName] = opt"
                class="btn variant-select-btn px-4 py-2 fw-medium"
                :class="{'selected': quickAddModal.selectedOptions[attrName] === opt}"
              >
                {{ opt }}
              </button>
            </div>
          </div>

          <!-- NÚT CỘNG TRỪ SỐ LƯỢNG (Sử dụng chuẩn Bootstrap Input Group) -->
          <div class="mb-4">
            <label class="d-block text-uppercase fw-bold mb-2 text-dark font-oswald" style="font-size: 0.85rem; letter-spacing: 1px;">
              SỐ LƯỢNG:
            </label>
            <div class="d-flex align-items-center gap-3">
               
               <div class="input-group" style="width: 140px;">
                 <button @click="updateQuickAddQty(-1)" class="btn btn-outline-secondary" type="button">
                   <i class="fa-solid fa-minus">-</i>
                 </button>
                 <input type="number" v-model.number="quickAddModal.quantity" @change="validateQuickAddQty" class="form-control text-center fw-bold text-dark" style="-moz-appearance: textfield;">
                 <button @click="updateQuickAddQty(1)" class="btn btn-outline-secondary" type="button">
                   <i class="fa-solid fa-plus">+</i>
                 </button>
               </div>
               
               <!-- Tồn kho thực tế -->
               <span v-if="isAllAttributesSelected && currentVariant" class="text-muted small fw-medium">
                 {{ currentVariant.stock_quantity > 0 ? `Còn ${currentVariant.stock_quantity} sản phẩm` : 'Hết hàng' }}
               </span>
               <span v-else class="text-muted small fw-medium fst-italic">
                 Vui lòng chọn phân loại
               </span>
            </div>
          </div>

          <!-- Cảnh báo lỗi / hết hàng / chưa chọn đủ phân loại -->
          <div v-if="!isAllAttributesSelected && Object.keys(quickAddModal.attributes).length > 0" class="alert alert-info py-2 small mb-0">
            <i class="fa-solid fa-circle-info me-1"></i> Vui lòng chọn đầy đủ phân loại sản phẩm.
          </div>
          <div v-else-if="!currentVariant && Object.keys(quickAddModal.attributes).length > 0" class="alert alert-warning py-2 small mb-0">
            <i class="fa-solid fa-triangle-exclamation me-1"></i> Phân loại này tạm thời không khả dụng.
          </div>
          <div v-else-if="currentVariant && currentVariant.stock_quantity <= 0" class="alert alert-danger py-2 small mb-0">
            <i class="fa-solid fa-ban me-1"></i> Sản phẩm này đã hết hàng trong kho.
          </div>
        </div>

        <!-- FOOTER: Nút Xác Nhận Nằm Ngang -->
        <div class="p-3 bg-light border-top">
          <button 
            @click="confirmAddToCart" 
            :disabled="quickAddModal.isAdding || !isAllAttributesSelected || !currentVariant || currentVariant.stock_quantity <= 0"
            class="btn w-100 py-3 text-uppercase fw-bold text-white d-flex justify-content-center align-items-center gap-2"
            style="background-color: #9f273b; border-radius: 4px; font-family: 'Oswald', sans-serif; letter-spacing: 1px; border: none; font-size: 0.95rem;"
          >
            <span v-if="quickAddModal.isAdding" class="spinner-border spinner-border-sm" role="status"></span>
            <template v-else>
              <i class="fa-solid fa-cart-plus me-1"></i> XÁC NHẬN THÊM
            </template>
          </button>
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
import { ref, onMounted, reactive, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Swal from 'sweetalert2';

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

const soraAlert = Swal.mixin({
  buttonsStyling: true,
  confirmButtonColor: '#9f273b',
  customClass: {
    confirmButton: 'px-4 py-2 mx-2 rounded-0 shadow-sm fw-bold font-oswald tracking-widest text-uppercase'
  }
});

const filters = reactive({ sort: 'recommended', categories: '', collections: '', metals: '' });
const filterGroups = ref([
  { title: 'Danh Mục', key: 'categories', isOpen: true, options: [] },
  { title: 'Bộ Sưu Tập', key: 'collections', isOpen: true, options: [
      { label: 'Solitaire Classic', value: 'solitaire' },
      { label: 'Halo Brilliance', value: 'halo' }
    ]
  }
]);

const toggleGroup = (index) => filterGroups.value[index].isOpen = !filterGroups.value[index].isOpen;
const toggleFilter = (key, value) => { filters[key] = filters[key] === value ? '' : value; applyFilters(); };

const formatPrice = (price) => {
    if (!price || isNaN(price)) return 'Liên Hệ';
    return new Intl.NumberFormat('vi-VN').format(price) + ' đ';
}
const getImageUrl = (path) => path ? (path.startsWith('http') ? path : `${API_BASE_URL}/storage/${path}`) : 'https://placehold.co/600x600';
const handleImageError = (e) => e.target.src = 'https://placehold.co/400x400';
const hasHoverImage = (product) => product.hover_image && product.hover_image !== product.thumbnail_image;

/* =========================================================
   LOGIC BÓC TÁCH BIẾN THỂ VÀ CHỜ NGƯỜI DÙNG CHỌN
   ========================================================= */
const quickAddModal = reactive({
  isOpen: false,
  product: null,
  attributes: {}, // { 'Kích thước': ['15', '16'], 'Màu sắc': ['Vàng', 'Bạc'] }
  selectedOptions: {}, // Rỗng lúc đầu: {}
  quantity: 1,
  isAdding: false
});

const openQuickAdd = (productSummary) => {
  quickAddModal.product = productSummary;
  quickAddModal.attributes = {};
  quickAddModal.selectedOptions = {}; // Reset rỗng (Mặc định chưa chọn gì)
  quickAddModal.quantity = 1;
  quickAddModal.isAdding = false;
  
  const attrs = {};
  
  if (productSummary.variants && productSummary.variants.length > 0) {
    productSummary.variants.forEach(variant => {
      if (variant.attribute_values && Array.isArray(variant.attribute_values)) {
        variant.attribute_values.forEach(av => {
          if (av.attribute && av.attribute.name) {
            const attrName = av.attribute.name;
            const val = av.value;
            if (!attrs[attrName]) attrs[attrName] = new Set();
            attrs[attrName].add(val);
          }
        });
      }
    });
  }
  
  // Chuyển Set sang Array (Không tự động chọn Option 1 nữa)
  for (const key in attrs) {
    quickAddModal.attributes[key] = Array.from(attrs[key]);
  }
  
  // KHÓA THANH CUỘN BACKGROUND KHI MỞ MODAL ĐỂ TRÁNH LỖI
  document.body.style.overflow = 'hidden';
  quickAddModal.isOpen = true;
};

const closeQuickAdd = () => {
  document.body.style.overflow = 'auto'; // Trả lại thanh cuộn
  quickAddModal.isOpen = false;
  quickAddModal.product = null;
};

// Kiểm tra xem đã chọn full các tuỳ chọn chưa
const isAllAttributesSelected = computed(() => {
  if (!quickAddModal.attributes) return true;
  const requiredAttrs = Object.keys(quickAddModal.attributes);
  if (requiredAttrs.length === 0) return true;

  // Trả về true nếu MỌI key đều có giá trị trong selectedOptions
  return requiredAttrs.every(attr => quickAddModal.selectedOptions[attr] !== undefined && quickAddModal.selectedOptions[attr] !== null);
});

// Chỉ lấy Variant ID khi đã được chọn đầy đủ
const currentVariant = computed(() => {
  if (!quickAddModal.product || !quickAddModal.product.variants || quickAddModal.product.variants.length === 0) return null;
  
  // Sản phẩm đơn giản không có phân loại
  if (Object.keys(quickAddModal.attributes).length === 0) {
    return quickAddModal.product.variants[0];
  }

  // NẾU CHƯA CHỌN ĐỦ -> TRẢ VỀ NULL ĐỂ KHÔNG HIỂN THỊ
  if (!isAllAttributesSelected.value) return null;

  return quickAddModal.product.variants.find(v => {
    if (!v.attribute_values || !Array.isArray(v.attribute_values)) return false;

    // Phải khớp tất cả các lựa chọn hiện tại
    for (const [attrName, selectedVal] of Object.entries(quickAddModal.selectedOptions)) {
      const match = v.attribute_values.find(av => av.attribute && av.attribute.name === attrName && String(av.value) === String(selectedVal));
      if (!match) return false;
    }
    return true;
  });
});

// Logic tính giá: Hiển thị giá cụ thể nếu đã chọn, hoặc KHOẢNG GIÁ nếu chưa chọn
const displayPriceFormatted = computed(() => {
  // 1. Nếu đã chọn phân loại đầy đủ
  if (currentVariant.value) {
    return formatPrice(currentVariant.value.promotional_price || currentVariant.value.price);
  }
  
  // 2. Nếu chưa chọn phân loại, quét hiển thị khoảng giá (Min - Max)
  if (quickAddModal.product) {
    if (quickAddModal.product.variants && quickAddModal.product.variants.length > 0) {
      const prices = quickAddModal.product.variants.map(v => parseFloat(v.promotional_price || v.price));
      const min = Math.min(...prices);
      const max = Math.max(...prices);
      
      if (min !== max && !isNaN(min) && !isNaN(max)) {
        return `${formatPrice(min)} - ${formatPrice(max)}`;
      }
      return formatPrice(min);
    }
    // Backup nếu không có variant
    return formatPrice(quickAddModal.product.promotional_price || quickAddModal.product.base_price);
  }
  return formatPrice(0);
});


// KIỂM SOÁT NHẬP XUẤT SỐ LƯỢNG (TỒN KHO)
const updateQuickAddQty = (delta) => {
  if (!isAllAttributesSelected.value) {
    soraAlert.fire({ icon: 'info', title: 'Chưa đủ thông tin', text: 'Vui lòng chọn đầy đủ phân loại sản phẩm trước khi thao tác số lượng.'});
    return;
  }
  
  const maxStock = currentVariant.value ? currentVariant.value.stock_quantity : 0;
  let newQty = quickAddModal.quantity + delta;
  
  if (newQty < 1) newQty = 1;
  if (newQty > maxStock) {
    soraAlert.fire({ icon: 'warning', title: 'Kho không đủ', text: `Sản phẩm này chỉ còn tối đa ${maxStock} chiếc.`});
    newQty = maxStock;
  }
  
  quickAddModal.quantity = newQty;
};

const validateQuickAddQty = () => {
  if (!isAllAttributesSelected.value) {
    quickAddModal.quantity = 1;
    soraAlert.fire({ icon: 'info', title: 'Chưa đủ thông tin', text: 'Vui lòng chọn đầy đủ phân loại sản phẩm trước khi thao tác số lượng.'});
    return;
  }
  
  const maxStock = currentVariant.value ? currentVariant.value.stock_quantity : 0;
  let qty = parseInt(quickAddModal.quantity);
  
  if (isNaN(qty) || qty < 1) quickAddModal.quantity = 1;
  else if (qty > maxStock) {
    soraAlert.fire({ icon: 'warning', title: 'Kho không đủ', text: `Sản phẩm này chỉ còn tối đa ${maxStock} chiếc.`});
    quickAddModal.quantity = maxStock;
  }
};

const confirmAddToCart = async () => {
  if (!isAllAttributesSelected.value || !currentVariant.value) {
    return soraAlert.fire({ icon: 'warning', title: 'Thiếu thông tin', text: 'Vui lòng chọn đầy đủ phân loại.' });
  }
  
  const maxStock = currentVariant.value.stock_quantity || 0;
  if (quickAddModal.quantity > maxStock) {
    return soraAlert.fire({ icon: 'error', title: 'Quá giới hạn', text: `Vượt quá tồn kho khả dụng (${maxStock} chiếc).` });
  }
  
  quickAddModal.isAdding = true;
  
  try {
    const token = localStorage.getItem('auth_token');
    let sessionId = localStorage.getItem('cart_session_id') || 'session_' + Math.random().toString(36).substr(2, 9);
    localStorage.setItem('cart_session_id', sessionId);

    const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Cart-Session-Id': sessionId };
    if (token) headers['Authorization'] = `Bearer ${token}`;

    const response = await fetch(`${API_BASE_URL}/api/client/cart`, {
      method: 'POST', headers: headers,
      body: JSON.stringify({ product_variant_id: currentVariant.value.id, quantity: quickAddModal.quantity })
    });

    const data = await response.json();
    
    if (data.success) {
      closeQuickAdd(); 
      router.push('/cart'); 
    } else {
      soraAlert.fire({ icon: 'error', title: 'Không thể thêm', text: data.message || "Đã có lỗi xảy ra." });
    }
  } catch (error) {
    soraAlert.fire({ icon: 'error', title: 'Lỗi', text: 'Lỗi kết nối tới máy chủ.' });
  } finally {
    quickAddModal.isAdding = false;
  }
};
/* ========================================================= */

const fetchCategories = async () => {
    isLoadingCategories.value = true;
    try {
        const response = await fetch(`${API_BASE_URL}/api/shop/${shopSlug.value}/categories`);
        const data = await response.json();
        if(data && data.success) {
            categories.value = data.data;
            const idx = filterGroups.value.findIndex(g => g.key === 'categories');
            if (idx !== -1) filterGroups.value[idx].options = data.data.map(cat => ({ label: cat.name, value: cat.slug }));
        }
    } catch (e) { } finally { isLoadingCategories.value = false; }
};

const fetchProducts = async (page = 1) => {
    isLoadingProducts.value = true;
    try {
        const params = new URLSearchParams({ page: page, sort: filters.sort });
        Object.keys(filters).forEach(k => { if(k !== 'sort' && filters[k]) params.append(k, filters[k]); });
        
        const response = await fetch(`${API_BASE_URL}/api/shop/${shopSlug.value}/products?${params.toString()}`);
        const data = await response.json();
        
        if(data && data.success) {
            allProducts.value = data.data.data;
            pagination.value = { current_page: data.data.current_page, last_page: data.data.last_page, total: data.data.total };
        } else allProducts.value = [];
    } catch (e) { allProducts.value = []; } finally { isLoadingProducts.value = false; }
};

const filterByCategory = (categorySlug) => {
  filters.categories = categorySlug; filters.collections = ''; applyFilters();
  window.scrollTo({ top: document.querySelector('.product-grid')?.offsetTop || 500, behavior: 'smooth' });
};
const applyFilters = () => fetchProducts(1);
const resetFilters = () => { Object.keys(filters).forEach(k => filters[k] = k === 'sort' ? 'recommended' : ''); applyFilters(); };
const changePage = (page) => { if(page >= 1 && page <= pagination.value.last_page) { fetchProducts(page); window.scrollTo({ top: document.querySelector('.category-section')?.offsetHeight || 0, behavior: 'smooth' }); } };
const goToProductDetail = (slug) => { if (slug) router.push({ name: 'productDetail', params: { shop_slug: shopSlug.value, slug: slug } }); };

onMounted(() => { Promise.all([fetchCategories(), fetchProducts(1)]).then(() => isPageLoading.value = false); });
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&family=Oswald:wght@400;500;600;700&display=swap');

:root {
  --sora-primary: #9f273b;
  --sora-secondary: #e7ce7d;
  --sora-accent: #cc1e2e;
  --sora-text: #2c2c2c;
  --sora-border: #eaeaea;
}

.shop-page { font-family: 'Inter', sans-serif; color: var(--sora-text); }
.playfair-font { font-family: 'Playfair Display', serif; }
.font-oswald { font-family: 'Oswald', sans-serif; }
.font-serif { font-family: 'Playfair Display', serif; }
.ls-widest { letter-spacing: 0.15em; }
.ls-wider { letter-spacing: 0.08em; }
.tracking-widest { letter-spacing: 2px; }
.fw-light { font-weight: 300; }
.fw-medium { font-weight: 500; }

.z-index-2 { z-index: 2; }
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

.sora-border-light { border-color: var(--sora-border) !important; }
.hover-text-dark:hover { color: #000 !important; }
.hover-text-gold:hover { color: var(--sora-secondary) !important; }
.hover-text-primary:hover { color: var(--sora-primary) !important; }
.divider-gold { width: 40px; height: 1px; background-color: var(--sora-secondary); margin: 0 auto; }

.sora-btn-primary { background-color: var(--sora-primary); color: #fff; border: 1px solid var(--sora-primary); }
.sora-btn-primary:hover { background-color: #831f30; border-color: #831f30; color: #fff; }
.sora-btn-outline { background-color: transparent; color: var(--sora-primary); border: 1px solid var(--sora-primary); }
.sora-btn-outline:hover { background-color: var(--sora-primary); color: var(--sora-secondary); }
.sora-link-explore:hover { border-bottom-color: var(--sora-secondary) !important; }

.overlay-gradient { background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0) 50%); transition: background 0.5s ease; }
.group:hover .overlay-gradient { background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.1) 100%); }
.group:hover .group-hover-scale { transform: scale(1.05); }
.group-hover-translate-y { transform: translateY(15px); }
.group:hover .group-hover-translate-y { transform: translateY(0); }
.inner-frame { top: 15px; left: 15px; right: 15px; bottom: 15px; border: 1px solid rgba(231, 206, 125, 0); z-index: 3; }
.group:hover .inner-frame { border-color: rgba(231, 206, 125, 0.6); transform: scale(0.96); }

.custom-radio { position: relative; display: flex; align-items: center; cursor: pointer; font-size: 0.9rem; color: #555; transition: color 0.3s; }
.custom-radio input { position: absolute; opacity: 0; cursor: pointer; height: 0; width: 0; }
.custom-radio .checkmark { position: relative; display: inline-block; width: 16px; height: 16px; border: 1px solid #b0b0b0; border-radius: 50%; margin-right: 12px; flex-shrink: 0; transition: all 0.3s; }
.custom-radio:hover .checkmark { border-color: var(--sora-secondary); }
.custom-radio:hover .label-text { color: var(--sora-primary); }
.custom-radio input:checked ~ .checkmark { border-color: var(--sora-primary); border-width: 4px; }
.custom-radio input:checked ~ .label-text { color: var(--sora-primary); font-weight: 500; }

.filter-options { display: flex; flex-direction: column; gap: 4px; }
.bg-primary-custom { background-color: var(--sora-primary) !important; }
.border-primary-custom { border: 1px solid var(--sora-primary) !important; }
.text-primary-custom { color: var(--sora-primary) !important; }
.border-muted { border: 1px solid #ced4da !important; }
.status-box { width: 18px; height: 18px; border-radius: 4px; }
.custom-filter-item:hover .status-box:not(.bg-primary-custom) { border-color: var(--sora-secondary) !important; }
.custom-filter-item:hover .label-text:not(.fw-bold) { color: var(--sora-primary) !important; }

.product-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 3rem 1.5rem; }

.luxury-related-card { transition: all 0.4s ease; border-color: var(--sora-border) !important; }
.luxury-related-card:hover { box-shadow: 0 15px 35px rgba(0,0,0,0.06); border-color: #d1d5db !important; }
.text-truncate-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

.sora-main-img, .sora-hover-img { transition: transform 0.8s cubic-bezier(0.165, 0.84, 0.44, 1), opacity 0.6s ease; }
.sora-main-img { z-index: 1; }
.sora-hover-img { z-index: 2; opacity: 0; }
.group:hover .sora-img-container.has-hover-image .sora-main-img { opacity: 0; }
.group:hover .sora-img-container.has-hover-image .sora-hover-img { opacity: 1; }
.group:hover .sora-main-img, .group:hover .sora-hover-img { transform: scale(1.08); }

.theme-bar { width: 30%; height: 3px; background-color: var(--sora-primary); opacity: 0; transition: opacity 0.3s ease; left: 0; }
.luxury-related-card:hover .theme-bar { opacity: 1; }

.related-btn-add {
    position: absolute; bottom: 0; left: 0; width: 100%;
    transform: translateY(101%); transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    z-index: 10; background: rgb(112, 21, 21);
}
.group:hover .related-btn-add, .product-card:hover .related-btn-add, .luxury-related-card:hover .related-btn-add { transform: translateY(0); }

.luxury-btn-solid { background-color: var(--sora-primary); color: white; border: 1px solid var(--sora-primary); transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); }
.luxury-btn-solid:hover:not(:disabled) { background-color: #7a1c2d; border-color: #7a1c2d; color: white; box-shadow: 0 8px 20px rgba(159,39,59,0.3); transform: translateY(-2px); }





.variant-select-btn:hover { border-color: #9f273b; color: #9f273b; }
.variant-select-btn.selected {
  border-color: #9f273b;
  color: #9f273b;
  font-weight: 700;
  background-color: #fdf5f6;
  box-shadow: inset 0 0 0 1px #9f273b; 
}

/* Animation mượt cho Modal */
@keyframes slideUp {
  from { transform: translateY(30px) scale(0.98); opacity: 0; }
  to { transform: translateY(0) scale(1); opacity: 1; }
}

@media (max-width: 1200px) { .product-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px) { .product-grid { grid-template-columns: repeat(1, 1fr); gap: 2rem; } .editorial-block { flex-direction: column !important; } .editorial-block > div { min-height: 350px; } }
</style>