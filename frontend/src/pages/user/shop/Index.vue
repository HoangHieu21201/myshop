<template>
  <div class="shop-page min-vh-100 bg-white">
    
    <!-- HEADER BREADCRUMB -->
    <div class="container-fluid px-4 py-3 border-bottom sora-border-light bg-light">
      <div class="d-flex align-items-center text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.15em;">
      <a href=""> <span class="text-muted cursor-pointer hover-text-primary transition-colors">Trang chủ</span> </a>
       
        <span class="mx-2 text-muted">/</span>
        <span class="fw-medium text-dark">Cửa hàng trang sức</span>
      </div>
    </div>

    <!-- LỰA CHỌN LÝ TƯỞNG (DANH MỤC) -->
   <section class="ideal-choices-section py-2 border-bottom sora-border-light" style="background-color: rgb(159,39,59);">
      <div class="container-fluid px-3 py-1 py-md-2">
        
        <!-- Header -->
        <div class="d-flex flex-column align-items-center text-center mb-2">
          <h2 class="text-white fw-bold mb-1 font-serif" style="font-size: clamp(1.4rem, 2.5vw, 1.8rem); letter-spacing: 0.02em;">Lựa chọn lý tưởng</h2>
          <div class="d-flex align-items-center justify-content-center mb-3">
            <svg width="120" height="15" viewBox="0 0 150 20" xmlns="http://www.w3.org/2000/svg" style="opacity: 0.8;">
              <!-- Ornamental divider -->
              <path d="M10 10h40m50 0h40M65 10c0-3 4-5 10-5s10 2 10 5-4 5-10 5-10-2-10-5z" stroke="white" stroke-width="1.5" fill="none"/>
            </svg>
          </div>
        </div>

        <!-- Skeleton/Spinner cho Categories -->
        <div v-if="isLoadingCategories" class="d-flex justify-content-center py-4">
          <div class="spinner-border text-white" style="width: 2rem; height: 2rem; border-width: 0.1em;" role="status"></div>
        </div>

        <!-- 5 Danh Mục Tròn (Co giãn Responsive - Đã thu nhỏ gọn hơn) -->
        <div v-else class="row justify-content-center row-cols-2 row-cols-sm-3 row-cols-md-5 g-2 g-md-3 mb-3 pb-2 mx-auto" style="max-width: 900px;">
          <div class="col" v-for="cat in categories.slice(0, 5)" :key="cat.id">
            <div class="category-circle-item text-center cursor-pointer group d-flex flex-column align-items-center" @click="filterByCategory(cat.slug)">
              <!-- Cố định kích thước ảnh tròn nhỏ lại khoảng 85px -->
              <div class="circle-img-wrapper rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center mb-2 transition-transform duration-400 group-hover-scale" style="width: 85px; height: 85px; padding: 2px;">
                <img :src="getImageUrl(cat.thumbnail)" loading="lazy" :alt="cat.name" @error="handleImageError" class="w-100 h-100 object-fit-contain rounded-circle transition-transform duration-500 group-hover-scale-img">
              </div>
              <h3 class="text-white fw-medium mb-0 tracking-wider text-truncate w-100 pb-1" style="font-size: 0.85rem;">
                <span class="category-name position-relative">{{ cat.name }}</span>
              </h3>
            </div>
          </div>
        </div>

        <!-- Banners Co giãn (Luôn hiển thị ít nhất 2 sản phẩm) -->
        <div class="row justify-content-center g-2 g-md-3 mx-auto" style="max-width: 1000px;" v-if="promoProducts.length > 0">
          <div class="col-12 col-md-6" v-for="(prod, idx) in promoProducts" :key="'promo-' + prod.id">
            <div class="promo-banner bg-white p-2 p-md-3 d-flex align-items-center justify-content-between h-100 cursor-pointer shadow-sm transition-all duration-300 hover-translate-y" style="border-radius: 4px;" @click="goToProductDetail(prod.slug)">
              <div class="promo-content pe-2">
                <p class="text-uppercase text-muted mb-1 font-oswald" style="font-size: 0.65rem; letter-spacing: 1px;">
                  {{ prod.category?.name || 'BỘ SƯU TẬP' }}
                </p>
                <h4 class="text-dark playfair-font fw-bold mb-1" style="font-size: clamp(0.9rem, 1.5vw, 1.1rem); line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                  {{ prod.name }}
                </h4>
                
                <!-- Tính toán phần trăm giảm giá tự động -->
                <p class="text-dark mb-1 mb-md-2 fw-medium font-oswald" style="font-size: 0.8rem;" v-if="prod.promotional_price && prod.base_price > prod.promotional_price">
                  Giảm {{ Math.round(((prod.base_price - prod.promotional_price) / prod.base_price) * 100) }}%
                </p>
                <p class="text-dark mb-1 mb-md-2 fw-medium font-oswald" style="font-size: 0.8rem;" v-else>
                  Mới Ra Mắt
                </p>

                <span class="text-uppercase border-bottom border-dark pb-0 text-dark d-inline-block font-oswald" style="font-size: 0.7rem; font-weight: 600; letter-spacing: 1px;">Mua ngay</span>
              </div>
              <div class="promo-img-wrapper flex-shrink-0" style="width: clamp(70px, 15vw, 100px); aspect-ratio: 1/1;">
                <img :src="getImageUrl(prod.thumbnail_image)" loading="lazy" :alt="prod.name" class="w-100 h-100 object-fit-contain" @error="handleImageError">
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- MAIN CONTENT: BỘ LỌC VÀ LƯỚI SẢN PHẨM -->
    <div class="container-fluid px-4 py-4 mt-2 mx-auto" style="max-width: 1440px;">
      <div class="row">
        
        <!-- SIDEBAR BỘ LỌC (LEFT) -->
        <div class="col-lg-2 col-md-3 d-none d-md-block sidebar-filter pe-4 border-end sora-border-light pt-2">
          
          <div class="filter-widget mb-5">
            <h5 class="filter-title playfair-font mb-4 fs-5 fw-normal text-dark border-bottom pb-3">Danh mục</h5>
            <ul class="list-unstyled mb-0 filter-list">
              <li v-for="cat in categories" :key="cat.id" class="mb-3">
                <div class="custom-checkbox d-flex align-items-center cursor-pointer" @click="filterByCategory(cat.slug)">
                  <div class="checkmark transition-all duration-300 shadow-sm" :class="{'checked': filters.categories === cat.slug}"></div>
                  <span class="label-text ms-3 transition-colors" :class="filters.categories === cat.slug ? 'text-primary-custom fw-bold' : 'text-muted'">{{ cat.name }}</span>
                </div>
              </li>
            </ul>
          </div>

          <div class="filter-widget mb-5" v-for="(group, index) in filterGroups" :key="index">
            <template v-if="group.key !== 'categories'">
              <h5 class="filter-title playfair-font mb-4 fs-5 fw-normal text-dark border-bottom pb-3">{{ group.title }}</h5>
              <ul class="list-unstyled mb-0 filter-list">
                <li v-for="(option, optIdx) in group.options" :key="optIdx" class="mb-3">
                  <div class="custom-checkbox d-flex align-items-center cursor-pointer" @click="toggleFilter(group.key, option.value)">
                    <div class="checkmark transition-all duration-300 shadow-sm" :class="{'checked': filters[group.key] === option.value}"></div>
                    <span class="label-text ms-3 transition-colors" :class="filters[group.key] === option.value ? 'text-primary-custom fw-bold' : 'text-muted'">{{ option.label }}</span>
                  </div>
                </li>
              </ul>
            </template>
          </div>
        </div>

        <!-- MAIN PRODUCT GRID (RIGHT) -->
        <div class="col-lg-10 col-md-9 ps-lg-5">
          
          <div class="shop-top-bar d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom sora-border-light">
            <div class="result-count text-muted mb-3 mb-md-0" style="font-size: 1.2rem;">
              <span v-if="!isLoadingProducts">Hiển thị 1–{{ allProducts.length }} của {{ pagination.total }} kết quả</span>
              <span v-else>Đang tải dữ liệu...</span>
            </div>
            
            <div class="d-flex align-items-center gap-4">
              <div class="sort-dropdown d-flex align-items-center gap-2">
                <span class="text-dark fw-medium" style="font-size: 1.25rem;">Sắp xếp:</span>                
                <select v-model="filters.sort" @change="applyFilters" class="form-select border-0 shadow-none cursor-pointer text-muted px-1" style="width: auto; background-color: transparent; font-size: 0.95rem;">
                  <option value="recommended">Mặc định</option>
                  <option value="new">Mới nhất</option>
                  <option value="price_asc">Giá: Thấp đến Cao</option>
                  <option value="price_desc">Giá: Cao đến Thấp</option>
                </select>
              </div>
            </div>
          </div>

          <!-- LƯỚI SẢN PHẨM -->
          <div v-if="isLoadingProducts" class="d-flex justify-content-center align-items-center" style="min-height: 400px;">
            <div class="spinner-border" style="color: var(--sora-primary); width: 2.5rem; height: 2.5rem; border-width: 0.1em;" role="status"></div>
          </div>

          <div v-else class="product-grid">
            <template v-for="product in allProducts" :key="product.id">
              <div class="sora-luxury-card" @click="goToProductDetail(product.slug)">
                  <div class="sora-card-image sora-img-container" :class="{'has-hover-image': hasHoverImage(product)}">
                      <div class="sora-card-badges">
                          <span v-if="product.is_new" class="sora-badge">MỚI</span>
                          <span v-if="product.promotional_price" class="sora-badge sale-badge">SALE</span>
                      </div>

                      <button class="sora-wishlist-btn" :class="{ 'active': isFavourited(product.id) }" @click.stop="toggleFavourite(product)" :disabled="isTogglingFav === product.id">
                          <span v-if="isTogglingFav === product.id" class="spinner-border spinner-border-sm text-danger" style="width: 1rem; height: 1rem;"></span>
                          <i v-else :class="isFavourited(product.id) ? 'bi bi-suit-heart-fill text-danger' : 'bi bi-suit-heart'"></i>
                      </button>

                      <img :src="getImageUrl(product.thumbnail_image)" loading="lazy" :alt="product.name" class="sora-main-img" @error="handleImageError">
                      
                      <img v-if="hasHoverImage(product)"
                           :src="getImageUrl(product.hover_image)" 
                           loading="lazy"
                           :alt="product.name + ' hover'" 
                           class="sora-hover-img" 
                           @error="handleImageError">
                  </div>

                  <div class="sora-card-info">
                      <h3 class="sora-card-title" :title="product.name">{{ product.name }}</h3>
                      <p class="sora-card-category">{{ product.category?.name || 'Trang sức SORA' }}</p>
                      
                      <!-- SỬA LẠI HIỂN THỊ GIÁ VÀ % GIẢM GIÁ Ở ĐÂY -->
                      <div class="sora-card-price d-flex align-items-center justify-content-center flex-wrap gap-1">
                          <span v-if="product.promotional_price" class="sora-card-price-old">
                            {{ formatPrice(product.base_price) }}
                          </span>
                          <span>{{ formatPrice(product.promotional_price || product.base_price) }}</span>
                          
                          <!-- Tag % Giảm giá -->
                          <span v-if="product.promotional_price && product.base_price && product.base_price > product.promotional_price" 
                                class="sora-discount-tag">
                              -{{ Math.round(((product.base_price - product.promotional_price) / product.base_price) * 100) }}%
                          </span>
                      </div>
                  </div>

                  <div class="sora-card-action">
                      <button class="sora-action-btn" @click.stop="openQuickAdd(product)">
                          <i class="bi bi-eye"></i> THÊM VÀO GIỎ
                      </button>
                  </div>
              </div>
            </template>
          </div>

          <!-- Empty State -->
          <div v-if="allProducts.length === 0 && !isLoadingProducts" class="text-center py-5 my-5 bg-light sora-border-light border" style="border-radius: 12px;">
            <i class="bi bi-gem fs-1 mb-3" style="color: var(--sora-secondary);"></i>
            <p class="text-dark playfair-font fs-4 mb-2">Không tìm thấy kiệt tác nào</p>
            <p class="text-muted mb-4 fw-light">Vui lòng thử thay đổi bộ lọc hoặc tìm kiếm khác.</p>
            <button @click="resetFilters" class="btn text-uppercase ls-widest px-5 py-3 transition-colors sora-btn-primary" style="font-size: 0.8rem; border-radius: 8px;">Xóa Bộ Lọc</button>
          </div>

          <!-- PAGINATION -->
          <div v-if="pagination.last_page > 1" class="d-flex justify-content-center align-items-center gap-4 mt-5 pt-5 border-top sora-border-light">
            <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="btn rounded-0 px-4 py-2 text-uppercase fw-medium ls-wider transition-colors sora-btn-outline" style="font-size: 0.75rem;">
              <i class="bi bi-chevron-left me-2"></i> Trước
            </button>
            <span class="px-3 playfair-font fs-5 text-dark">
              <span class="fw-bold">{{ pagination.current_page }}</span> 
              <span class="text-muted mx-2 fw-light">/</span> 
              <span class="text-muted fw-light">{{ pagination.last_page }}</span>
            </span>
            <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="btn rounded-0 px-4 py-2 text-uppercase fw-medium ls-wider transition-colors sora-btn-outline" style="font-size: 0.75rem;">
              Sau <i class="bi bi-chevron-right ms-2"></i>
            </button>
          </div>

        </div>
      </div>
    </div>
    
    <!-- CHÂN DUNG SORA (MARQUEE SECTION) -->
    <section class="portrait-section py-5 mt-4 overflow-hidden border-top sora-border-light">
      <div class="text-center mb-5 px-3">
        <p class="text-uppercase text-gold mb-2" style="font-size: 0.75rem; letter-spacing: 0.2em; color: var(--sora-secondary); font-weight: 600;">SORA In Real Life</p>
        <h2 class="playfair-font text-dark fw-normal mb-3" style="font-size: 2.5rem; letter-spacing: 0.02em;">Chân Dung SORA</h2>
        <div class="divider-gold mb-4"></div>
      </div>

      <div class="sora-marquee-wrapper">
        <div class="sora-marquee-container">
          <div class="sora-marquee-track">
            <template v-for="loop in 2" :key="loop">
              <div class="sora-marquee-item" v-for="(img, idx) in portraitImages" :key="`img-${loop}-${idx}`">
                <img :src="img" loading="lazy" alt="SORA Portrait" class="w-100 h-100 object-fit-cover">
                <div class="overlay-hover position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center opacity-0 transition-all duration-400">
                  <div class="overlay-content text-center">
                    <i class="bi bi-instagram text-white d-block mb-3" style="font-size: 2.2rem;"></i>
                    <span class="text-white font-oswald text-uppercase fw-medium" style="letter-spacing: 2px; font-size: 0.85rem;">Chi Tiết</span>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </section>

    <!-- MODAL QUICK ADD -->
    <div v-if="quickAddModal.isOpen" class="modal-overlay position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" @click.self="closeQuickAdd" style="z-index: 9999 !important; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(2px);">
      <div class="bg-white rounded shadow-lg d-flex flex-column position-relative" style="width: 90%; max-width: 480px; max-height: 90vh; overflow: hidden; animation: slideUp 0.3s ease-out; border-radius: 12px !important;">
        <div class="d-flex justify-content-between align-items-center px-4 py-3" style="background-color: #9f273b;">
          <h5 class="mb-0 text-white fw-bold font-serif fs-5" style="letter-spacing: 0.5px;">Tùy chọn Sản phẩm</h5>
          <button @click="closeQuickAdd" class="btn text-white p-0 m-0 border-0" style="opacity: 0.8;">
            <i class="bi bi-x-lg fs-4"></i>
          </button>
        </div>
        <div class="p-4 overflow-y-auto" style="flex-grow: 1;">
          <div class="d-flex gap-3 mb-4 pb-4 border-bottom">
            <div class="flex-shrink-0 border rounded" style="width: 90px; height: 90px; overflow: hidden; border-color: #eaeaea;">
               <img :src="getImageUrl(currentVariant?.image_url || quickAddModal.product.thumbnail_image)" class="w-100 h-100 object-fit-cover bg-light" @error="handleImageError">
            </div>
            <div class="d-flex flex-column justify-content-center">
              <span class="text-uppercase fw-bold mb-1" style="font-size: 0.7rem; color: #e7ce7d; letter-spacing: 2px;">{{ quickAddModal.product.category?.name || 'SẢN PHẨM' }}</span>
              <h6 class="fs-5 mb-2 fw-bold text-dark font-serif">{{ quickAddModal.product.name }}</h6>
              <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="fw-bold fs-5" style="color: #9f273b; font-family: 'Playfair Display', serif;">{{ displayPriceFormatted }}</span>
                <span v-if="modalOldPrice" class="text-muted text-decoration-line-through" style="font-size: 0.95rem;">
                  {{ formatPrice(modalOldPrice) }}
                </span>
                <span v-if="modalDiscount > 0" class="sora-discount-tag" style="padding: 2px 6px; font-size: 0.75rem;">
                  -{{ modalDiscount }}%
                </span>
              </div>
            </div>
          </div>
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
          <div class="mb-4">
            <label class="d-block text-uppercase fw-bold mb-2 text-dark font-oswald" style="font-size: 0.85rem; letter-spacing: 1px;">SỐ LƯỢNG:</label>
            <div class="d-flex align-items-center gap-3">
               <div class="input-group" style="width: 140px;">
                 <button @click="updateQuickAddQty(-1)" class="btn btn-outline-secondary" type="button"><i class="bi bi-dash"></i></button>
                 <input type="number" v-model.number="quickAddModal.quantity" @change="validateQuickAddQty" class="form-control text-center fw-bold text-dark" style="-moz-appearance: textfield;">
                 <button @click="updateQuickAddQty(1)" class="btn btn-outline-secondary" type="button"><i class="bi bi-plus"></i></button>
               </div>
               <span v-if="isAllAttributesSelected && currentVariant" class="text-muted small fw-medium">
                 {{ currentVariant.stock_quantity > 0 ? `Còn ${currentVariant.stock_quantity} sản phẩm` : 'Hết hàng' }}
               </span>
               <span v-else class="text-muted small fw-medium fst-italic">Vui lòng chọn phân loại</span>
            </div>
          </div>
          <div v-if="!isAllAttributesSelected && Object.keys(quickAddModal.attributes).length > 0" class="alert alert-info py-2 small mb-0"><i class="bi bi-info-circle me-1"></i> Vui lòng chọn đầy đủ phân loại sản phẩm.</div>
          <div v-else-if="!currentVariant && Object.keys(quickAddModal.attributes).length > 0" class="alert alert-warning py-2 small mb-0"><i class="bi bi-exclamation-triangle me-1"></i> Phân loại này tạm thời không khả dụng.</div>
          <div v-else-if="currentVariant && currentVariant.stock_quantity <= 0" class="alert alert-danger py-2 small mb-0"><i class="bi bi-slash-circle me-1"></i> Sản phẩm này đã hết hàng trong kho.</div>
        </div>
        <div class="p-3 bg-light border-top">
          <button 
            @click="confirmAddToCart" 
            :disabled="quickAddModal.isAdding || !isAllAttributesSelected || !currentVariant || currentVariant.stock_quantity <= 0"
            class="btn w-100 py-3 text-uppercase fw-bold text-white d-flex justify-content-center align-items-center gap-2"
            style="background-color: #9f273b; border-radius: 8px; font-family: 'Oswald', sans-serif; letter-spacing: 1px; border: none; font-size: 0.95rem;"
          >
            <span v-if="quickAddModal.isAdding" class="spinner-border spinner-border-sm" role="status"></span>
            <template v-else><i class="bi bi-cart-plus me-1"></i> XÁC NHẬN THÊM</template>
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, shallowRef, onMounted, reactive, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Swal from 'sweetalert2';

