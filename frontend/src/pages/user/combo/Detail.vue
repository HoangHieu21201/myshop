<template>
  <div class="combo-detail-page bg-light-custom pb-5" v-if="combo">
    
    <!-- BREADCRUMB LUXURY -->
    <div class="bg-transparent pt-4 pb-2">
      <div class="container">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 font-oswald text-uppercase tracking-wide small" style="font-size: 0.75rem;">
            <li class="breadcrumb-item"><router-link to="/" class="text-muted text-decoration-none hover-primary">Trang chủ</router-link></li>
            <li class="breadcrumb-item"><router-link :to="{ name: 'client-combos' }" class="text-muted text-decoration-none hover-primary">Bộ sưu tập</router-link></li>
            <li class="breadcrumb-item active fw-bold text-sora-primary" aria-current="page">{{ combo.name }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container pt-4">
      <!-- MAIN COMBO SECTION -->
      <div class="row g-0 g-lg-5 mb-5 pb-5">
        
        <!-- BÊN TRÁI: ẢNH SẢN PHẨM (EDITORIAL STYLE) -->
        <div class="col-lg-6 mb-4 mb-lg-0">
          <div class="sticky-top" style="top: 100px; z-index: 1;">
            <div class="luxury-image-wrapper position-relative overflow-hidden cursor-zoom-in" @click="viewFullImage(getImage(combo.thumbnail_image))">
              
              <!-- Badge Giá Giảm Thiết Kế Lại -->
              <div class="position-absolute top-0 start-0 z-index-2 mt-4 ms-4">
                <div class="luxury-badge bg-sora-primary text-white font-oswald tracking-widest px-3 py-2 text-uppercase shadow-sm">
                  Giảm {{ combo.discount_type === 'percentage' ? combo.discount_value + '%' : formatCurrency(combo.discount_value) }}
                </div>
              </div>

              <!-- Lớp phủ báo hết hạn -->
              <div v-if="getTimerData(combo).isEnded" class="ended-overlay d-flex align-items-center justify-content-center flex-column text-center p-4">
                  <h3 class="text-white font-oswald tracking-widest m-0 text-uppercase" style="letter-spacing: 4px;">{{ getTimerData(combo).title }}</h3>
                  <div class="mt-3 bg-white" style="width: 40px; height: 1px;"></div>
              </div>

              <img :src="getImage(combo.thumbnail_image)" class="w-100 object-fit-cover img-zoom-hover" style="height: auto; min-height: 600px; max-height: 80vh;" :class="{'opacity-75 grayscale': getTimerData(combo).isEnded}">
              
              <!-- Hint phóng to -->
              <div class="position-absolute bottom-0 end-0 m-4 z-index-2 text-muted small fw-light fst-italic bg-white px-3 py-2 rounded-pill shadow-sm" style="opacity: 0.8; font-size: 0.75rem;">
                <i class="bi bi-arrows-fullscreen me-1"></i> Nhấp để xem chi tiết
              </div>
            </div>
          </div>
        </div>

        <!-- BÊN PHẢI: THÔNG TIN TỐI GIẢN & SANG TRỌNG -->
        <div class="col-lg-6">
          <div class="ps-lg-4 pt-2">
            
            <div class="d-flex align-items-center gap-3 mb-3 text-uppercase font-oswald tracking-widest small">
              <span class="text-gold fw-medium"><i class="bi bi-stars me-1"></i> Bộ Sưu Tập {{ combo.items.length }} Món</span>
              <span v-if="combo.theme" class="text-muted border-start ps-3 border-secondary-subtle">{{ combo.theme }}</span>
            </div>
            
            <h1 class="display-4 fw-bold text-dark mb-4 font-serif" style="line-height: 1.15; letter-spacing: -0.5px;">{{ combo.name }}</h1>
            <p class="text-muted fs-6 mb-5 lh-lg fw-light" style="font-family: 'Arial', sans-serif;">{{ combo.description }}</p>

            <!-- BỘ ĐẾM THỜI GIAN (TYPOGRAPHY MINIMALIST) -->
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

            <!-- DANH SÁCH MÓN & CHỌN RADIO XỊN XÒ -->
            <div class="combo-items-editorial mb-5">
              <h5 class="fw-bold text-dark mb-4 font-serif fs-4">Định Hình Phong Cách</h5>
              
              <div class="editorial-item mb-4 pb-4 border-bottom border-light-subtle" v-for="(item, index) in combo.items" :key="item.id">
                <div class="d-flex gap-4">
                  <!-- Ảnh sản phẩm nhỏ gọn -->
                  <div class="position-relative flex-shrink-0 cursor-zoom-in" @click="viewFullImage(getImage(item.product?.thumbnail_image))">
                    <img :src="getImage(item.product?.thumbnail_image)" class="object-fit-cover bg-white shadow-sm" style="width: 80px; height: 80px; border-radius: 2px;">
                    <div class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark text-white border border-white px-2 py-1 shadow-sm" style="font-family: 'Oswald', sans-serif;">x{{ item.quantity }}</div>
                  </div>
                  
                  <div class="flex-grow-1">
                    <h6 class="fw-bold mb-1 text-dark font-serif fs-5">{{ item.product?.name }}</h6>
                    
                    <!-- Admin ép cấu hình -->
                    <div v-if="item.product_variant_id" class="text-muted small mt-2 font-oswald tracking-wide text-uppercase">
                      Phiên bản: <span class="text-dark fw-bold">{{ item.variant?.sku }}</span>
                    </div>

                    <!-- Khách chọn: RADIO LUXURY -->
                    <div v-else class="mt-3">
                      <p class="small text-muted font-oswald tracking-wide text-uppercase mb-2">Chọn phiên bản:</p>
                      
                      <div class="d-flex flex-wrap gap-2">
                        <label v-for="v in item.product?.variants" :key="v.id" 
                               class="custom-radio-chip m-0"
                               :class="{'selected': userSelections[item.id] === v.id, 'error': validationErrors[item.id]}">
                          <input type="radio" class="d-none" :name="`item_${item.id}`" :value="v.id" v-model="userSelections[item.id]" @change="selectVariant(item.id, v.id)">
                          
                          <span class="chip-content d-flex flex-column align-items-center justify-content-center">
                            <span class="fw-medium font-oswald tracking-wide">{{ v.sku }}</span>
                            <span class="small price-diff" v-if="v.price > item.product.base_price">+ {{ formatCurrency(v.price - item.product.base_price) }}</span>
                          </span>
                        </label>
                      </div>
                      <div class="text-danger small mt-2 fst-italic" v-if="validationErrors[item.id]">
                        Vui lòng lựa chọn phong cách cho tác phẩm này.
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- TỔNG KẾT GIÁ TRỊ TỐI GIẢN -->
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
            <div v-if="!combo.can_buy" class="text-center py-4 bg-light border">
              <span class="font-oswald tracking-widest text-uppercase text-muted fs-5">HÃY CHÚ Ý THEO DÕI</span>
            </div>
            
            <div v-else class="row g-3">
              <div class="col-sm-6">
                <button class="btn luxury-btn-outline w-100 py-3 font-oswald tracking-widest text-uppercase" @click="addToCart">
                  Thêm Vào Giỏ
                </button>
              </div>
              <div class="col-sm-6">
                <button class="btn luxury-btn-solid w-100 py-3 font-oswald tracking-widest text-uppercase" @click="buyNow">
                  Sở Hữu Ngay
                </button>
              </div>
            </div>

            <!-- CAM KẾT (ICONS NHỎ GỌN) -->
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

const userSelections = ref({}); 
const validationErrors = ref({}); 

// Data cho sections phụ
const otherCombos = ref([]);

// Đồng hồ
const currentTime = ref(new Date());
let timerInterval = null;

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(val || 0);
const getImage = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : 'https://placehold.co/400x300';

// Hàm Phóng to ảnh (Lightbox)
const viewFullImage = (url) => {
  Swal.fire({
    imageUrl: url,
    imageAlt: 'Product Image',
    width: 'auto',
    padding: 0,
    background: 'transparent',
    backdrop: 'rgba(0,0,0,0.85)',
    showConfirmButton: false,
    showCloseButton: true,
    customClass: { image: 'rounded-4 shadow-lg object-fit-contain' }
  });
};

const originalTotal = computed(() => {
  if (!combo.value) return 0;
  return combo.value.items.reduce((total, item) => {
    let price = 0;
    if (item.product_variant_id && item.variant) {
      price = item.variant.price; 
    } else if (userSelections.value[item.id]) {
      const selectedVar = item.product?.variants.find(v => v.id === userSelections.value[item.id]);
      price = selectedVar ? selectedVar.price : item.product.base_price;
    } else {
      price = item.product ? item.product.base_price : 0;
    }
    return total + (parseFloat(price) * item.quantity);
  }, 0);
});

const finalPrice = computed(() => {
  if (!combo.value) return 0;
  let total = originalTotal.value;
  let discount = parseFloat(combo.value.discount_value);
  if (combo.value.discount_type === 'percentage') {
    return total - (total * (Math.min(discount, 100) / 100));
  }
  return Math.max(0, total - discount);
});

const savingsPercentage = computed(() => {
  if (originalTotal.value === 0) return 0;
  const savings = originalTotal.value - finalPrice.value;
  return Math.round((savings / originalTotal.value) * 100);
});

const calculateFinal = (c) => {
  let total = c.items.reduce((sum, item) => {
    let p = item.variant ? item.variant.price : (item.product ? item.product.base_price : 0);
    return sum + (parseFloat(p) * item.quantity);
  }, 0);
  let discount = parseFloat(c.discount_value);
  if (c.discount_type === 'percentage') return total - (total * (Math.min(discount, 100) / 100));
  return Math.max(0, total - discount);
};

// ===============================================================
// THUẬT TOÁN BỘ ĐẾM THỜI GIAN (REALTIME TIMEZONE-SAFE)
// ===============================================================
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
    d: days.toString().padStart(2, '0'),
    h: hours.toString().padStart(2, '0'),
    m: minutes.toString().padStart(2, '0'),
    s: seconds.toString().padStart(2, '0')
  };
};

