<template>
  <div class="compare-page-wrapper">
    <div class="container">
      <div class="breadcrumb mb-4">
        <span @click="router.push('/')">Trang chủ</span> <span class="separator">/</span>
        <span class="current">So sánh sản phẩm</span>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">So sánh sản phẩm</h1>
        <div class="controls">
          <label class="diff-toggle">
            <input type="checkbox" v-model="showDiffOnly">
            <span>Chỉ hiển thị điểm khác biệt</span>
          </label>
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
        <table class="compare-table">
          <!-- HÀNG 1: THÔNG TIN CƠ BẢN VÀ ACTION -->
          <thead>
            <tr>
              <th class="criteria-col empty-th"></th>
              <th v-for="(product, index) in products" :key="product.id" class="product-col" :class="{'best-choice': isBestPrice(product.base_price)}">
                <div class="product-card-top">
                  <button class="btn-remove" @click="removeProduct(product.id)">✕</button>
                  <img :src="product.thumbnail_image || 'https://via.placeholder.com/150'" :alt="product.name" class="p-img">
                  <h3 class="p-name" @click="goToDetail(product.slug)">{{ product.name }}</h3>
                  <div class="p-price">{{ formatMoney(product.promotional_price || product.base_price) }}</div>
                  
                  <button class="btn-buy" @click="goToDetail(product.slug)">XEM CHI TIẾT</button>
                </div>
              </th>
              <th v-if="products.length < 4" class="add-more-col">
                <div class="add-more-box" @click="router.push(`/shop/${shopSlug}`)">
                  <div class="plus-icon">+</div>
                  <p>Thêm sản phẩm</p>
                </div>
              </th>
            </tr>
          </thead>

          <!-- BODY: CÁC TIÊU CHÍ SO SÁNH -->
          <tbody>
            <!-- Thương hiệu -->
            <tr v-show="!showDiffOnly || hasDifference('brand_name')">
              <td class="criteria-name">Thương hiệu</td>
              <td v-for="product in products" :key="'brand-'+product.id" :class="{'highlight-diff': showDiffOnly && hasDifference('brand_name')}">
                {{ product.brand_name }}
              </td>
              <td v-if="products.length < 4"></td>
            </tr>

            <!-- Danh mục -->
            <tr v-show="!showDiffOnly || hasDifference('category_name')">
              <td class="criteria-name">Danh mục</td>
              <td v-for="product in products" :key="'cat-'+product.id" :class="{'highlight-diff': showDiffOnly && hasDifference('category_name')}">
                {{ product.category_name }}
              </td>
              <td v-if="products.length < 4"></td>
            </tr>

            <!-- Giá trị trường (Ví dụ highlight giá rẻ nhất) -->
            <tr v-show="!showDiffOnly || hasDifference('base_price')">
              <td class="criteria-name">Mức giá</td>
              <td v-for="product in products" :key="'price-'+product.id" 
                  :class="{'text-success fw-bold': isBestPrice(product.promotional_price || product.base_price), 'highlight-diff': showDiffOnly && hasDifference('base_price')}">
                {{ formatMoney(product.promotional_price || product.base_price) }}
              </td>
              <td v-if="products.length < 4"></td>
            </tr>

            <!-- Mô tả ngắn gọn -->
            <tr v-show="!showDiffOnly || hasDifference('description')">
              <td class="criteria-name">Mô tả</td>
              <td v-for="product in products" :key="'desc-'+product.id" class="desc-cell" :class="{'highlight-diff': showDiffOnly && hasDifference('description')}">
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
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const shopSlug = route.params.shop_slug || 'aurora';
const API_BASE_URL = 'http://127.0.0.1:8000';

