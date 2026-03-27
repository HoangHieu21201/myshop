<template>
  <div class="product-page">
    
    <!-- LOADING STATE -->
    <div v-if="isLoading" class="loading-container">
      <div class="spinner"></div>
      <p>Đang tải chi tiết sản phẩm...</p>
    </div>

    <!-- MAIN CONTENT -->
    <template v-else-if="product">
      <!-- Breadcrumb -->
      <div class="breadcrumb">
        <span>Trang chủ</span> <span class="separator">/</span>
        <span>Sản phẩm</span> <span class="separator">/</span>
        <span class="current">{{ product.name }}</span>
      </div>

      <main class="product-container">
        <!-- CHIA LẠI GRID 2 CỘT SANG TRỌNG CẢ TẬP TRUNG VÀO HÌNH ẢNH -->
        <div class="product-grid">
          
          <!-- CỘT 1 (TRÁI): THƯ VIỆN HÌNH ẢNH (Chiếm diện tích lớn) -->
          <div class="product-gallery">
            <div class="thumbnails-list">
              <button 
                v-for="(img, idx) in product.images" 
                :key="idx" 
                @click="setMainImage(img)"
                class="thumb-btn"
                :class="{ 'active': img === mainImage }"
              >
                <img :src="img" :alt="'Thumbnail ' + idx" class="thumb-img">
              </button>
            </div>
            
            <div class="main-image-wrapper">
              <img :src="mainImage" :alt="product.name" class="main-img">
            </div>
          </div>

          <!-- CỘT 2 (PHẢI): THÔNG TIN SẢN PHẨM -->
          <div class="product-info">
            
            <div class="product-brand-top">
              <span v-if="product.brand" class="brand-name" @click="goToShopWithBrand(product.brand.id)">
                {{ product.brand.name }}
              </span>
              <span v-else class="brand-name">SORA JEWELRY</span>
              <span class="sku" v-if="product.sku">SKU: {{ product.sku }}</span>
            </div>

            <h1 class="product-title">{{ product.name }}</h1>
            
            <div class="product-price mb-4">
              <template v-if="isAllAttributesSelected && currentVariant">
                <span class="price-current">{{ formatMoney(currentVariant.promotional_price || currentVariant.price) }}</span>
                <span v-if="currentVariant.promotional_price" class="price-old">
                  {{ formatMoney(currentVariant.price) }}
                </span>
                <span v-if="currentVariant.promotional_price" class="discount-badge">
                  -{{ Math.round((1 - currentVariant.promotional_price / currentVariant.price) * 100) }}%
                </span>
              </template>
              <template v-else>
                <!-- Hiển thị giá của biến thể đầu tiên hoặc giá cơ bản làm tham khảo khi chưa chọn -->
                <span class="price-current">{{ formatMoney(product.promotional_price || product.base_price || product.variants?.[0]?.price) }}</span>
              </template>
            </div>

            <div class="product-variants">
              <div v-for="(options, attrName) in product.attributes" :key="attrName" class="variant-group">
                <h3 class="variant-label">{{ attrName }}</h3>
                <div class="variant-options">
                  <button 
                    v-for="option in options" 
                    :key="option.id"
                    @click="selectAttribute(attrName, option.id)"
                    class="variant-btn"
                    :class="{ 'active': selectedAttributes[attrName] === option.id }"
                  >
                    {{ option.name }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Vị trí mới hiển thị Tồn kho (Theo yêu cầu) -->
            <div class="stock-status-luxury mb-3">
              <span v-if="isAllAttributesSelected && currentVariant" :class="getStock(currentVariant) > 0 ? 'in-stock' : 'out-of-stock'">
                <i class="fa-solid" :class="getStock(currentVariant) > 0 ? 'fa-box-open' : 'fa-box'"></i> 
                {{ getStock(currentVariant) > 0 ? `Còn ${getStock(currentVariant)} sản phẩm` : 'Pre-Order (Đặt trước)' }}
              </span>
              <span v-else class="text-muted fst-italic" style="font-size: 0.9rem;">
                <i class="fa-solid fa-circle-info me-1"></i> Vui lòng chọn đầy đủ phân loại để xem số lượng
              </span>
            </div>

            <!-- Action Buttons Area -->
            <div class="action-area">
              <div class="quantity-selector">
                <button class="qty-btn" @click="updateQuantity(-1)">-</button>
                <input type="number" 
                       v-model.number="selectedQuantity" 
                       @change="validateQuantity"
                       class="qty-input">
                <button class="qty-btn" @click="updateQuantity(1)">+</button>
              </div>

              <div class="action-buttons">
                <button 
                  class="btn-add-cart"
                  @click="addToCart"
                >
                  THÊM VÀO GIỎ
                </button>
                <button class="btn-consult">TƯ VẤN NGAY</button>
              </div>
            </div>

            <!-- Short Description / Notes -->
            <div class="product-short-desc">
              <p><em>(*) Giá niêm yết trên đây là <strong>GIÁ THAM KHẢO</strong> dành cho nhẫn nữ với các thông số tiêu chuẩn. Giá có thể thay đổi trên thực tế tùy thuộc vào thông số cụ thể <strong>theo ni tay và yêu cầu riêng của từng khách hàng.</strong></em></p>
              
              <div class="notes">
                <h4>GHI CHÚ</h4>
                <ul>
                  <li>Sản phẩm được bảo hành trọn đời với phiếu bảo hành đính kèm.</li>
                  <li>Nhấn nút <strong>TƯ VẤN NGAY</strong> để gặp nhân viên hỗ trợ thêm về thông tin và báo giá sản phẩm.</li>
                </ul>
              </div>
            </div>

          </div>
        </div>
      </main>

      <!-- DÒNG HÀNG NỔI BẬT SECTION -->
      <section class="featured-lines-section">
        <h2 class="section-title text-center">Dòng hàng nổi bật</h2>
        <div class="featured-lines-container">
          <!-- Banner Trái -->
          <div class="featured-banner">
            <img src="https://bazaarvietnam.vn/wp-content/uploads/2015/05/co-nen-cat-toc-ngan-02-lisa-blackpink-bvlgari.jpg" alt="Kim cương tinh tuyển">
          </div>
          
          <!-- Nội dung Phải -->
          <div class="featured-content">
            <!-- Các nút thương hiệu lấy từ DB -->
            <div class="featured-tags">
              <button 
                v-for="brand in shopBrands" 
                :key="brand.id" 
                class="f-tag-btn"
                @click="goToShopWithBrand(brand.id)"
              >
                {{ brand.name }}
              </button>
              
              <button v-if="shopBrands.length === 0" class="f-tag-btn">Đang tải thương hiệu...</button>
            </div>
            
            <div class="featured-desc">
              <p>
                <strong>Sora – Tỏa sáng vẻ đẹp tinh tế</strong> với những thiết kế trang sức nổi bật được chế tác tỉ mỉ trong từng chi tiết. Lấy cảm hứng từ nét đẹp hiện đại pha lẫn sự thanh lịch, mỗi sản phẩm mang đến dấu ấn riêng, giúp bạn tự tin thể hiện phong cách cá nhân.
              </p>
              <p>
                Từ những mẫu tối giản đến thiết kế cầu kỳ, <em>bộ sưu tập nổi bật của Sora</em> không chỉ là trang sức mà còn là biểu tượng của sự sang trọng và đẳng cấp. Sora đồng hành cùng bạn trong mọi khoảnh khắc – từ thường ngày đến những dịp đặc biệt.
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- CÓ THỂ BẠN SẼ THÍCH SECTION -->
      <section class="recommendations-section">
        <div class="recommendations-header">
          <div class="rec-title-wrap">
            <h2 class="rec-title">CÓ THỂ BẠN SẼ THÍCH</h2>
            <p class="rec-subtitle">Khám phá thêm các sản phẩm chất lượng khác</p>
          </div>
          
          <div class="rec-controls">
            <div class="rec-tabs">
              <button :class="{ active: activeTab === 'related_category' }" @click="changeRecTab('related_category')">Cùng danh mục</button>
              <button :class="{ active: activeTab === 'new' }" @click="changeRecTab('new')">Sản phẩm mới</button>
              <button :class="{ active: activeTab === 'viewed' }" @click="changeRecTab('viewed')">Sản phẩm đã xem</button>
            </div>
            
            <div class="rec-arrows">
              <button @click="scrollRecSlider('left')" class="arrow-btn" aria-label="Previous">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
              </button>
              <button @click="scrollRecSlider('right')" class="arrow-btn" aria-label="Next">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
              </button>
            </div>
          </div>
        </div>

        <div v-if="isLoadingRecs" class="rec-loading">
          <div class="spinner small-spinner"></div>
        </div>

        <div v-else class="rec-slider-container" ref="recSliderRef">
          <div 
            v-for="item in recommendedProducts" 
            :key="item.id" 
            class="rec-product-card"
            @click="goToProduct(item.slug || item.id)"
          >
            <button class="wishlist-float-btn" @click.stop>
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            </button>
            <div class="rec-img-wrap">
              <img :src="getImageUrl(item.thumbnail_image)" :alt="item.name">
            </div>
            <div class="rec-card-body">
              <span class="rec-brand">{{ item.brand?.name || 'SORA' }}</span>
              <h4 class="rec-name" :title="item.name">{{ item.name }}</h4>
              <div class="rec-price">
                {{ formatMoney(item.promotional_price || item.base_price || 0) }}
              </div>
            </div>
          </div>
          
          <div v-if="recommendedProducts.length === 0" class="rec-empty">
            Chưa có sản phẩm nào.
          </div>
        </div>
      </section>

      <!-- CHI TIẾT SẢN PHẨM -->
      <section class="product-description-section">
        <h2 class="section-title">MÔ TẢ SẢN PHẨM</h2>
        <div class="description-content" v-html="product.description"></div>
      </section>

    </template>
    
    <!-- ERROR STATE -->
    <div v-else class="error-container">
      <h2>Không tìm thấy sản phẩm!</h2>
      <p>Sản phẩm này có thể đã bị xóa hoặc không tồn tại.</p>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

// -------------------------------------------------------------
// KHỞI TẠO STATE & TÍCH HỢP API CHÍNH
// -------------------------------------------------------------
const route = useRoute();
const router = useRouter();
const product = ref(null);
const mainImage = ref('');
const selectedAttributes = ref({});
const selectedQuantity = ref(1);
const isLoading = ref(true);

const API_BASE_URL = 'http://127.0.0.1:8000'; 
const shopSlug = route.params.shop_slug || 'aurora';

// --- STATE CHO PHẦN CÓ THỂ BẠN SẼ THÍCH ---
const activeTab = ref('related_category');
const recommendedProducts = ref([]);
const isLoadingRecs = ref(false);
const recSliderRef = ref(null);

// --- STATE CHO DÒNG HÀNG NỔI BẬT ---
const shopBrands = ref([]); 

// Cấu hình SweetAlert Sang trọng (Cho Modal)
const soraAlert = Swal.mixin({
  buttonsStyling: true,
  confirmButtonColor: '#9f273b',
  cancelButtonColor: '#6c757d',
  customClass: {
    confirmButton: 'px-4 py-2 mx-2 rounded-pill shadow-sm fw-bold',
    cancelButton: 'px-4 py-2 mx-2 rounded-pill fw-bold'
  }
});

// Cấu hình Toast Notification (Góc phải màn hình giống hệt thông báo thành công)
const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  background: '#fffafa',
  color: '#9f273b',
  iconColor: '#9f273b'
});