const route = useRoute();
const router = useRouter();
const shopSlug = ref(route.params.shop_slug || 'aurora-jewelry');
const API_BASE_URL = 'http://127.0.0.1:8000';

const isLoadingCategories = ref(true);
const isLoadingProducts = ref(true);
const categories = shallowRef([]);
const allProducts = shallowRef([]);
const defaultProducts = shallowRef([]); // Lưu danh sách mặc định làm fallback
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });

// Computed property để luôn lấy ra ít nhất 2 sản phẩm cho phần banner "Lựa chọn lý tưởng"
const promoProducts = computed(() => {
  let items = allProducts.value.slice(0, 2);
  if (items.length < 2) {
    const needed = 2 - items.length;
    // Bù thêm từ danh sách mặc định nếu không đủ 2 sản phẩm
    const fallback = defaultProducts.value.filter(dp => !items.some(item => item.id === dp.id)).slice(0, needed);
    items = [...items, ...fallback];
  }
  return items;
});

const portraitImages = [
  'https://placehold.co/400x500/e7ce7d/ffffff?text=Chân+Dung+1',
  'https://placehold.co/400x500/9f273b/ffffff?text=Chân+Dung+2',
  'https://placehold.co/400x500/fcfcfc/000000?text=Chân+Dung+3',
  'https://placehold.co/400x500/2c2c2c/ffffff?text=Chân+Dung+4',
  'https://placehold.co/400x500/e7ce7d/ffffff?text=Chân+Dung+5',
  'https://placehold.co/400x500/9f273b/ffffff?text=Chân+Dung+6',
];