const getTimerData = (combo) => {
    if (!combo) return { type: 'forever', title: '', isEnded: false };
    const now = currentTime.value.getTime();
    
    if (combo.usage_limit !== null && combo.usage_limit <= 0) {
        return { type: 'soldout', title: 'ĐÃ BÁN HẾT SỐ LƯỢNG', isEnded: true };
    }

    const startTime = parseDBDate(combo.start_date);
    const endTime = parseDBDate(combo.end_date);

    if (endTime && endTime < now) return { type: 'ended', title: 'ƯU ĐÃI ĐÃ KẾT THÚC', isEnded: true };
    if (startTime && startTime > now) {
        const diff = startTime - now;
        return { type: 'upcoming', title: 'MỞ BÁN SAU', isEnded: false, ...calculateTimeParts(diff) };
    }
    if (endTime && endTime >= now) {
        const diff = endTime - now;
        return { type: 'active', title: 'KẾT THÚC TRONG', isEnded: false, ...calculateTimeParts(diff) };
    }
    return { type: 'forever', title: '', isEnded: false };
};

// ===============================================================
// XỬ LÝ CHỌN BIẾN THỂ RADIO 
// ===============================================================
const selectVariant = (itemId, variantId) => {
    validationErrors.value[itemId] = false; 
};