// Hàm lấy số lượng tồn kho của 1 biến thể
const getStock = (variant) => {
  if(!variant) return 0;
  return variant.stock_quantity ?? variant.stock ?? 0;
}

// Kiểm tra xem user đã chọn đủ thuộc tính chưa
const isAllAttributesSelected = computed(() => {
  if (!product.value || !product.value.attributes) return false;
  const requiredAttrs = Object.keys(product.value.attributes);
  if (requiredAttrs.length === 0) return true; // Không có thuộc tính (sản phẩm đơn giản)
  
  return requiredAttrs.every(attr => selectedAttributes.value[attr] !== undefined);
});

// Xác định biến thể đang được chọn
const currentVariant = computed(() => {
  if (!product.value || !product.value.variants) return null;
  // Trả về biến thể khớp với các thuộc tính đang chọn
  return product.value.variants.find((variant) => {
    for (const [attrName, selectedOptionId] of Object.entries(selectedAttributes.value)) {
      if (variant.attributes[attrName] !== selectedOptionId) {
        return false;
      }
    }
    return true;
  });
});

// Hàm tải dữ liệu sản phẩm
const fetchProductData = async () => {
  const productSlug = route.params.slug || route.params.product_slug; 
  if (!productSlug) {
    isLoading.value = false;
    return;
  }

  isLoading.value = true;
  try {
    const response = await fetch(`${API_BASE_URL}/api/shop/${shopSlug}/products/${productSlug}`);
    const result = await response.json();

    if (result.success && result.data) {
      product.value = result.data;
      
      if (product.value.images && product.value.images.length > 0) {
        mainImage.value = product.value.images[0];
      }

      // Không tự động chọn mặc định
      selectedAttributes.value = {};
      selectedQuantity.value = 1;

      saveToRecentlyViewed(product.value);
      fetchRecommendations('related_category');

    } else {
      console.error("Lỗi từ server:", result.message);
    }
  } catch (error) {
    console.error("Lỗi kết nối API lấy chi tiết sản phẩm:", error);
  } finally {
    isLoading.value = false;
  }
};

