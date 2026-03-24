<template>
  <div class="favorite-page">
    <div class="container">
      <div class="page-header">
        <h1 class="page-title">Sản Phẩm Yêu Thích</h1>
        <p class="page-subtitle">Những món trang sức bạn đã lưu lại</p>
      </div>

      <!-- Trạng thái trống (Không có sản phẩm) -->
      <div v-if="favorites.length === 0" class="empty-state">
        <div class="empty-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
          </svg>
        </div>
        <h2>Danh sách yêu thích của bạn đang trống</h2>
        <p>Hãy khám phá các bộ sưu tập trang sức của chúng tôi và lưu lại những món đồ bạn yêu thích nhé.</p>
        <router-link to="/" class="btn-shopping">Tiếp tục mua sắm</router-link>
      </div>

      <!-- Danh sách sản phẩm -->
      <div v-else class="product-grid">
        <div v-for="item in favorites" :key="item.id" class="product-card">
          <div class="product-image">
            <img :src="item.image" :alt="item.name" />
            <!-- Nút xóa khỏi danh sách yêu thích -->
            <button class="btn-remove" @click="removeFromFavorites(item.id)" title="Xóa khỏi danh sách yêu thích">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
              </svg>
            </button>
            <span v-if="item.isSale" class="sale-badge">Sale</span>
          </div>

          <div class="product-info">
            <h3 class="product-name">
              <router-link :to="`/product/${item.slug}`">{{ item.name }}</router-link>
            </h3>
            <div class="product-price">
              <span class="current-price">{{ formatPrice(item.price) }}</span>
              <span v-if="item.oldPrice" class="old-price">{{ formatPrice(item.oldPrice) }}</span>
            </div>
            
            <button class="btn-add-cart" @click="addToCart(item)">
              Thêm vào giỏ hàng
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// Mock data (Dữ liệu giả lập cho giao diện)
// Sếp có thể thay thế bằng dữ liệu gọi từ API Laravel sau này
const favorites = ref([
  {
    id: 1,
    name: 'Dây chuyền vàng 18K đính kim cương',
    slug: 'day-chuyen-vang-18k-kim-cuong',
    price: 15500000,
    oldPrice: 18000000,
    isSale: true,
    image: 'https://images.unsplash.com/photo-1599643478514-4a4e0e69b50e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
  },
  {
    id: 2,
    name: 'Nhẫn đính hôn Platinum sang trọng',
    slug: 'nhan-dinh-hon-platinum',
    price: 22000000,
    oldPrice: null,
    isSale: false,
    image: 'https://images.unsplash.com/photo-1605100804763-247f67b2548e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
  },
  {
    id: 3,
    name: 'Bông tai ngọc trai quý phái',
    slug: 'bong-tai-ngoc-trai',
    price: 8500000,
    oldPrice: null,
    isSale: false,
    image: 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
  },
  {
    id: 4,
    name: 'Lắc tay đính đá Sapphire đỏ',
    slug: 'lac-tay-da-sapphire',
    price: 12400000,
    oldPrice: 15000000,
    isSale: true,
    image: 'https://images.unsplash.com/photo-1611591437281-460bfbe1220a?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'
  }
]);

// Hàm định dạng tiền tệ VNĐ
const formatPrice = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

// Hàm xóa khỏi danh sách yêu thích
const removeFromFavorites = (id) => {
  // Logic tạm thời để xóa khỏi mảng
  favorites.value = favorites.value.filter(item => item.id !== id);
  // Ở đây sếp sẽ gọi API backend: axios.delete(`/api/favorites/${id}`)
  alert('Đã xóa sản phẩm khỏi danh sách yêu thích!');
};

// Hàm thêm vào giỏ hàng
const addToCart = (item) => {
  // Ở đây sếp sẽ gọi API backend hoặc Vuex/Pinia action để thêm vào giỏ
  alert(`Đã thêm "${item.name}" vào giỏ hàng!`);
};
</script>