const fetchDetail = async (slug) => {
  combo.value = null; // reset để trigger loading
  try {
    const res = await axios.get(`http://127.0.0.1:8000/api/client/combos/${slug}`);
    combo.value = res.data.data;
    
    // Reset state & validations
    userSelections.value = {};
    validationErrors.value = {};

    combo.value.items.forEach(item => {
      if (!item.product_variant_id) userSelections.value[item.id] = '';
    });

    fetchOtherCombos(combo.value.id);

  } catch (error) {
    Swal.fire('Lỗi', 'Combo không tồn tại hoặc đã hết hạn!', 'error').then(() => router.push({name: 'client-combos'}));
  }
};

const fetchOtherCombos = async (currentId) => {
    try {
        const res = await axios.get(`http://127.0.0.1:8000/api/client/combos`);
        const all = res.data.data.data;
        otherCombos.value = all.filter(c => c.id !== currentId).slice(0, 3);
    } catch(e) {}
};

const validateSelections = () => {
  let isValid = true;
  combo.value.items.forEach(item => {
    if (!item.product_variant_id && !userSelections.value[item.id]) {
      validationErrors.value[item.id] = true;
      isValid = false;
    }
  });
  return isValid;
};

const preparePayload = () => {
  const customSelections = Object.keys(userSelections.value).map(itemId => ({
    combo_item_id: itemId,
    selected_variant_id: userSelections.value[itemId]
  }));
  return { combo_id: combo.value.id, quantity: 1, selections: customSelections };
};

const addToCart = () => {
  if (!validateSelections()) {
    Swal.fire({ toast:true, position: 'top', icon:'warning', title: 'Vui lòng định hình phong cách cho tất cả sản phẩm!', showConfirmButton: false, timer: 2000 });
    return;
  }
  Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã thêm vào Túi mua sắm', showConfirmButton: false, timer: 1500 });
};