const fetchBrands = async () => {
  try {
    const response = await fetch(`${API_BASE_URL}/api/shop/${shopSlug}/brands`);
    const result = await response.json();
    if (result.success && result.data) {
      shopBrands.value = result.data.slice(0, 10);
    }
  } catch (error) {
    console.error("Lỗi tải danh mục thương hiệu:", error);
  }
};

onMounted(() => {
  fetchProductData();
  fetchBrands();
});

watch(() => route.params.slug, (newSlug, oldSlug) => {
  if (newSlug && newSlug !== oldSlug) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    fetchProductData();
  }
});

// --- LOGIC UI/UX THÊM VÀO GIỎ & KIỂM TRA TỒN KHO ---
const selectAttribute = (attrName, optionId) => {
  selectedAttributes.value[attrName] = optionId;
  selectedQuantity.value = 1; // Reset số lượng khi đổi option
  
  // Nếu đã chọn đủ, cập nhật hình ảnh chính (nếu biến thể có ảnh riêng)
  if (isAllAttributesSelected.value && currentVariant.value && currentVariant.value.image_url) {
    setMainImage(currentVariant.value.image_url);
  }
};

const updateQuantity = (delta) => {
  if (!isAllAttributesSelected.value || !currentVariant.value) {
    Toast.fire({ icon: 'info', title: 'Vui lòng chọn phân loại hàng trước' });
    return;
  }

  let newQty = selectedQuantity.value + delta;
  const stock = getStock(currentVariant.value);

  if (newQty < 1) newQty = 1;
  
  if (newQty > stock) {
    Toast.fire({ icon: 'warning', title: 'Đã quá số lượng trong kho' });
    newQty = stock;
  }

  selectedQuantity.value = newQty;
};

