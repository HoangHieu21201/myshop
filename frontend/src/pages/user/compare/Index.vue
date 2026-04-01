<template>
  <div class="compare-page-wrapper">
    <div class="container">
      <div class="breadcrumb mb-4">
        <span @click="router.push('/')">Trang chủ</span> <span class="separator">/</span>
        <span @click="router.push(`/shop/${shopSlug}`)">Sản phẩm</span> <span class="separator">/</span>
        <span class="current">So sánh sản phẩm</span>
      </div>

      <!-- HEADER MỚI: QUAY LẠI VÀ CHỈ BÁO SP GỐC -->
      <div class="compare-header mb-4 pb-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div class="header-left">
            <h1 class="page-title mb-2">So sánh sản phẩm</h1>
            <!-- Hiển thị tên sản phẩm gốc -->
            <p v-if="baseProductName" class="text-muted mb-0" style="font-size: 15px;">
              Bạn đang so sánh với sản phẩm: <strong style="color: rgb(159,39,59);">{{ baseProductName }}</strong>
            </p>
          </div>
          
          <div class="header-right">
            <!-- Nút Quay lại sản phẩm -->
            <button class="btn-outline" @click="goBackToBaseProduct">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2" style="vertical-align: middle; margin-top: -2px;">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
              </svg>
              Quay lại sản phẩm
            </button>

            <label class="diff-toggle ms-md-3">
              <input type="checkbox" v-model="showDiffOnly">
              <span>Chỉ hiển thị điểm khác biệt</span>
            </label>
          </div>
        </div>
      </div>

      <div v-if="isLoading" class="loading-state">
        <div class="spinner"></div>
        <p>Đang tải dữ liệu so sánh...</p>
      </div>

      <div v-else-if="products.length === 0" class="empty-state">
        <p>Chưa có sản phẩm nào để so sánh.</p>
        <button class="btn-primary-outline" @click="router.push(`/shop/${shopSlug}`)">Tiếp tục mua sắm</button>
      </div>

      <div v-else class="table-responsive">
        <table class="compare-table hover-column-table">
          <!-- HÀNG 1: THÔNG TIN CƠ BẢN VÀ ACTION -->
          <thead>
            <tr>
              <th class="criteria-col empty-th"></th>
              <th v-for="(product, index) in products" :key="product.id" class="product-col" :class="{'best-choice': isBestPrice(product.promotional_price || product.base_price)}">
                <div class="product-card-top">
                  <button class="btn-remove" @click="removeProduct(product.id)" title="Xóa khỏi so sánh">✕</button>
                  <img :src="product.thumbnail_image || 'https://via.placeholder.com/150'" :alt="product.name" class="p-img">
                  <h3 class="p-name" @click="goToDetail(product.slug)" :title="product.name">{{ product.name }}</h3>
                  
                  <!-- Tag Sản phẩm gốc được gán tự động vào cột đầu tiên (hoặc trùng với spGoc id) -->
                  <div v-if="product.id == spGoc || (index === 0 && !spGoc && products.length > 1)" class="base-product-badge">
                    Sản phẩm gốc
                  </div>

                  <button class="btn-buy mt-3" @click="goToDetail(product.slug)">XEM CHI TIẾT</button>
                </div>
              </th>
              <!-- Ô thêm sản phẩm -->
              <th v-if="products.length < 4" class="add-more-col">
                <div class="add-more-box" @click="router.push(`/shop/${shopSlug}`)">
                  <div class="plus-icon">+</div>
                  <p>Thêm sản phẩm</p>
                </div>
              </th>
            </tr>
          </thead>

          <!-- BODY: CÁC TIÊU CHÍ SO SÁNH (Hover cột hoạt động ở đây) -->
          <tbody>
            
            <!-- 1. MỨC GIÁ VÀ CHÊNH LỆCH -->
            <tr v-show="!showDiffOnly || hasDifference('price')">
              <td class="criteria-name">Mức giá</td>
              <td v-for="(product, index) in products" :key="'price-'+product.id" class="val-cell" :class="{'highlight-diff': showDiffOnly && hasDifference('price')}">
                <div class="price-wrap" :class="{'text-success fw-bold': isBestPrice(product.promotional_price || product.base_price)}">
                  {{ formatMoney(product.promotional_price || product.base_price) }}
                </div>
                
                <!-- Text giải thích chênh lệch giá (So với sản phẩm cột 1) -->
                <div v-if="index > 0" class="diff-text mt-1" :class="getPriceDiffColor(product, products[0])">
                  <span v-if="getPriceDiff(product, products[0])">
                    {{ getPriceDiff(product, products[0]) }}
                  </span>
                  <span v-else class="text-muted fst-italic">Bằng giá</span>
                </div>
              </td>
              <td v-if="products.length < 4"></td>
            </tr>

            <!-- 2. TÌNH TRẠNG TỒN KHO -->
            <tr v-show="!showDiffOnly || hasDifference('stock_quantity')">
              <td class="criteria-name">Tồn kho</td>
              <td v-for="(product, index) in products" :key="'stock-'+product.id" class="val-cell" :class="{'highlight-diff': showDiffOnly && hasDifference('stock_quantity')}">
                <div :class="product.stock_quantity > 0 ? 'text-dark' : 'text-danger'">
                  {{ product.stock_quantity > 0 ? `Còn hàng (${product.stock_quantity})` : 'Hết hàng' }}
                </div>
                
                <!-- Text chênh lệch số lượng -->
                <div v-if="index > 0" class="diff-text text-muted mt-1 fst-italic">
                  {{ getStockDiff(product, products[0]) }}
                </div>
              </td>
              <td v-if="products.length < 4"></td>
            </tr>

            <!-- 3. THƯƠNG HIỆU -->
            <tr v-show="!showDiffOnly || hasDifference('brand_name')">
              <td class="criteria-name">Thương hiệu</td>
              <td v-for="product in products" :key="'brand-'+product.id" class="val-cell" :class="{'highlight-diff': showDiffOnly && hasDifference('brand_name')}">
                {{ product.brand_name || 'Không có' }}
              </td>
              <td v-if="products.length < 4"></td>
            </tr>

            <!-- 4. DANH MỤC -->
            <tr v-show="!showDiffOnly || hasDifference('category_name')">
              <td class="criteria-name">Danh mục</td>
              <td v-for="product in products" :key="'cat-'+product.id" class="val-cell" :class="{'highlight-diff': showDiffOnly && hasDifference('category_name')}">
                {{ product.category_name || 'Không có' }}
              </td>
              <td v-if="products.length < 4"></td>
            </tr>

            <!-- 5. CÁC THÔNG SỐ KỸ THUẬT (TỰ ĐỘNG EXTRACT TỪ JSON DATABASE) -->
            <tr v-for="specKey in allSpecificationKeys" :key="specKey" v-show="!showDiffOnly || hasSpecDifference(specKey)">
              <td class="criteria-name text-capitalize">{{ specKey }}</td>
              <td v-for="(product, index) in products" :key="specKey+'-'+product.id" class="val-cell" :class="{'highlight-diff': showDiffOnly && hasSpecDifference(specKey)}">
                
                <div class="fw-medium">{{ getSpecValue(product, specKey) }}</div>
                
                <!-- Giải thích sự khác biệt Thuộc tính -->
                <div v-if="index > 0" class="diff-text attr-diff mt-1">
                  {{ getSpecDiffText(product, products[0], specKey) }}
                </div>

              </td>
              <td v-if="products.length < 4"></td>
            </tr>

            <!-- 6. MÔ TẢ NGẮN -->
            <tr v-show="!showDiffOnly || hasDifference('description')">
              <td class="criteria-name">Mô tả</td>
              <td v-for="product in products" :key="'desc-'+product.id" class="desc-cell val-cell" :class="{'highlight-diff': showDiffOnly && hasDifference('description')}">
                <div v-html="truncateHtml(product.description, 100)"></div>
              </td>
              <td v-if="products.length < 4"></td>
            </tr>

          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const shopSlug = route.params.shop_slug || 'aurora';
