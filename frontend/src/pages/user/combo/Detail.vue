<template>
  <div class="combo-detail-page bg-light-custom pb-5" v-if="combo">
    
    <div class="bg-transparent pt-4 pb-2">
      <div class="container">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 font-oswald text-uppercase tracking-wide small" style="font-size: 0.75rem;">
            <li class="breadcrumb-item"><router-link to="/" class="text-muted text-decoration-none hover-primary">Trang chủ</router-link></li>
            <li class="breadcrumb-item"><router-link :to="{ name: 'client-combos' }" class="text-muted text-decoration-none hover-primary">Gói Ưu Đãi</router-link></li>
            <li class="breadcrumb-item active fw-bold text-sora-primary" aria-current="page">{{ combo.name }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container pt-4">
      <div class="row g-0 g-lg-5 mb-5 pb-5">
        
        <!-- BÊN TRÁI: ẢNH ĐẠI DIỆN COMBO -->
        <div class="col-lg-6 mb-4 mb-lg-0">
          <div class="sticky-top" style="top: 100px; z-index: 1;">
            <div class="luxury-image-wrapper position-relative overflow-hidden cursor-zoom-in" @click="viewFullImage(getImage(combo.thumbnail_image))">
              
              <div class="position-absolute top-0 start-0 z-index-2 mt-4 ms-4">
                <div class="luxury-badge bg-sora-primary text-white font-oswald tracking-widest px-3 py-2 text-uppercase shadow-sm">
                  Giảm {{ combo.discount_type === 'percentage' ? combo.discount_value + '%' : formatCurrency(combo.discount_value) }}
                </div>
              </div>

              <div v-if="getTimerData(combo).isEnded" class="ended-overlay d-flex align-items-center justify-content-center flex-column text-center p-4">
                  <h3 class="text-white font-oswald tracking-widest m-0 text-uppercase" style="letter-spacing: 4px;">{{ getTimerData(combo).title }}</h3>
                  <div class="mt-3 bg-white" style="width: 40px; height: 1px;"></div>
              </div>

              <img :src="getImage(combo.thumbnail_image)" class="w-100 object-fit-cover img-zoom-hover bg-white" style="height: auto; min-height: 600px; max-height: 80vh;" :class="{'opacity-75 grayscale': getTimerData(combo).isEnded}">
              
              <div class="position-absolute bottom-0 end-0 m-4 z-index-2 text-muted small fw-light fst-italic bg-white px-3 py-2 rounded-pill shadow-sm" style="opacity: 0.8; font-size: 0.75rem;">
                <i class="bi bi-arrows-fullscreen me-1"></i> Nhấp để xem chi tiết
              </div>
            </div>
          </div>
        </div>

        <!-- BÊN PHẢI: THÔNG TIN VÀ CHỌN LỰA -->
        <div class="col-lg-6">
          <div class="ps-lg-4 pt-2">
            
            <div class="d-flex align-items-center gap-3 mb-3 text-uppercase font-oswald tracking-widest small">
              <span class="text-gold fw-medium"><i class="bi bi-stars me-1"></i> Bộ Sưu Tập {{ combo.items.length }} Món</span>
              <span v-if="combo.theme" class="text-muted border-start ps-3 border-secondary-subtle">{{ combo.theme }}</span>
            </div>
            
            <h1 class="display-4 fw-bold text-dark mb-4 font-serif" style="line-height: 1.15; letter-spacing: -0.5px;">{{ combo.name }}</h1>
            <p class="text-muted fs-6 mb-5 lh-lg fw-light" style="font-family: 'Arial', sans-serif;">{{ combo.description }}</p>

            <!-- BỘ ĐẾM THỜI GIAN -->
            <div class="luxury-timer-section mb-5 py-3 border-top border-bottom border-gold-light" v-if="getTimerData(combo).type !== 'forever'">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                  <div class="d-flex align-items-center gap-2">
                    <div class="pulsing-dot" :class="getTimerData(combo).type === 'active' ? 'bg-sora-red' : 'bg-warning'" v-if="!getTimerData(combo).isEnded"></div>
                    <span class="text-muted font-oswald tracking-wide text-uppercase small fw-medium">
                      {{ getTimerData(combo).title }}
                    </span>
                  </div>
                  
                  <div v-if="!getTimerData(combo).isEnded" class="d-flex gap-2 align-items-baseline font-oswald text-dark fs-4">
                      <span>{{ getTimerData(combo).d }}</span><span class="fs-6 text-muted mx-1 fw-light">Ngày</span>
                      <span class="text-gold fw-light mx-1">:</span>
                      <span>{{ getTimerData(combo).h }}</span><span class="fs-6 text-muted mx-1 fw-light">Giờ</span>
                      <span class="text-gold fw-light mx-1">:</span>
                      <span>{{ getTimerData(combo).m }}</span><span class="fs-6 text-muted mx-1 fw-light">Phút</span>
                      <span class="text-gold fw-light mx-1">:</span>
                      <span class="text-sora-red">{{ getTimerData(combo).s }}</span><span class="fs-6 text-sora-red mx-1 fw-light">Giây</span>
                  </div>
                </div>
            </div>

            <!-- DANH SÁCH MÓN & CHỌN PHÂN LOẠI -->
            <div class="combo-items-editorial mb-5">
              <h5 class="fw-bold text-dark mb-4 font-serif fs-4">Định Hình Phong Cách</h5>
              
              <div class="editorial-item mb-4 pb-4 border-bottom border-light-subtle" v-for="(item, index) in combo.items" :key="item.id">
                <div class="d-flex gap-4">
                  <!-- ẢNH ĐẠI DIỆN MÓN (TỰ ĐỘNG ĐỔI THEO BIẾN THỂ KHI CHỌN ĐỦ THUỘC TÍNH) -->
                  <div class="position-relative flex-shrink-0 cursor-zoom-in" @click="viewFullImage(getDisplayImage(item))">
                    <img :src="getDisplayImage(item)" class="object-fit-cover bg-white shadow-sm transition-all" style="width: 80px; height: 80px; border-radius: 2px;">
                    <div class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-sora-primary text-white px-2 py-1 shadow-sm" style="font-family: 'Oswald', sans-serif;">x{{ item.quantity }}</div>
                  </div>
                  
                  <div class="flex-grow-1">
                    <h6 class="fw-bold mb-1 text-dark font-serif fs-5">{{ item.product?.name }}</h6>
                    
                    <!-- NẾU ADMIN ĐÃ CỐ ĐỊNH PHIÊN BẢN -->
                    <div v-if="item.product_variant_id" class="text-muted small mt-2 font-oswald tracking-wide text-uppercase">
                      Phiên bản cố định: 
                      <span class="text-sora-primary fw-bold" v-if="item.variant?.formatted_attributes">
                        {{ Object.values(item.variant.formatted_attributes).join(' - ') }}
                      </span>
                      <span class="text-sora-primary fw-bold" v-else>{{ item.variant?.sku }}</span>
                    </div>

                    <!-- THIẾT KẾ MA TRẬN CHỌN THUỘC TÍNH TÙY CHỈNH -->
                    <div v-else class="mt-3">
                      <template v-if="itemMatrices[item.id]">
                        <div v-for="(values, attrName) in itemMatrices[item.id]" :key="attrName" class="mb-3">
                          <p class="text-dark font-serif fw-bold mb-2 fs-6">
                            {{ attrName }}: <span class="fw-normal font-oswald text-muted ms-1">{{ userSelections[item.id][attrName] || '' }}</span>
                          </p>
                          
                          <div class="d-flex flex-wrap gap-2">
                            <label v-for="val in values" :key="val" 
                                   class="attr-chip m-0 cursor-pointer transition-all"
                                   :class="{'selected': userSelections[item.id][attrName] === val, 'error': validationErrors[item.id]}">
                              
                              <input type="radio" class="d-none" :name="`attr_${item.id}_${attrName}`" :value="val" v-model="userSelections[item.id][attrName]" @change="validationErrors[item.id] = false">
                              
                              <div class="chip-inner px-3 py-2 d-flex flex-column align-items-center justify-content-center text-center shadow-sm">
                                <span class="fw-bold font-oswald tracking-wide">{{ val }}</span>
                              </div>
                            </label>
                          </div>
                        </div>
                      </template>

                      <!-- Báo Lỗi: Chưa chọn đủ -->
                      <div class="text-danger small mt-2 fst-italic" v-if="validationErrors[item.id]">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Vui lòng định hình phong cách cho thiết kế này.
                      </div>
                      
                      <!-- Báo Lỗi: Chọn tổ hợp không có thật -->
                      <div class="text-danger small mt-2 fst-italic fw-bold" v-else-if="!getSelectedVariant(item.id) && isAllAttributesSelected(item.id)">
                        <i class="bi bi-x-circle-fill me-1"></i> Phiên bản này đã hết hàng hoặc không tồn tại.
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>

            <!-- TỔNG KẾT GIÁ TRỊ -->
            <div class="luxury-price-summary mb-5 p-4 bg-white border border-gold-light" style="border-radius: 2px;">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-light-subtle">
                  <span class="text-muted font-oswald text-uppercase tracking-wide">Giá Trị Gốc</span>
                  <span class="text-muted text-decoration-line-through fs-5 font-serif">{{ formatCurrency(originalTotal) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <span class="fw-bold text-dark font-oswald text-uppercase tracking-wide fs-5">Mức Giá Ưu Đãi</span>
                  <span class="fw-bold text-sora-primary display-5 font-serif">{{ formatCurrency(finalPrice) }}</span>
                </div>
                <div class="text-end">
                  <span class="font-oswald text-gold tracking-widest text-uppercase small fw-bold">
                    Tiết Kiệm Lên Đến {{ savingsPercentage }}%
                  </span>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div v-if="!canBuyCombo" class="text-center py-4 bg-light border">
              <span class="font-oswald tracking-widest text-uppercase text-muted fs-5">
                <template v-if="getTimerData(combo).type === 'upcoming'">
                   Gói ưu đãi chưa mở bán
                </template>
                <template v-else-if="getTimerData(combo).type === 'soldout'">
                   Đã bán hết số lượng
                </template>
                <template v-else>
                   Gói ưu đãi đã khép lại
                </template>
              </span>
            </div>
            
            <div v-else class="row g-3">
              <div class="col-sm-6">
                <button class="btn luxury-btn-outline w-100 py-3 font-oswald tracking-widest text-uppercase" @click="addToCart">
                  <span v-if="isAddingToCart" class="spinner-border spinner-border-sm me-2"></span>
                  Thêm Vào Giỏ
                </button>
              </div>
              <div class="col-sm-6">
                <button class="btn luxury-btn-solid w-100 py-3 font-oswald tracking-widest text-uppercase" @click="buyNow">
                  Sở Hữu Ngay
                </button>
              </div>
            </div>

            <!-- CAM KẾT -->
            <div class="d-flex justify-content-between mt-5 pt-4 border-top border-light-subtle opacity-75">
              <div class="text-center">
                <i class="bi bi-truck fs-4 text-dark mb-1 d-block"></i>
                <span class="font-oswald text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">Giao Hàng<br>Miễn Phí</span>
              </div>
              <div class="text-center">
                <i class="bi bi-arrow-repeat fs-4 text-dark mb-1 d-block"></i>
                <span class="font-oswald text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">Đổi Trả<br>Dễ Dàng</span>
              </div>
              <div class="text-center">
                <i class="bi bi-shield-check fs-4 text-dark mb-1 d-block"></i>
                <span class="font-oswald text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">Bảo Hành<br>Trọn Đời</span>
              </div>
              <div class="text-center">
                <i class="bi bi-gem fs-4 text-dark mb-1 d-block"></i>
                <span class="font-oswald text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">Chất Lượng<br>Đỉnh Cao</span>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div v-else class="vh-100 d-flex justify-content-center align-items-center bg-light-custom">
    <div class="spinner-grow text-gold" style="width: 3rem; height: 3rem;" role="status"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const combo = ref(null);

const itemMatrices = ref({}); 
const userSelections = ref({}); 
const validationErrors = ref({}); 
const isAddingToCart = ref(false);

const currentTime = ref(new Date());
let timerInterval = null;

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(val || 0);
const getImage = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : 'https://placehold.co/400x300';

const getDisplayImage = (item) => {
    if (item.product_variant_id && item.variant && item.variant.image_url) {
        return getImage(item.variant.image_url);
    }
    if (!item.product_variant_id) {
        const selectedVar = getSelectedVariant(item.id);
        if (selectedVar && selectedVar.image_url) {
            return getImage(selectedVar.image_url);
        }
    }
    return getImage(item.product?.thumbnail_image);
};

const viewFullImage = (url) => {
  Swal.fire({
    imageUrl: url, 
    imageAlt: 'Product Image', 
    width: 600, 
    imageHeight: 600, 
    padding: 0, 
    background: 'transparent',
    backdrop: 'rgba(0,0,0,0.85)', 
    showConfirmButton: false, 
    showCloseButton: true,
    customClass: { 
        image: 'rounded-3 shadow-lg object-fit-contain bg-white',
        popup: 'p-0 bg-transparent'
    }
  });
};

const getSelectedVariant = (itemId) => {
    const item = combo.value.items.find(i => i.id === itemId);
    if (!item || item.product_variant_id) return item?.variant;
    
    const selections = userSelections.value[itemId];
    if (!selections) return null;
    
    const requiredAttrs = Object.keys(itemMatrices.value[itemId] || {});
    if (requiredAttrs.length === 0) return null;

    const hasAllAttrs = requiredAttrs.every(attr => selections[attr]);
    if (!hasAllAttrs) return null;
    
    return item.product.variants.find(v => {
        return requiredAttrs.every(attr => v.formatted_attributes && v.formatted_attributes[attr] === selections[attr]);
    });
};

const isAllAttributesSelected = (itemId) => {
    const selections = userSelections.value[itemId];
    if (!selections) return false;
    const requiredAttrs = Object.keys(itemMatrices.value[itemId] || {});
    return requiredAttrs.every(attr => selections[attr]);
};

const originalTotal = computed(() => {
  if (!combo.value) return 0;
  return combo.value.items.reduce((total, item) => {
    let price = 0;
    if (item.product_variant_id && item.variant) {
      price = item.variant.price; 
    } else {
      const selectedVar = getSelectedVariant(item.id);
      price = selectedVar ? selectedVar.price : (item.product ? item.product.base_price : 0);
    }
    return total + (parseFloat(price) * item.quantity);
  }, 0);
});

const finalPrice = computed(() => {
  if (!combo.value) return 0;
  let total = originalTotal.value;
  let discount = parseFloat(combo.value.discount_value);
  if (combo.value.discount_type === 'percentage') return total - (total * (Math.min(discount, 100) / 100));
  return Math.max(0, total - discount);
});

const savingsPercentage = computed(() => {
  if (originalTotal.value === 0) return 0;
  const savings = originalTotal.value - finalPrice.value;
  return Math.round((savings / originalTotal.value) * 100);
});

const parseDBDate = (dateStr) => {
    if (!dateStr) return null;
    return new Date(dateStr.replace(' ', 'T').substring(0, 19)).getTime();
};

const calculateTimeParts = (diff) => {
  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
  const minutes = Math.floor((diff / 1000 / 60) % 60);
  const seconds = Math.floor((diff / 1000) % 60);
  return {
    d: days.toString().padStart(2, '0'), h: hours.toString().padStart(2, '0'),
    m: minutes.toString().padStart(2, '0'), s: seconds.toString().padStart(2, '0')
  };
};

const getTimerData = (combo) => {
    if (!combo) return { type: 'forever', title: '', isEnded: false };
    const now = currentTime.value.getTime();
    
    if (combo.usage_limit !== null && combo.usage_limit <= 0) return { type: 'soldout', title: 'ĐÃ BÁN HẾT SỐ LƯỢNG', isEnded: true };

    const startTime = parseDBDate(combo.start_date);
    const endTime = parseDBDate(combo.end_date);

    if (endTime && endTime < now) return { type: 'ended', title: 'ƯU ĐÃI ĐÃ KẾT THÚC', isEnded: true };
    if (startTime && startTime > now) return { type: 'upcoming', title: 'MỞ BÁN SAU', isEnded: false, ...calculateTimeParts(startTime - now) };
    if (endTime && endTime >= now) return { type: 'active', title: 'KẾT THÚC TRONG', isEnded: false, ...calculateTimeParts(endTime - now) };
    
    return { type: 'forever', title: '', isEnded: false };
};

const canBuyCombo = computed(() => {
    if (!combo.value) return false;
    const timer = getTimerData(combo.value);
    return timer.type === 'active' || timer.type === 'forever';
});

const selectVariant = (itemId, variantId) => {
    validationErrors.value[itemId] = false; 
};

const fetchDetail = async (slug) => {
  combo.value = null; 
  try {
    const res = await axios.get(`http://127.0.0.1:8000/api/client/combos/${slug}`);
    combo.value = res.data.data;
    userSelections.value = {}; validationErrors.value = {}; itemMatrices.value = {};

    combo.value.items.forEach(item => {
      if (!item.product_variant_id) {
        userSelections.value[item.id] = {}; 
        
        const matrix = {};
        if (item.product?.variants) {
          item.product.variants.forEach(variant => {
            if (variant.formatted_attributes) {
              Object.entries(variant.formatted_attributes).forEach(([attrName, attrValue]) => {
                if (!matrix[attrName]) matrix[attrName] = new Set();
                matrix[attrName].add(attrValue);
              });
            }
          });
        }
        
        const finalMatrix = {};
        Object.keys(matrix).forEach(key => {
          finalMatrix[key] = Array.from(matrix[key]);
        });
        
        itemMatrices.value[item.id] = finalMatrix;

        // ĐÃ THÊM: TỰ ĐỘNG CHỌN NẾU SẢN PHẨM CHỈ CÓ DUY NHẤT 1 BIẾN THỂ
        if (item.product?.variants && item.product.variants.length === 1) {
            const singleVariant = item.product.variants[0];
            if (singleVariant.formatted_attributes) {
                Object.entries(singleVariant.formatted_attributes).forEach(([attrName, attrValue]) => {
                    userSelections.value[item.id][attrName] = attrValue;
                });
            }
        }
      }
    });
  } catch (error) {
    Swal.fire('Lỗi', 'Combo không tồn tại hoặc đã hết hạn!', 'error').then(() => router.push({name: 'client-combos'}));
  }
};

const validateSelections = () => {
  let isValid = true;
  validationErrors.value = {};
  
  combo.value.items.forEach(item => {
    if (!item.product_variant_id) {
      const selectedVar = getSelectedVariant(item.id);
      if (!selectedVar) {
        validationErrors.value[item.id] = true;
        isValid = false;
      } else {
        validationErrors.value[item.id] = false;
      }
    }
  });
  return isValid;
};

const preparePayload = () => {
  const customSelections = combo.value.items.filter(i => !i.product_variant_id).map(item => {
    const selectedVar = getSelectedVariant(item.id);
    return {
      combo_item_id: parseInt(item.id),
      selected_variant_id: selectedVar ? selectedVar.id : null 
    };
  });
  
  return { 
      combo_id: combo.value.id, 
      quantity: 1, 
      combo_selections: customSelections 
  };
};

const addToCart = async () => {
  if (!validateSelections()) {
    Swal.fire({ toast:true, position: 'top', icon:'warning', title: 'Vui lòng chọn đầy đủ phiên bản!', showConfirmButton: false, timer: 2000 });
    return;
  }
  
  isAddingToCart.value = true;
  const payload = preparePayload();
  
  try {
      const token = localStorage.getItem('auth_token'); 
      const sessionId = localStorage.getItem('cart_session_id'); 
      
      const headers = { 'Accept': 'application/json' };
      if (token) headers['Authorization'] = `Bearer ${token}`;
      if (sessionId) headers['X-Cart-Session-Id'] = sessionId;

      const res = await axios.post('http://127.0.0.1:8000/api/client/cart/add-combo', payload, { headers });
      
      if (res.data.session_id) {
          localStorage.setItem('cart_session_id', res.data.session_id);
      }

      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã thêm Combo vào Túi mua sắm', showConfirmButton: false, timer: 1500 });
      
  } catch (error) {
      console.error(error);
      const msg = error.response?.data?.message || 'Không thể thêm vào giỏ hàng. Vui lòng thử lại!';
      Swal.fire('Lỗi', msg, 'error');
  } finally {
      isAddingToCart.value = false;
  }
};

const buyNow = () => {
  if (!validateSelections()) {
    Swal.fire({ toast:true, position: 'top', icon:'warning', title: 'Vui lòng chọn đầy đủ phiên bản!', showConfirmButton: false, timer: 2000 });
    return;
  }
  
  const payload = preparePayload();
  localStorage.setItem('checkout_combo_direct', JSON.stringify(payload));
  
  Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Chuyển hướng đến Thanh toán...', showConfirmButton: false, timer: 1000 }).then(() => {
     console.log("Đang chuyển tới Checkout với data:", payload);
  });
};

watch(() => route.params.slug, (newSlug) => {
    if(newSlug && route.name === 'client-combo-detail') fetchDetail(newSlug);
});

onMounted(() => {
    fetchDetail(route.params.slug);
    timerInterval = setInterval(() => { currentTime.value = new Date(); }, 1000);
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
    document.querySelectorAll('.swal2-container').forEach(el => el.remove());
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap');

.bg-light-custom { background-color: #faf9f6; min-height: 100vh; }
.font-serif { font-family: 'Playfair Display', serif; }
.font-oswald { font-family: 'Oswald', sans-serif; }
.tracking-wide { letter-spacing: 1px; }
.tracking-widest { letter-spacing: 2px; }
.z-index-2 { z-index: 2; }
.transition-color { transition: color 0.3s ease; }
.transition-all { transition: all 0.3s ease; }
.cursor-pointer { cursor: pointer; }
.cursor-zoom-in { cursor: zoom-in; }

/* FIX CSS LỖI VUE SCOPED: GỠ :ROOT VÀ FIX CỨNG MÃ HEXA */
.text-sora-primary { color: #9f273b !important; }
.text-sora-red { color: #cc1e2e !important; }
.text-gold { color: #e7ce7d !important; }
.bg-sora-primary { background-color: #9f273b !important; }
.border-gold-light { border-color: rgba(231, 206, 125, 0.4) !important; }

.hover-primary:hover { color: #9f273b !important; }

.luxury-image-wrapper { border-radius: 4px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); background: #fff; }
.img-zoom-hover { transition: transform 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94); }
.luxury-image-wrapper:hover .img-zoom-hover { transform: scale(1.05); }
.grayscale { filter: grayscale(100%); }
.ended-overlay { position: absolute; inset: 0; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(2px); z-index: 10; }
.luxury-badge { letter-spacing: 2px; font-size: 0.85rem; }

.pulsing-dot { width: 8px; height: 8px; border-radius: 50%; animation: pulse 1.5s infinite; }
@keyframes pulse { 0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(204, 30, 46, 0.7); } 70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(204, 30, 46, 0); } 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(204, 30, 46, 0); } }

/* ================= CUSTOM VARIANT CHIP TÔNG MÀU SORA (9f273b / e7ce7d) ================= */
.attr-chip {
    border-radius: 4px;
    overflow: hidden;
    min-width: 55px;
}
.attr-chip .chip-inner {
    border: 1px solid #dee2e6;
    background-color: #fff;
    color: #555;
    border-radius: 4px;
    transition: all 0.3s ease-in-out;
    padding: 8px 16px;
}
.attr-chip:hover .chip-inner {
    border-color: #e7ce7d; /* Viền Vàng Gold */
    color: #9f273b;
}
.attr-chip.selected .chip-inner {
    background-color: #9f273b; /* Đỏ Sẫm */
    border-color: #9f273b;
    color: #fff !important;
    box-shadow: 0 4px 10px rgba(159, 39, 59, 0.25);
}
.attr-chip.selected .chip-inner span {
    color: #fff !important;
}
.attr-chip.error .chip-inner {
    border-color: #dc3545;
    color: #dc3545;
    background-color: rgba(220, 53, 69, 0.05);
    animation: shake 0.4s;
}

@keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-4px); } 50% { transform: translateX(4px); } 75% { transform: translateX(-4px); } }

.luxury-btn-solid { background-color: #9f273b; color: white; border: 1px solid #9f273b; transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); }
.luxury-btn-solid:hover { background-color: #7a1c2d; border-color: #7a1c2d; color: white; box-shadow: 0 8px 20px rgba(159,39,59,0.3); transform: translateY(-2px); }
.luxury-btn-outline { border: 1px solid #9f273b; color: #9f273b; background: transparent; transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); }
.luxury-btn-outline:hover { background: #9f273b; color: white; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(159,39,59,0.2); }
</style>