const validateQuantity = () => {
  if (!isAllAttributesSelected.value || !currentVariant.value) {
    Toast.fire({ icon: 'info', title: 'Vui lòng chọn phân loại hàng trước' });
    selectedQuantity.value = 1;
    return;
  }

  let qty = parseInt(selectedQuantity.value);
  const stock = getStock(currentVariant.value);

  if (isNaN(qty) || qty < 1) {
    selectedQuantity.value = 1;
    return;
  }

  if (qty > stock) {
    Toast.fire({ icon: 'warning', title: 'Đã quá số lượng trong kho' });
    selectedQuantity.value = stock;
  }
};

const getHeaders = () => {
  const headers = { 'Accept': 'application/json' };
  const token = localStorage.getItem('auth_token');
  if (token) headers['Authorization'] = `Bearer ${token}`;
  
  let sid = localStorage.getItem('cart_session_id');
  if (!sid && !token) { 
    sid = 'session_' + Math.random().toString(36).substr(2, 9);
    localStorage.setItem('cart_session_id', sid);
  }
  if (sid) headers['X-Cart-Session-Id'] = sid;
  return headers;
};

const addToCart = async () => {
  if (!isAllAttributesSelected.value || !currentVariant.value) {
    Toast.fire({ icon: 'info', title: 'Vui lòng chọn đầy đủ thông tin sản phẩm (màu sắc, kích thước...) trước khi thêm.' });
    return;
  }

  const stock = getStock(currentVariant.value);
  if (selectedQuantity.value > stock) {
    Toast.fire({ icon: 'warning', title: 'Đã quá số lượng trong kho' });
    return;
  }

  try {
    const payload = {
      product_variant_id: currentVariant.value.id,
      quantity: selectedQuantity.value
    };

    const response = await axios.post(`${API_BASE_URL}/api/client/cart`, payload, { headers: getHeaders() });

    if (response.data.success) {
      Toast.fire({
        icon: 'success',
        title: 'Thêm vào giỏ thành công'
      });
      if (response.data.session_id) {
        localStorage.setItem('cart_session_id', response.data.session_id);
      }
      
      // CHUYỂN HƯỚNG SANG TRANG GIỎ HÀNG NGAY KHI THÀNH CÔNG
      router.push('/cart');
    }
  } catch (error) {
    let errorMsg = 'Hệ thống đang bận, không thể thêm vào giỏ hàng lúc này.';
    if (error.response?.data?.message) {
      errorMsg = error.response.data.message;
      if (errorMsg.includes('vượt quá tồn kho')) {
        Toast.fire({ icon: 'warning', title: 'Đã quá số lượng trong kho' });
        return;
      }
    }
    soraAlert.fire({ icon: 'error', title: 'Không thành công', text: errorMsg });
  }
};
// --- END LOGIC THÊM VÀO GIỎ ---