<style scoped>
/* Định nghĩa biến màu theo yêu cầu của sếp */
:root {
  --color-gold: #e7ce7d;
  --color-gold-hover: #d1b866;
  --color-red: #cc1e2e;
  --color-red-hover: #a81725;
  --color-text: #333333;
  --color-text-light: #777777;
  --color-bg-light: #f9f9f9;
}

.favorite-page {
  padding: 60px 0;
  background-color: #fff;
  font-family: 'Helvetica Neue', Arial, sans-serif;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 15px;
}

.page-header {
  text-align: center;
  margin-bottom: 50px;
}

.page-title {
  font-size: 32px;
  color: var(--color-text);
  margin-bottom: 10px;
  text-transform: uppercase;
  letter-spacing: 2px;
  font-weight: 600;
}

.page-subtitle {
  color: var(--color-text-light);
  font-size: 16px;
}

/* Empty State Styles */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  background-color: var(--color-bg-light);
  border-radius: 8px;
}

.empty-icon svg {
  width: 80px;
  height: 80px;
  color: #ccc;
  margin-bottom: 20px;
}

.empty-state h2 {
  font-size: 24px;
  color: var(--color-text);
  margin-bottom: 10px;
}

.empty-state p {
  color: var(--color-text-light);
  margin-bottom: 30px;
}

.btn-shopping {
  display: inline-block;
  background-color: #e7ce7d; /* Vàng đồng */
  color: #333;
  padding: 12px 30px;
  border-radius: 4px;
  text-decoration: none;
  font-weight: 600;
  text-transform: uppercase;
  transition: all 0.3s ease;
}

.btn-shopping:hover {
  background-color: #d1b866;
}

/* Product Grid Styles */
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 30px;
}

.product-card {
  background: #fff;
  border: 1px solid #eee;
  border-radius: 8px;
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.05);
}

.product-image {
  position: relative;
  aspect-ratio: 1/1;
  overflow: hidden;
  background-color: #f5f5f5;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.product-card:hover .product-image img {
  transform: scale(1.05);
}

.btn-remove {
  position: absolute;
  top: 15px;
  right: 15px;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #cc1e2e; /* Đỏ thẫm */
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
  z-index: 2;
}

.btn-remove svg {
  width: 20px;
  height: 20px;
  transition: transform 0.2s ease;
}

.btn-remove:hover {
  background: #cc1e2e;
  color: #fff;
}

.btn-remove:hover svg {
  transform: scale(1.1);
}

.sale-badge {
  position: absolute;
  top: 15px;
  left: 15px;
  background-color: #cc1e2e; /* Đỏ thẫm */
  color: white;
  padding: 4px 12px;
  font-size: 12px;
  font-weight: bold;
  border-radius: 4px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.product-info {
  padding: 20px;
  text-align: center;
}

.product-name {
  font-size: 16px;
  margin-bottom: 10px;
  font-weight: 500;
  line-height: 1.4;
  height: 44px; /* Fixed height for 2 lines */
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.product-name a {
  color: #333;
  text-decoration: none;
  transition: color 0.3s;
}

.product-name a:hover {
  color: #e7ce7d; /* Vàng đồng */
}

.product-price {
  margin-bottom: 20px;
}

.current-price {
  color: #cc1e2e; /* Đỏ thẫm */
  font-size: 18px;
  font-weight: 600;
  margin-right: 10px;
}

.old-price {
  color: #999;
  text-decoration: line-through;
  font-size: 14px;
}

.btn-add-cart {
  width: 100%;
  background-color: #fff;
  color: #333;
  border: 1px solid #e7ce7d; /* Border Vàng đồng */
  padding: 10px 0;
  font-size: 14px;
  font-weight: 600;
  text-transform: uppercase;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-add-cart:hover {
  background-color: #e7ce7d; /* Vàng đồng */
  color: #333;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .product-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
  }
  
  .page-title {
    font-size: 24px;
  }
}

@media (max-width: 480px) {
  .product-grid {
    grid-template-columns: 1fr;
  }
}
</style>