const fallbackJewelryImages = [
  'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=500&q=80',
  'https://images.unsplash.com/photo-1599643478524-fb66f70d00f0?w=500&q=80',
  'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=500&q=80',
  'https://images.unsplash.com/photo-1601121141461-9d6647bca1ed?w=500&q=80',
  'https://images.unsplash.com/photo-1588444650733-d0767b753cb8?w=500&q=80'
];

const soraAlert = Swal.mixin({
  buttonsStyling: true,
  confirmButtonColor: '#9f273b',
  customClass: { confirmButton: 'px-4 py-2 mx-2 rounded shadow-sm fw-bold font-oswald tracking-widest text-uppercase' },
  didOpen: (modal) => { if (modal.parentElement) modal.parentElement.style.zIndex = '10005'; }
});

const Toast = Swal.mixin({
  toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true,
  didOpen: (toast) => { if (toast.parentElement) toast.parentElement.style.zIndex = '10005'; }
});

const formatPrice = (price) => {
  if (!price || isNaN(price)) return 'Liên Hệ';
  return new Intl.NumberFormat('vi-VN').format(price) + ' đ';
};

const getImageUrl = (path) => path ? (path.startsWith('http') ? path : `${API_BASE_URL}/storage/${path}`) : fallbackJewelryImages[0];