const goToShopWithBrand = (brandId) => {
  if (!brandId) return;
  router.push({ path: `/shop/${shopSlug}`, query: { brand: brandId } }).catch(() => {
    window.location.href = `/shop/${shopSlug}?brand=${brandId}`;
  });
};

const saveToRecentlyViewed = (prod) => {
  try {
    let viewed = JSON.parse(localStorage.getItem('viewed_products') || '[]');
    viewed = viewed.filter(p => p.id !== prod.id); 
    
    viewed.unshift({
      id: prod.id,
      slug: route.params.slug || route.params.product_slug,
      name: prod.name,
      thumbnail_image: prod.images && prod.images.length > 0 ? prod.images[0] : '',
      brand: prod.brand,
      rating_avg: prod.rating_avg,
      sold_count: prod.sold_count,
      base_price: prod.variants && prod.variants.length > 0 ? prod.variants[0].price : 0,
      promotional_price: prod.variants && prod.variants.length > 0 ? prod.variants[0].promotional_price : null
    });
    
    if (viewed.length > 12) viewed.pop(); 
    localStorage.setItem('viewed_products', JSON.stringify(viewed));
  } catch (error) {
    console.error("Lỗi lưu sản phẩm đã xem:", error);
  }
};

const fetchRecommendations = async (tab) => {
  activeTab.value = tab;
  isLoadingRecs.value = true;
  recommendedProducts.value = []; 
  
  if (tab === 'viewed') {
    try {
      const viewed = JSON.parse(localStorage.getItem('viewed_products') || '[]');
      recommendedProducts.value = viewed.filter(p => p.id !== product.value?.id);
    } catch (error) {
      console.error("Lỗi đọc sản phẩm đã xem:", error);
    } finally {
      isLoadingRecs.value = false;
    }
    return;
  }

  try {
    let url = new URL(`${API_BASE_URL}/api/shop/${shopSlug}/products`);
    url.searchParams.append('per_page', '8');
    
    if (product.value?.id) {
      url.searchParams.append('exclude_id', product.value.id);
    }

    if (tab === 'related_category' && product.value?.category?.slug) {
      url.searchParams.append('categories', product.value.category.slug);
    } else if (tab === 'related_brand' && product.value?.brand?.name) {
      url.searchParams.append('keyword', product.value.brand.name);
    } else if (tab === 'new') {
      url.searchParams.append('sort', 'new');
    }

    const response = await fetch(url.toString());
    const result = await response.json();
    
    if (result.success && result.data?.data) {
      recommendedProducts.value = result.data.data;
    }
  } catch (error) {
    console.error("Lỗi tải sản phẩm khuyến nghị:", error);
  } finally {
    isLoadingRecs.value = false;
  }
};

const changeRecTab = (tab) => {
  if (activeTab.value !== tab) {
    fetchRecommendations(tab);
  }
};

const scrollRecSlider = (direction) => {
  if (recSliderRef.value) {
    const scrollAmount = 280; 
    recSliderRef.value.scrollBy({
      left: direction === 'left' ? -scrollAmount : scrollAmount,
      behavior: 'smooth'
    });
  }
};

const goToProduct = (slug) => {
  if (!slug) return;
  router.push({ params: { ...route.params, slug: slug } }).catch(() => {
    window.location.href = `/shop/${shopSlug}/productdetail/${slug}`;
  });
};

const getImageUrl = (path) => {
  if (!path) return 'https://via.placeholder.com/300?text=No+Image';
  if (path.startsWith('http') || path.startsWith('data:')) return path;
  return `${API_BASE_URL}/storage/${path}`;
};

const formatMoney = (amount) => {
  if (!amount) return '0 ₫';
  return new Intl.NumberFormat('vi-VN').format(amount) + ' ₫';
};

const setMainImage = (url) => {
  mainImage.value = url;
};