const API_BASE_URL = 'http://127.0.0.1:8000';

const products = ref([]);
const isLoading = ref(true);
const showDiffOnly = ref(false);

// Thay vì dùng ref, dùng computed để theo dõi query param theo thời gian thực an toàn hơn
const spGoc = computed(() => route.query.spGoc || null);

// Computed lấy tên sản phẩm gốc đang được so sánh
const baseProductName = computed(() => {
  if (!spGoc.value || products.value.length === 0) return null;
  const baseProd = products.value.find(p => p.id == spGoc.value);
  return baseProd ? baseProd.name : null;
});

// Hàm quay lại: Trả về chi tiết spGoc hoặc danh sách chung
const goBackToBaseProduct = () => {
  console.log("Đã bấm quay lại. Đang kiểm tra spGoc URL param:", spGoc.value);
  
  if (spGoc.value) {
      const baseProd = products.value.find(p => p.id == spGoc.value);
      
      if (baseProd && baseProd.slug) {
          // FIX: Đổi từ productdetail sang product cho khớp với Vue Router
          const detailUrl = `/shop/${shopSlug}/product/${baseProd.slug}`;
          console.log("Bắt đầu chuyển hướng về:", detailUrl);
          
          router.push(detailUrl).catch((err) => {
              console.warn("Cảnh báo từ Vue Router, dùng fallback chuyển trang...", err);
              window.location.href = detailUrl;
          });
          return;
      } else {
          console.warn("Không tìm thấy sản phẩm trong danh sách hoặc sản phẩm không có slug hợp lệ.");
      }
  }
  
  const shopUrl = `/shop/${shopSlug}`;
  console.log("Không có spGoc hợp lệ. Chuyển về trang cửa hàng:", shopUrl);
  router.push(shopUrl).catch(() => {
      window.location.href = shopUrl;
  });
};