const handleImageError = (e) => {
  e.target.onerror = null; 
  const randomIndex = Math.floor(Math.random() * fallbackJewelryImages.length);
  e.target.src = fallbackJewelryImages[randomIndex];
};

const hasHoverImage = (product) => product.hover_image && product.hover_image !== product.thumbnail_image;

const getToken = () => {
  const possibleKeys = ['access_token', 'token', 'auth_token', 'userToken', 'user_token', 'user'];
  
  for (const k of possibleKeys) {
    const rawVal = localStorage.getItem(k) || sessionStorage.getItem(k);
    if (!rawVal) continue;
    if (rawVal.startsWith('{')) {
      try {
        const parsed = JSON.parse(rawVal);
        if (parsed?.access_token) return parsed.access_token;
        if (parsed?.token) return parsed.token;
        if (parsed?.user?.token) return parsed.user.token;
      } catch(e) { }
    } else if (rawVal.length > 15) {
      return rawVal;
    }
  }
  return '';
};

const favourites = ref([]);
const isTogglingFav = ref(null);

const fetchFavorites = async () => {
  const token = getToken();
  if (!token) return; 
  try {
    const response = await fetch(`${API_BASE_URL}/api/client/favourites`, {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    const data = await response.json();
    if (data.status) favourites.value = data.data.map(fav => fav.product_id);
  } catch (e) {
    console.error('Lỗi tải danh sách yêu thích:', e);
  }
};

const isFavourited = (productId) => favourites.value.includes(productId);

const toggleFavourite = async (product) => {
  const token = getToken();
  if (!token) {
    return soraAlert.fire({
      icon: 'warning', title: 'Bạn chưa đăng nhập!',
      text: 'Vui lòng đăng nhập để lưu trữ bộ sưu tập yêu thích của mình.',
      confirmButtonText: 'Đăng Nhập Ngay', showCancelButton: true, cancelButtonText: 'Đóng'
    }).then((result) => { if (result.isConfirmed) router.push('/login'); });
  }

  isTogglingFav.value = product.id; 
  try {
    const response = await fetch(`${API_BASE_URL}/api/client/favourites/toggle`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      body: JSON.stringify({ product_id: product.id })
    });
    
    const data = await response.json();
    if (data.status) {
      if (data.action === 'added') {
        favourites.value.push(product.id);
        Toast.fire({ icon: 'success', title: 'Đã thêm vào yêu thích', color: '#9f273b', iconColor: '#9f273b' });
      } else if (data.action === 'removed') {
        favourites.value = favourites.value.filter(id => id !== product.id);
        Toast.fire({ icon: 'info', title: 'Đã bỏ yêu thích' });
      }
    }
  } catch (error) {
    Toast.fire({ icon: 'error', title: 'Có lỗi xảy ra, thử lại sau' });
  } finally {
    isTogglingFav.value = null; 
  }
};

const filters = reactive({ sort: 'recommended', categories: '', collections: '', metals: '' });
const filterGroups = ref([
  { title: 'Danh Mục', key: 'categories', isOpen: true, options: [] },
  { title: 'Bộ Sưu Tập', key: 'collections', isOpen: true, options: [
      { label: 'Solitaire Classic', value: 'solitaire' },
      { label: 'Halo Brilliance', value: 'halo' }
    ]
  }
]);

const toggleFilter = (key, value) => { 
  filters[key] = filters[key] === value ? '' : value; 
  applyFilters(); 
};

const fetchCategories = async () => {
  isLoadingCategories.value = true;
  try {
    const response = await fetch(`${API_BASE_URL}/api/shop/${shopSlug.value}/categories`);
    const data = await response.json();
    if(data?.success) {
      categories.value = data.data; 
      const idx = filterGroups.value.findIndex(g => g.key === 'categories');
      if (idx !== -1) {
        filterGroups.value[idx].options = data.data.map(cat => ({ label: cat.name, value: cat.slug }));
      }
    }
  } catch (e) {
    console.error(e);
  } finally { 
    isLoadingCategories.value = false; 
  }
};

const fetchProducts = async (page = 1) => {
  isLoadingProducts.value = true;
  try {
    const queryPayload = { page, sort: filters.sort };
    Object.entries(filters).forEach(([key, val]) => {
      if (key !== 'sort' && val) queryPayload[key] = val;
    });
    
    const params = new URLSearchParams(queryPayload);
    const response = await fetch(`${API_BASE_URL}/api/shop/${shopSlug.value}/products?${params.toString()}`);
    const data = await response.json();
    
    if(data?.success) {
      allProducts.value = data.data.data; 
      pagination.value = { current_page: data.data.current_page, last_page: data.data.last_page, total: data.data.total };
      
      // Lưu lại danh sách sản phẩm lúc mới load trang (không có bộ lọc) để dự phòng cho banner
      if (defaultProducts.value.length === 0 && data.data.data.length > 0) {
        defaultProducts.value = data.data.data;
      }
    } else {
      allProducts.value = [];
    }
  } catch (e) { 
    allProducts.value = []; 
  } finally { 
    isLoadingProducts.value = false; 
  }
};

const filterByCategory = (categorySlug) => {
  filters.categories = filters.categories === categorySlug ? '' : categorySlug; 
  filters.collections = ''; 
  applyFilters();
};

const applyFilters = () => fetchProducts(1);
const resetFilters = () => { 
  Object.keys(filters).forEach(k => filters[k] = k === 'sort' ? 'recommended' : ''); 
  applyFilters(); 
};

// Đã sửa lại lỗi cuộn màn hình khi chuyển trang
const changePage = (page) => { 
  if(page >= 1 && page <= pagination.value.last_page) { 
    fetchProducts(page); 
    const shopTopBar = document.querySelector('.shop-top-bar');
    if (shopTopBar) {
      const y = shopTopBar.getBoundingClientRect().top + window.scrollY - 80;
      window.scrollTo({ top: y, behavior: 'smooth' }); 
    }
  } 
};

const goToProductDetail = (slug) => { 
  if (slug) router.push({ name: 'productDetail', params: { shop_slug: shopSlug.value, slug: slug } }); 
};

const quickAddModal = reactive({
  isOpen: false, product: null, attributes: {}, selectedOptions: {}, quantity: 1, isAdding: false
});

const openQuickAdd = (productSummary) => {
  quickAddModal.product = productSummary;
  quickAddModal.selectedOptions = {}; 
  quickAddModal.quantity = 1;
  quickAddModal.isAdding = false;
  
  const attrs = {};
  productSummary.variants?.forEach(variant => {
    variant.attribute_values?.forEach(av => {
      const attrName = av.attribute?.name;
      if (attrName) {
        if (!attrs[attrName]) attrs[attrName] = new Set();
        attrs[attrName].add(av.value);
      }
    });
  });
  
  quickAddModal.attributes = Object.fromEntries(
    Object.entries(attrs).map(([key, valueSet]) => [key, Array.from(valueSet)])
  );
  
  document.body.style.overflow = 'hidden';
  quickAddModal.isOpen = true;
};

const closeQuickAdd = () => {
  document.body.style.overflow = 'auto'; 
  quickAddModal.isOpen = false;
  quickAddModal.product = null;
};

const isAllAttributesSelected = computed(() => {
  const requiredAttrs = Object.keys(quickAddModal.attributes || {});
  if (requiredAttrs.length === 0) return true;
  return requiredAttrs.every(attr => quickAddModal.selectedOptions[attr] != null);
});

const currentVariant = computed(() => {
  const variants = quickAddModal.product?.variants;
  if (!variants?.length) return null;
  if (Object.keys(quickAddModal.attributes).length === 0) return variants[0];
  if (!isAllAttributesSelected.value) return null;

  return variants.find(v => {
    if (!v.attribute_values) return false;
    return Object.entries(quickAddModal.selectedOptions).every(([attrName, selectedVal]) => {
      return v.attribute_values.some(av => 
        av.attribute?.name === attrName && String(av.value) === String(selectedVal)
      );
    });
  });
});

const displayPriceFormatted = computed(() => {
  if (currentVariant.value) {
    return formatPrice(currentVariant.value.promotional_price || currentVariant.value.price);
  }
  if (quickAddModal.product) {
    if (quickAddModal.product.variants?.length > 0) {
      const prices = quickAddModal.product.variants.map(v => parseFloat(v.promotional_price || v.price));
      const min = Math.min(...prices);
      const max = Math.max(...prices);
      if (min !== max && !isNaN(min) && !isNaN(max)) return `${formatPrice(min)} - ${formatPrice(max)}`;
      return formatPrice(min);
    }
    return formatPrice(quickAddModal.product.promotional_price || quickAddModal.product.base_price);
  }
  return formatPrice(0);
});

const modalOldPrice = computed(() => {
  if (currentVariant.value && currentVariant.value.promotional_price && currentVariant.value.price > currentVariant.value.promotional_price) {
    return currentVariant.value.price;
  }
  if (!currentVariant.value && quickAddModal.product?.promotional_price && quickAddModal.product?.base_price > quickAddModal.product?.promotional_price) {
    return quickAddModal.product.base_price;
  }
  return null;
});

const modalDiscount = computed(() => {
  if (currentVariant.value && currentVariant.value.promotional_price && currentVariant.value.price > currentVariant.value.promotional_price) {
    return Math.round(((currentVariant.value.price - currentVariant.value.promotional_price) / currentVariant.value.price) * 100);
  }
  if (!currentVariant.value && quickAddModal.product?.promotional_price && quickAddModal.product?.base_price > quickAddModal.product?.promotional_price) {
    return Math.round(((quickAddModal.product.base_price - quickAddModal.product.promotional_price) / quickAddModal.product.base_price) * 100);
  }
  return 0;
});

const updateQuickAddQty = (delta) => {
  if (!isAllAttributesSelected.value) { return Toast.fire({ icon: 'info', title: 'Vui lòng chọn đầy đủ phân loại.' }); }
  const maxStock = currentVariant.value?.stock_quantity || 0;
  let newQty = quickAddModal.quantity + delta;
  
  if (newQty < 1) newQty = 1;
  if (newQty > maxStock) { 
    Toast.fire({ icon: 'warning', title: `Chỉ còn tối đa ${maxStock} sản phẩm` }); 
    newQty = maxStock; 
  }
  quickAddModal.quantity = newQty;
};

const validateQuickAddQty = () => {
  if (!isAllAttributesSelected.value) { 
    quickAddModal.quantity = 1; 
    return Toast.fire({ icon: 'info', title: 'Vui lòng chọn đầy đủ phân loại.' }); 
  }
  const maxStock = currentVariant.value?.stock_quantity || 0;
  let qty = parseInt(quickAddModal.quantity);
  
  if (isNaN(qty) || qty < 1) quickAddModal.quantity = 1;
  else if (qty > maxStock) { 
    Toast.fire({ icon: 'warning', title: `Chỉ còn tối đa ${maxStock} sản phẩm` }); 
    quickAddModal.quantity = maxStock; 
  }
};

const confirmAddToCart = async () => {
  if (!isAllAttributesSelected.value || !currentVariant.value) {
    return Toast.fire({ icon: 'warning', title: 'Vui lòng chọn đầy đủ phân loại.' });
  }
  const maxStock = currentVariant.value.stock_quantity || 0;
  if (quickAddModal.quantity > maxStock) {
    return soraAlert.fire({ icon: 'error', title: 'Vượt quá tồn kho', text: `Rất tiếc, cửa hàng chỉ còn ${maxStock} sản phẩm khả dụng.` });
  }
  
  quickAddModal.isAdding = true;
  try {
    const token = getToken();
    let sessionId = localStorage.getItem('cart_session_id');
    if (!sessionId) {
      sessionId = 'session_' + Math.random().toString(36).substr(2, 9);
      localStorage.setItem('cart_session_id', sessionId);
    }

    const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Cart-Session-Id': sessionId };
    if (token) headers['Authorization'] = `Bearer ${token}`;

    const response = await fetch(`${API_BASE_URL}/api/client/cart`, {
      method: 'POST', headers,
      body: JSON.stringify({ product_variant_id: currentVariant.value.id, quantity: quickAddModal.quantity })
    });
    
    const data = await response.json();
    if (data.success) { closeQuickAdd(); router.push('/cart'); } 
    else { soraAlert.fire({ icon: 'error', title: 'Không thể thêm', text: data.message || "Đã có lỗi xảy ra." }); }
  } catch (error) { 
    soraAlert.fire({ icon: 'error', title: 'Lỗi', text: 'Lỗi kết nối tới máy chủ.' }); 
  } finally { 
    quickAddModal.isAdding = false; 
  }
};

onMounted(() => { 
  // Đảm bảo cuộn lên đầu trang ngay khi load
  window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
  
  fetchCategories();
  fetchProducts(1);
  fetchFavorites();
});
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
.tracking-wider { letter-spacing: 1px; }
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

/* Các hiệu ứng Hover mới */
.hover-translate-y:hover { transform: translateY(-6px); box-shadow: 0 10px 25px rgba(0,0,0,0.12) !important; }
.object-fit-contain { object-fit: contain !important; }
.group:hover .group-hover-scale { transform: scale(1.05); }

/* Gạch chân màu vàng khi hover Danh mục */
.category-name::after {
  content: '';
  position: absolute;
  width: 0;
  height: 2px;
  bottom: -2px;
  left: 50%;
  transform: translateX(-50%);
  background-color: var(--sora-secondary);
  transition: width 0.3s ease;
}
.group:hover .category-name::after {
  width: 100%;
}

/* CUSTOM CHECKBOX */
.custom-checkbox .checkmark {
  position: relative; width: 18px; height: 18px; background-color: #fff; border: 1px solid #ccc; border-radius: 3px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.custom-checkbox:hover .checkmark { border-color: var(--sora-primary); }
.custom-checkbox .checkmark.checked { background-color: var(--sora-primary); border-color: var(--sora-primary); }
.custom-checkbox .checkmark::after { content: ""; position: absolute; display: none; width: 5px; height: 10px; border: solid white; border-width: 0 2px 2px 0; transform: rotate(45deg); margin-top: -2px; }
.custom-checkbox .checkmark.checked::after { display: block; }
.filter-list .label-text { font-size: 0.95rem; }

.bg-primary-custom { background-color: var(--sora-primary) !important; }
.border-primary-custom { border: 1px solid var(--sora-primary) !important; }
.text-primary-custom { color: var(--sora-primary) !important; }
.border-muted { border: 1px solid #ced4da !important; }

/* SORA LUXURY PRODUCT CARD STANDARDIZED - Tối ưu tự động co giãn bằng minmax() */
.product-grid { 
  display: grid; 
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); 
  gap: 2.5rem 1.5rem; 
}

.sora-luxury-card {
    background: #ffffff; border: 1px solid #f0f0f0; border-radius: 12px; position: relative; display: flex; flex-direction: column; overflow: hidden; transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); cursor: pointer; height: 100%;
}
.sora-luxury-card:hover { box-shadow: 0 15px 35px rgba(0,0,0,0.08); border-color: #e5e5e5; transform: translateY(-5px); }

.sora-card-image { position: relative; width: 100%; aspect-ratio: 1 / 1; overflow: hidden; background-color: #f9f9f9; }
.sora-card-image img { width: 100%; height: 100%; object-fit: cover; object-position: center; transition: opacity 0.6s ease; }

.sora-main-img { z-index: 1; }
.sora-hover-img { position: absolute; top:0; left:0; z-index: 2; opacity: 0; }
.sora-luxury-card:hover .sora-card-image.has-hover-image .sora-main-img { opacity: 0; }
.sora-luxury-card:hover .sora-card-image.has-hover-image .sora-hover-img { opacity: 1; }

.sora-card-badges { position: absolute; top: 15px; left: 15px; z-index: 10; display: flex; flex-direction: column; gap: 8px; }
.sora-badge { background: #ffffff; color: #222; font-family: 'Oswald', sans-serif; font-size: 0.65rem; font-weight: 700; letter-spacing: 2px; padding: 4px 10px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
.sale-badge { background-color: #9f273b !important; color: white !important; }

.sora-wishlist-btn { position: absolute; top: 15px; right: 15px; width: 38px; height: 38px; background: #ffffff; border: none; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); z-index: 10; cursor: pointer; transition: all 0.3s ease; color: #6c757d; }
.sora-wishlist-btn:hover:not(:disabled) { transform: scale(1.1); color: #cc1e2e; }
.sora-wishlist-btn.active { color: #dc3545; }
.sora-wishlist-btn.active i { color: #dc3545 !important; }
.sora-wishlist-btn i { font-size: 1.15rem; margin-top: 2px; }

.sora-card-info { padding: 20px 15px 70px 15px; text-align: center; flex-grow: 1; display: flex; flex-direction: column; justify-content: center; }
.sora-card-title { font-family: 'Oswald', sans-serif; font-size: 1.1rem; font-weight: 600; color: #111; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.sora-card-category { font-family: 'Playfair Display', serif; font-style: italic; color: #666; font-size: 0.95rem; margin-bottom: 15px; }
.sora-card-price { font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 700; color: #9f273b; margin-top: auto; }
.sora-card-price-old { font-size: 0.95rem; color: #999; text-decoration: line-through; margin-right: 10px; font-weight: 400; }

.sora-card-action { position: absolute; bottom: 0; left: 0; width: 100%; transform: translateY(100%); transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); z-index: 10; }
.sora-luxury-card:hover .sora-card-action { transform: translateY(0); }
.sora-action-btn { width: 100%; padding: 14px 0; background: #731621; color: #ffffff; border: none; font-family: 'Oswald', sans-serif; font-size: 0.9rem; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; transition: background 0.3s ease; }
.sora-action-btn:hover { background: #500f17; color: #fff;}

.variant-select-btn:hover { border-color: #9f273b; color: #9f273b; }
.variant-select-btn.selected { border-color: #9f273b; color: #9f273b; font-weight: 700; background-color: #fdf5f6; box-shadow: inset 0 0 0 1px #9f273b; }

/* SORA MARQUEE */
.portrait-section { background: linear-gradient(to bottom, #ffffff, #faf9f7); }
.sora-marquee-wrapper { width: 100%; padding: 20px 0; -webkit-mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent); mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent); }
.sora-marquee-container { width: 100%; overflow: hidden; position: relative; padding: 20px 0; }
.sora-marquee-track { display: flex; width: max-content; align-items: center; animation: sora-marquee 40s linear infinite; }
.sora-marquee-container:hover .sora-marquee-track { animation-play-state: paused; }
.sora-marquee-item { position: relative; width: 280px; height: 380px; margin-right: 24px; flex-shrink: 0; overflow: hidden; cursor: pointer; border-radius: 16px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: all 0.4s ease; }
.sora-marquee-item:nth-child(even) { margin-top: 40px; }
.sora-marquee-item img { transition: transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94); }
.sora-marquee-item:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.12); z-index: 10; }
.sora-marquee-item:hover img { transform: scale(1.1); }

.overlay-hover { background: rgba(159, 39, 59, 0.7); backdrop-filter: blur(3px); }
.overlay-content { transform: translateY(20px); transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); }
.sora-marquee-item:hover .overlay-hover { opacity: 1 !important; }
.sora-marquee-item:hover .overlay-content { transform: translateY(0); }

@keyframes sora-marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
@keyframes slideUp { from { transform: translateY(30px) scale(0.98); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }

@media (max-width: 768px) { 
  .sora-marquee-wrapper { -webkit-mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent); mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent); }
  .sora-marquee-item { width: 220px; height: 300px; margin-right: 16px; }
  .sora-marquee-item:nth-child(even) { margin-top: 20px; }
  .sora-marquee-track { animation-duration: 25s; } 
}
</style>