</script>

<style scoped>
/* =========================================
   LUXURY UI & PURE CSS LAYOUT
   ========================================= */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

:root {
  --sora-primary: rgb(159,39,59);
  --sora-primary-hover: #cc1e2e;
  --sora-text: #333333;
  --sora-gray: #f8f9fa;
  --sora-border: #eaeaea;
}

.product-page {
  font-family: "Helvetica Neue", Arial, sans-serif; /* Phông chữ sạch, hiện đại */
  background-color: #ffffff; /* Nền trắng sang trọng thay vì xám nhạt */
  min-height: 100vh;
  padding: 20px 0;
  color: var(--sora-text);
}

/* Loading & Error State */
.loading-container, .error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  text-align: center;
  color: #555;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #f3f3f3;
  border-top: 3px solid var(--sora-primary); /* Đổi màu xoay sang màu đỏ mận */
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}
.small-spinner { width: 30px; height: 30px; border-width: 2px; }

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Breadcrumb - Căn lề rộng */
.breadcrumb {
  max-width: 1300px; 
  margin: 0 auto 30px auto;
  font-size: 13px;
  color: #888;
  padding: 0 20px;
}
.breadcrumb .separator { margin: 0 10px; color: #ccc; }
.breadcrumb .current { color: #333; }

/* Container */
.product-container,
.featured-lines-section,
.recommendations-section,
.product-description-section {
  max-width: 1300px;
  margin: 0 auto;
  background: #fff;
  padding: 0 20px 50px 20px;
}

/* --- GRID SẢN PHẨM 2 CỘT TẬP TRUNG HÌNH ẢNH --- */
.product-grid { 
  display: flex; 
  gap: 50px; 
  align-items: flex-start; 
}

/* CỘT 1: Hình ảnh (Khoảng 58%) */
.product-gallery { 
  display: flex; 
  gap: 15px; 
  width: 58%; 
}
.thumbnails-list { 
  display: flex; 
  flex-direction: column; 
  gap: 10px; 
  width: 75px; 
  flex-shrink: 0; 
}
.thumb-btn { 
  width: 75px; 
  height: 75px; 
  border: 1px solid transparent; 
  background: var(--sora-gray); 
  cursor: pointer; 
  overflow: hidden; 
  transition: all 0.3s ease; 
  padding: 0; 
  border-radius: 4px; /* Bo nhẹ góc */
}
.thumb-btn:hover { border-color: #ccc; }
.thumb-btn.active { border-color: var(--sora-primary); } 
.thumb-img { width: 100%; height: 100%; object-fit: cover; }

.main-image-wrapper { 
  flex-grow: 1; 
  border: none; 
  background: var(--sora-gray); 
  display: flex; 
  align-items: center; 
  justify-content: center; 
  padding: 0; 
  aspect-ratio: 1 / 1; 
  overflow: hidden;
  border-radius: 8px; /* Bo mềm mại */
}
.main-img { 
  width: 100%; 
  height: 100%; 
  object-fit: cover; 
  object-position: center;
}

/* CỘT 2: Thông tin (Khoảng 42%) */
.product-info { 
  width: 42%; 
  padding-top: 10px;
}

.product-brand-top {
  font-size: 13px;
  color: #666;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.product-brand-top .brand-name {
  color: var(--sora-text);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  cursor: pointer;
}
.product-brand-top .sku {
  color: #999;
  border-left: 1px solid #ddd;
  padding-left: 10px;
}

.product-title { 
  font-size: 26px; 
  font-weight: 500; 
  color: #222;
  margin-bottom: 20px; 
  line-height: 1.3; 
  letter-spacing: 0.5px;
}

.product-price { 
  display: flex; 
  align-items: center; 
  gap: 15px; 
  padding-bottom: 15px;
  border-bottom: 1px dotted #e5e5e5;
}
.price-current { 
  font-size: 24px; 
  font-weight: 700; 
  color: rgb(159,39,59); 
}
.price-old { 
  font-size: 16px; 
  color: #999; 
  text-decoration: line-through; 
}
.discount-badge {
  background: #fff0f2;
  color: rgb(159,39,59);
  padding: 3px 8px;
  font-size: 12px;
  font-weight: 600;
  border-radius: 4px;
}

.stock-status-luxury {
  font-size: 14px;
  font-weight: 500;
}
.in-stock { color: rgb(159,39,59); } /* Đỏ mận */
.out-of-stock { color: #999; }

.product-variants { margin-bottom: 15px; }
.variant-group { margin-bottom: 20px; }
.variant-label { 
  font-size: 13px; 
  color: #555; 
  margin-bottom: 12px; 
  font-weight: 500;
  text-transform: uppercase;
}
.variant-options { display: flex; flex-wrap: wrap; gap: 12px; }

/* Nút Option */
.variant-btn { 
  background: #f8f9fa; /* Màu xám disabled-style mặc định */
  border: 1px solid #e9ecef;
  color: #6c757d;
  padding: 10px 18px; 
  cursor: pointer; 
  font-size: 13px; 
  transition: all 0.3s ease; 
  min-width: 60px;
  text-align: center;
  border-radius: 6px; /* Bo góc nhẹ */
}
.variant-btn:hover { 
  border-color: rgba(159,39,59, 0.5); /* Viền đỏ nhẹ */
  color: rgb(159,39,59); 
  background-color: #ffffff;
}
.variant-btn.active { 
  background-color: rgb(159,39,59);
  border-color: rgb(159,39,59);
  color: #ffffff;
  box-shadow: 0 4px 10px rgba(159,39,59,0.2);
}

.action-area {
  display: flex;
  gap: 15px;
  margin-bottom: 35px;
}

/* Bộ điều chỉnh số lượng */
.quantity-selector {
  display: flex;
  border: 1px solid #eaeaea;
  height: 48px;
  border-radius: 8px;
  overflow: hidden;
  background-color: #f8f9fa;
}
.qty-btn {
  width: 40px;
  background: transparent;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: #555;
  transition: color 0.2s;
}
.qty-btn:hover { color: rgb(159,39,59); }
.qty-input {
  width: 45px;
  text-align: center;
  border: none;
  background: transparent;
  font-size: 15px;
  font-weight: 600;
  color: #333;
}
/* Ẩn mũi tên mặc định của input number */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
input[type=number] {
  -moz-appearance: textfield;
}

.action-buttons { flex: 1; display: flex; gap: 15px; }
.btn-add-cart, .btn-consult {
  flex: 1;
  height: 48px;
  border: none;
  font-size: 14px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  cursor: pointer;
  border-radius: 8px; /* Bo góc mềm mại */
  transition: all 0.3s ease;
}

.btn-add-cart { 
  background-color: transparent; 
  color: rgb(159,39,59); 
  border: 1px solid rgb(159,39,59); 
}
.btn-add-cart:hover { 
  background-color: rgb(159,39,59); 
  color: #fff; 
  box-shadow: 0 4px 15px rgba(159,39,59,0.3);
}

.btn-consult {
  background-color: #fff;
  border: 1px solid #333;
  color: #333;
}
.btn-consult:hover { background-color: #333; color: #fff; }


/* Phần mô tả ngắn & Ghi chú */
.product-short-desc {
  border-top: 1px dotted #e5e5e5;
  padding-top: 25px;
  font-size: 13px;
  line-height: 1.6;
  color: #555;
}
.product-short-desc p { margin-bottom: 20px; }
.notes h4 {
  font-size: 12px;
  color: #222;
  margin-bottom: 10px;
  letter-spacing: 0.5px;
}
.notes ul {
  list-style: none;
  padding-left: 0;
}
.notes li {
  position: relative;
  padding-left: 12px;
  margin-bottom: 8px;
}
.notes li::before {
  content: '•';
  position: absolute;
  left: 0;
  color: rgb(159,39,59);
}

/* =========================================
   DÒNG HÀNG NỔI BẬT 
   ========================================= */
.featured-lines-section {
  padding-top: 30px;
  border-top: 1px solid #eee;
}

.text-center { text-align: center; }

.featured-lines-section .section-title {
  font-size: 22px;
  font-weight: 600;
  color: #222; 
  margin-bottom: 30px;
  text-transform: uppercase;
  letter-spacing: 2px;
}

.featured-lines-container {
  display: flex;
  gap: 40px;
  align-items: center; 
}

.featured-banner {
  width: 50%;
  overflow: hidden;
  aspect-ratio: 4 / 3; 
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 8px;
}
.featured-banner img {
  width: 100%;
  height: 100%;
  object-fit: cover; 
}

.featured-content {
  width: 50%;
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.featured-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.f-tag-btn {
  background: transparent;
  border: 1px solid #ccc;
  color: #555;
  padding: 10px 20px;
  font-size: 13px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 1px;
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 50px; /* Nhấn kiểu badge sang trọng */
}

.f-tag-btn:hover, .f-tag-btn:first-child {
  background: #333; 
  color: #fff;
  border-color: #333;
}

.featured-desc p {
  font-size: 14px;
  line-height: 1.8;
  color: #555;
  margin-bottom: 15px;
  text-align: justify;
}

/* =========================================
   CÓ THỂ BẠN SẼ THÍCH 
   ========================================= */
.recommendations-section { padding-top: 40px; border-top: 1px solid #eee;}
.recommendations-header { display: flex; justify-content: space-between; align-items: flex-end; padding-bottom: 20px; margin-bottom: 20px; }
.rec-title-wrap { display: flex; flex-direction: column; gap: 8px; }
.rec-title { font-size: 18px; font-weight: 600; color: #222; letter-spacing: 1px; }
.rec-subtitle { font-size: 13px; color: #777; }
.rec-controls { display: flex; align-items: center; gap: 24px; }
.rec-tabs { display: flex; gap: 25px; }
.rec-tabs button { background: none; border: none; font-size: 14px; font-weight: 500; color: #888; cursor: pointer; padding-bottom: 10px; border-bottom: 2px solid transparent; transition: all 0.2s; }
.rec-tabs button:hover { color: rgb(159,39,59); }
.rec-tabs button.active { color: rgb(159,39,59); border-bottom-color: rgb(159,39,59); }
.rec-arrows { display: flex; gap: 8px; }
.arrow-btn { width: 36px; height: 36px; border: 1px solid #ddd; background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #555; transition: all 0.2s; border-radius: 50%; }
.arrow-btn:hover { background: rgb(159,39,59); color: #fff; border-color: rgb(159,39,59); }
.rec-loading, .rec-empty { min-height: 250px; display: flex; align-items: center; justify-content: center; color: #777; }
.rec-slider-container { display: flex; gap: 20px; overflow-x: auto; scroll-behavior: smooth; padding-bottom: 15px; -ms-overflow-style: none; scrollbar-width: none; }
.rec-slider-container::-webkit-scrollbar { display: none; }
.rec-product-card { min-width: 240px; max-width: 240px; background: #fff; position: relative; transition: transform 0.3s; display: flex; flex-direction: column; cursor: pointer; border: 1px solid transparent; border-radius: 8px;}
.rec-product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
.wishlist-float-btn { position: absolute; top: 15px; right: 15px; background: transparent; border: none; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color: #ccc; cursor: pointer; z-index: 2; transition: all 0.2s; }
.wishlist-float-btn:hover { color: rgb(159,39,59); }
.rec-img-wrap { width: 100%; aspect-ratio: 1 / 1; background: #f9f9f9; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 15px; border-radius: 8px 8px 0 0;}
.rec-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;}
.rec-product-card:hover .rec-img-wrap img { transform: scale(1.05); }
.rec-card-body { padding: 0 5px 15px 5px; display: flex; flex-direction: column; align-items: center; text-align: center; }
.rec-brand { font-size: 11px; color: #999; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 1px;}
.rec-name { font-size: 14px; font-weight: 500; color: #333; margin-bottom: 10px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.rec-price { font-size: 15px; font-weight: 600; color: rgb(159,39,59); }

/* ========================================= */
.product-description-section { padding-top: 40px; border-top: 1px solid #eee; margin-top: 20px;}
.section-title { font-size: 18px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 25px; }
.description-content { line-height: 1.8; color: #555; font-size: 15px; }

/* Responsive cơ bản */
@media (max-width: 1024px) {
  .product-grid { flex-wrap: wrap; }
  .product-gallery { width: 100%; }
  .product-info { width: 100%; }
  .featured-lines-container { flex-direction: column; }
  .featured-banner, .featured-content { width: 100%; }
}

@media (max-width: 768px) {
  .product-gallery { flex-direction: column-reverse; }
  .thumbnails-list { flex-direction: row; width: 100%; overflow-x: auto; }
  .thumb-btn { width: 60px; height: 60px; }
  .recommendations-header { flex-direction: column; align-items: flex-start; gap: 16px; }
  .rec-controls { width: 100%; justify-content: space-between; }
  .rec-tabs { overflow-x: auto; white-space: nowrap; padding-bottom: 2px; }
  .action-area { flex-wrap: wrap; }
  .quantity-selector { width: 100%; justify-content: center; }
}
</style>