const buyNow = () => {
  if (!validateSelections()) {
    Swal.fire({ toast:true, position: 'top', icon:'warning', title: 'Vui lòng định hình phong cách cho tất cả sản phẩm!', showConfirmButton: false, timer: 2000 });
    return;
  }
  const payload = preparePayload();
  localStorage.setItem('checkout_combo_direct', JSON.stringify(payload));
  Swal.fire('Thành công', 'Chuyển hướng đến trang Thanh toán Mua Ngay...', 'success');
};

const goToDetail = (slug) => {
  router.push({ name: 'client-combo-detail', params: { slug } });
  window.scrollTo({ top: 0, behavior: 'smooth' });
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
.transition-transform { transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94); }
.duration-500 { transition-duration: 500ms; }
.cursor-pointer { cursor: pointer; }
.cursor-zoom-in { cursor: zoom-in; }

/* COLORS */
.text-sora-primary { color: #9f273b !important; }
.text-sora-red { color: #cc1e2e !important; }
.text-gold { color: #e7ce7d !important; }
.bg-sora-primary { background-color: #9f273b !important; }
.bg-sora-red { background-color: #cc1e2e !important; color: white;}
.bg-gold { background-color: #e7ce7d !important; }
.border-gold-light { border-color: rgba(231, 206, 125, 0.4) !important; }

/* BREADCRUMB */
.hover-primary:hover { color: #9f273b !important; }

/* IMAGE EFFECTS (EDITORIAL) */
.luxury-image-wrapper { border-radius: 4px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); background: #fff; }
.img-zoom-hover { transition: transform 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94); }
.luxury-image-wrapper:hover .img-zoom-hover { transform: scale(1.05); }
.grayscale { filter: grayscale(100%); }
.ended-overlay { position: absolute; inset: 0; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(2px); z-index: 10; }
.luxury-badge { letter-spacing: 2px; font-size: 0.85rem; }

/* TIME BOX (MINIMALIST) */
.pulsing-dot { width: 8px; height: 8px; border-radius: 50%; animation: pulse 1.5s infinite; }
@keyframes pulse { 0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(204, 30, 46, 0.7); } 70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(204, 30, 46, 0); } 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(204, 30, 46, 0); } }

/* CUSTOM RADIO CHIPS (XỊN XÒ) */
.custom-radio-chip {
    cursor: pointer;
    display: inline-block;
}
.custom-radio-chip .chip-content {
    border: 1px solid #dcdcdc;
    padding: 8px 16px;
    background-color: transparent;
    color: #555;
    transition: all 0.3s ease;
    min-width: 80px;
    border-radius: 2px;
}
.custom-radio-chip:hover .chip-content {
    border-color: #e7ce7d;
    color: #9f273b;
}
.custom-radio-chip.selected .chip-content {
    border-color: #9f273b;
    background-color: #9f273b;
    color: #fff;
    box-shadow: 0 4px 10px rgba(159, 39, 59, 0.2);
}
.custom-radio-chip.selected .chip-content .price-diff {
    color: #ffdae0 !important;
}
.custom-radio-chip.error .chip-content {
    border-color: #cc1e2e;
    color: #cc1e2e;
    animation: shake 0.4s;
    background-color: rgba(204, 30, 46, 0.05);
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-4px); }
    50% { transform: translateX(4px); }
    75% { transform: translateX(-4px); }
}

/* BUTTONS LUXURY */
.luxury-btn-solid { 
    background-color: #9f273b; color: white; border: 1px solid #9f273b; 
    transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); 
}
.luxury-btn-solid:hover { 
    background-color: #7a1c2d; border-color: #7a1c2d; color: white; 
    box-shadow: 0 8px 20px rgba(159,39,59,0.3); transform: translateY(-2px); 
}
.luxury-btn-outline { 
    border: 1px solid #9f273b; color: #9f273b; background: transparent; 
    transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); 
}
.luxury-btn-outline:hover { 
    background: #9f273b; color: white; 
    transform: translateY(-2px); box-shadow: 0 8px 20px rgba(159,39,59,0.2); 
}

/* HOVER CARDS (Other Combos) */
.group-hover:hover { box-shadow: 0 15px 35px rgba(0,0,0,0.05) !important; transform: translateY(-5px); transition: all 0.4s ease; }
.group-hover:hover .transition-transform { transform: scale(1.05); }
.title-hover:hover { color: #cc1e2e !important; }
</style>