const products = ref([]);
const isLoading = ref(true);
const showDiffOnly = ref(false);

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

    // GỌI API BACKEND
    const response = await axios.post(`${API_BASE_URL}/api/shop/${shopSlug}/compare`, {
      product_ids: ids
    });

    if (response.data.success) {
      products.value = response.data.data;
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

// KIỂM TRA SỰ KHÁC BIỆT (Cho toggle "Chỉ hiển thị khác biệt")
const hasDifference = (key) => {
  if (products.value.length <= 1) return false;
  const firstVal = products.value[0][key];
  return products.value.some(p => p[key] !== firstVal);
};

// Logic tìm sản phẩm có giá tốt nhất
const isBestPrice = (price) => {
  if (products.value.length <= 1) return false;
  const prices = products.value.map(p => parseFloat(p.promotional_price || p.base_price));
  const minPrice = Math.min(...prices);
  return parseFloat(price) === minPrice;
};

const removeProduct = (id) => {
  // Xóa trên giao diện
  products.value = products.value.filter(p => p.id !== id);
  
  // Xóa trong localStorage
  const stored = JSON.parse(localStorage.getItem(`compare_list_${shopSlug}`) || '[]');
  const updatedList = stored.filter(p => p.id !== id);
  localStorage.setItem(`compare_list_${shopSlug}`, JSON.stringify(updatedList));
};

const goToDetail = (slug) => {
  router.push(`/shop/${shopSlug}/productdetail/${slug}`);
};

const formatMoney = (amount) => {
  return new Intl.NumberFormat('vi-VN').format(amount) + ' ₫';
};

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

.page-title { font-size: 24px; font-weight: 600; color: #333; }

.diff-toggle {
  display: flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer; color: #555;
}

.table-responsive { overflow-x: auto; }

.compare-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
  min-width: 900px;
}

.criteria-col { width: 15%; background: #fdfdfd; border-right: 1px solid #eee; }
.product-col { width: 21.25%; border-right: 1px solid #eee; vertical-align: top;}
.add-more-col { width: 21.25%; vertical-align: top; padding: 20px;}

.compare-table th, .compare-table td {
  padding: 20px 15px;
  border-bottom: 1px solid #eee;
}

.product-card-top {
  position: relative;
  text-align: center;
}

.btn-remove {
  position: absolute; top: -10px; right: 0; background: #eee; border: none; width: 24px; height: 24px; border-radius: 50%; cursor: pointer; color: #555;
}
.btn-remove:hover { background: red; color: white; }

.p-img { width: 120px; height: 120px; object-fit: cover; margin-bottom: 15px; border-radius: 8px;}
.p-name { font-size: 15px; font-weight: 500; color: #333; cursor: pointer; margin-bottom: 10px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;}
.p-name:hover { color: rgb(159,39,59); }
.p-price { font-size: 16px; font-weight: bold; color: rgb(159,39,59); margin-bottom: 15px; }

.btn-buy {
  width: 100%; padding: 10px; background: rgb(159,39,59); color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;
}

.add-more-box {
  height: 100%; min-height: 250px; border: 2px dashed #ddd; border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; color: #888; transition: all 0.3s;
}
.add-more-box:hover { border-color: rgb(159,39,59); color: rgb(159,39,59); background: #fffcfc;}
.plus-icon { font-size: 40px; margin-bottom: 10px; }

.criteria-name {
  font-weight: 600; color: #555; background: #fafafa;
}

.desc-cell { font-size: 13px; color: #666; line-height: 1.6; }

/* Highlight rules */
.highlight-diff { background-color: #fff9e6; transition: background 0.3s; }
.text-success { color: #28a745 !important; }

/* Loading state */
.loading-state, .empty-state { text-align: center; padding: 50px 0; color: #666;}
.spinner { width: 40px; height: 40px; border: 3px solid #f3f3f3; border-top: 3px solid rgb(159,39,59); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 16px; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

.btn-primary-outline { background: transparent; border: 1px solid rgb(159,39,59); color: rgb(159,39,59); padding: 10px 24px; border-radius: 6px; cursor: pointer; margin-top: 15px;}
</style>