const loadCompareData = async () => {
  try {
    const stored = localStorage.getItem(`compare_list_${shopSlug}`);
    if (!stored) {
      isLoading.value = false;
      return;
    }

    const compareList = JSON.parse(stored);
    const ids = compareList.map(p => p.id);

    if (ids.length === 0) {
      isLoading.value = false;
      return;
    }

    const response = await axios.post(`${API_BASE_URL}/api/shop/${shopSlug}/compare`, {
      product_ids: ids
    });

    if (response.data.success) {
      let fetchedProducts = response.data.data;
      
      // Sắp xếp đưa Sản phẩm gốc (spGoc) lên đầu tiên (vị trí index 0)
      if (spGoc.value) {
          fetchedProducts.sort((a, b) => {
              if (a.id == spGoc.value) return -1;
              if (b.id == spGoc.value) return 1;
              return 0;
          });
      }
      
      products.value = fetchedProducts;
    }

  } catch (error) {
    console.error("Lỗi khi tải dữ liệu so sánh", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  loadCompareData();
});

// ==========================================
// LOGIC: TRÍCH XUẤT THÔNG SỐ (SPECIFICATIONS)
// ==========================================
// Lấy tất cả các keys có trong json specifications của các sản phẩm
const allSpecificationKeys = computed(() => {
  const keys = new Set();
  products.value.forEach(p => {
    if (p.specifications && typeof p.specifications === 'object') {
      Object.keys(p.specifications).forEach(k => keys.add(k));
    }
  });
  return Array.from(keys);
});

const getSpecValue = (product, key) => {
  if (!product.specifications || !product.specifications[key]) return '-';
  return product.specifications[key];
};

// ==========================================
// LOGIC: KIỂM TRA KHÁC BIỆT ĐỂ ẨN/HIỆN HÀNG
// ==========================================
const hasDifference = (key) => {
  if (products.value.length <= 1) return false;
  const firstVal = key === 'price' 
      ? (products.value[0].promotional_price || products.value[0].base_price) 
      : products.value[0][key];

  return products.value.some(p => {
    const val = key === 'price' ? (p.promotional_price || p.base_price) : p[key];
    return val !== firstVal;
  });
};

const hasSpecDifference = (specKey) => {
  if (products.value.length <= 1) return false;
  const firstVal = getSpecValue(products.value[0], specKey);
  return products.value.some(p => getSpecValue(p, specKey) !== firstVal);
};

// ==========================================
// LOGIC: TEXT GIẢI THÍCH CHI TIẾT CHÊNH LỆCH
// ==========================================

// 1. Chênh lệch Giá
const getPriceDiff = (current, base) => {
  if (!base || current.id === base.id) return null;
  const pCurrent = parseFloat(current.promotional_price || current.base_price);
  const pBase = parseFloat(base.promotional_price || base.base_price);
  const diff = pCurrent - pBase;

  if (diff === 0) return null;
  
  const diffFormatted = new Intl.NumberFormat('vi-VN').format(Math.abs(diff)) + 'đ';
  
  // Custom text mạnh nếu chênh lệch trên 2 triệu
  if (diff > 2000000) return `Đắt hơn rất nhiều (+ ${diffFormatted})`;
  if (diff < -2000000) return `Rẻ hơn cực nhiều (- ${diffFormatted})`;
  
  return diff > 0 ? `Đắt hơn ${diffFormatted}` : `Rẻ hơn ${diffFormatted}`;
};

const getPriceDiffColor = (current, base) => {
  if (!base || current.id === base.id) return '';
  const pCurrent = parseFloat(current.promotional_price || current.base_price);
  const pBase = parseFloat(base.promotional_price || base.base_price);
  return pCurrent > pBase ? 'text-danger' : (pCurrent < pBase ? 'text-success' : '');
};

// 2. Chênh lệch Tồn kho
const getStockDiff = (current, base) => {
  if (!base || current.id === base.id) return null;
  const sCurrent = current.stock_quantity || 0;
  const sBase = base.stock_quantity || 0;
  const diff = sCurrent - sBase;

  if (diff === 0) return null;
  return diff > 0 ? `(Nhiều hơn ${diff} sản phẩm)` : `(Ít hơn ${Math.abs(diff)} sản phẩm)`;
};

// 3. Khác biệt thông số (Màu sắc, Size...)
const getSpecDiffText = (current, base, specKey) => {
  if (!base || current.id === base.id) return null;
  const vCurrent = getSpecValue(current, specKey);
  const vBase = getSpecValue(base, specKey);

  if (vCurrent !== vBase && vCurrent !== '-' && vBase !== '-') {
    return `Khác: ${vCurrent} vs ${vBase}`;
  }
  return null;
};


// ==========================================
// UTILITIES CƠ BẢN
// ==========================================
const isBestPrice = (price) => {
  if (products.value.length <= 1 || !price) return false;
  const prices = products.value.map(p => parseFloat(p.promotional_price || p.base_price)).filter(p => !isNaN(p));
  const minPrice = Math.min(...prices);
  return parseFloat(price) === minPrice;
};

const removeProduct = (id) => {
  products.value = products.value.filter(p => p.id !== id);
  const stored = JSON.parse(localStorage.getItem(`compare_list_${shopSlug}`) || '[]');
  const updatedList = stored.filter(p => p.id !== id);
  localStorage.setItem(`compare_list_${shopSlug}`, JSON.stringify(updatedList));
};

// FIX: Đổi từ productdetail sang product cho khớp với Vue Router
const goToDetail = (slug) => router.push(`/shop/${shopSlug}/product/${slug}`);
const formatMoney = (amount) => amount ? new Intl.NumberFormat('vi-VN').format(amount) + ' ₫' : '0 ₫';
const truncateHtml = (html, length) => {
  if (!html) return '';
  const tmp = document.createElement("DIV");
  tmp.innerHTML = html;
  const text = tmp.textContent || tmp.innerText || "";
  return text.length > length ? text.substring(0, length) + "..." : text;
};
</script>

<style scoped>
.compare-page-wrapper {
  background: #f8f9fa;
  min-height: 100vh;
  padding: 40px 0;
}

.container {
  max-width: 1300px;
  margin: 0 auto;
  background: #fff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}

.breadcrumb { font-size: 14px; color: #888; cursor: pointer; }
.breadcrumb .separator { margin: 0 10px; }
.breadcrumb .current { color: #333; pointer-events: none;}

.compare-header {
  border-bottom: 1px solid #eee;
}
.header-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

.page-title { font-size: 24px; font-weight: 600; color: #333; }

.diff-toggle {
  display: flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer; color: #555;
}

.table-responsive { overflow-x: auto; padding-bottom: 20px; }

/* Nút quay lại */
.btn-outline {
  background: transparent;
  border: 1px solid #ccc;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  color: #555;
  font-weight: 600;
  transition: background 0.2s, border-color 0.2s;
  display: flex;
  align-items: center;
}
.btn-outline:hover {
  background: #f9f9f9;
  border-color: #999;
}
.me-2 { margin-right: 8px; }

/* ==========================================
   CSS TRICK: HIỆU ỨNG HOVER CẢ CỘT
========================================== */
.hover-column-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed; /* Bắt buộc để hover cột đều nhau */
  min-width: 900px;
  overflow: hidden; /* Cắt bớt phần ::after dư thừa */
}

.hover-column-table td, .hover-column-table th {
  position: relative; /* Context cho pseudo-element */
  z-index: 1;
  padding: 20px 15px;
  border-bottom: 1px solid #eee;
}

/* Kích hoạt highlight cột khi hover vào bất kỳ td nào có class val-cell, product-col */
.hover-column-table .product-col:hover::after,
.hover-column-table .val-cell:hover::after {
  content: "";
  position: absolute;
  background-color: rgba(159, 39, 59, 0.04); /* Màu đỏ nhạt của SORA */
  left: 0;
  top: -5000px;
  height: 10000px; /* Trải dài toàn bộ chiều cao bảng */
  width: 100%;
  z-index: -1;
  pointer-events: none;
}

/* Cột Layout */
.criteria-col { width: 15%; background: #fdfdfd; border-right: 1px solid #eee; }
.product-col { width: 21.25%; border-right: 1px solid #eee; vertical-align: top;}
.add-more-col { width: 21.25%; vertical-align: top; padding: 20px;}

/* Header Sản Phẩm */
.product-card-top {
  position: relative;
  text-align: center;
}

.btn-remove {
  position: absolute; top: -10px; right: 0; background: #eee; border: none; width: 26px; height: 26px; border-radius: 50%; cursor: pointer; color: #555; display: flex; align-items: center; justify-content: center; z-index: 10;
}
.btn-remove:hover { background: #E74C3C; color: white; }

.p-img { width: 140px; height: 140px; object-fit: cover; margin-bottom: 15px; border-radius: 8px; background: #fafafa;}
.p-name { font-size: 15px; font-weight: 500; color: #333; cursor: pointer; margin-bottom: 10px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 42px;}
.p-name:hover { color: rgb(159,39,59); }

.base-product-badge {
  display: inline-block; padding: 3px 8px; background: #f1f1f1; border-radius: 4px; font-size: 11px; color: #555; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
}

.btn-buy {
  width: 100%; padding: 10px; background: transparent; color: rgb(159,39,59); border: 1px solid rgb(159,39,59); border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s;
}
.btn-buy:hover { background: rgb(159,39,59); color: #fff;}

.add-more-box {
  height: 100%; min-height: 250px; border: 2px dashed #ddd; border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; color: #888; transition: all 0.3s;
}
.add-more-box:hover { border-color: rgb(159,39,59); color: rgb(159,39,59); background: #fffcfc;}
.plus-icon { font-size: 40px; margin-bottom: 10px; }

/* Table Body Text */
.criteria-name {
  font-weight: 600; color: #555; background: #fafafa; font-size: 14px;
}
.val-cell { text-align: center; vertical-align: middle;}
.price-wrap { font-size: 16px; font-weight: 600; color: #333; }
.desc-cell { font-size: 13px; color: #666; line-height: 1.6; text-align: left;}

/* Difference Text & Highlights */
.diff-text { font-size: 12px; font-weight: 600; }
.attr-diff { color: rgb(159,39,59); font-style: italic;}
.highlight-diff { background-color: #fdfaf5; transition: background 0.3s; }

/* Utilities */
.text-success { color: #28a745 !important; }
.text-danger { color: #dc3545 !important; }
.text-muted { color: #888 !important;}
.fw-bold { font-weight: 700 !important; }
.fw-medium { font-weight: 500 !important; }
.fst-italic { font-style: italic !important; }
.mt-1 { margin-top: 0.25rem; }
.mt-3 { margin-top: 1rem; }

/* Loading state */
.loading-state, .empty-state { text-align: center; padding: 50px 0; color: #666;}
.spinner { width: 40px; height: 40px; border: 3px solid #f3f3f3; border-top: 3px solid rgb(159,39,59); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 16px; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
.btn-primary-outline { background: transparent; border: 1px solid rgb(159,39,59); color: rgb(159,39,59); padding: 10px 24px; border-radius: 6px; cursor: pointer; margin-top: 15px;}

@media (min-width: 768px) {
  .ms-md-3 { margin-left: 1rem; }
}
</style>