<template>
  <div class="storefront-wrapper font-luxury bg-white">
    
    <section class="hero-carousel position-relative">
      <div id="homeBannerCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">
          <div v-for="(banner, index) in data.banners" :key="banner.id" class="carousel-item" :class="{ active: index === 0 }">
            <img :src="getImageUrl(banner.image_desktop)" class="d-block w-100 hero-img object-fit-cover" alt="Banner" @error="handleImageError">
            <div class="carousel-overlay"></div>
            <div class="carousel-caption d-none d-md-flex flex-column justify-content-center h-100 text-center px-5">
              <h6 class="text-gold tracking-widest text-uppercase mb-3">SORA Exclusive</h6>
              <h2 class="display-3 font-serif fw-bold text-white mb-4 shadow-text lh-sm">{{ banner.title || 'VẺ ĐẸP VĨNH CỬU' }}</h2>
              <div class="mt-2">
                <a :href="banner.target_url || '#'" class="btn btn-outline-light px-5 py-3 text-uppercase tracking-widest fw-bold rounded-0 mx-2 hover-gold-btn">
                  Khám Phá Cửa Hàng
                </a>
              </div>
            </div>
          </div>
          <div v-if="data.banners.length === 0" class="carousel-item active bg-dark d-flex align-items-center justify-content-center" style="height: 80vh;">
            <h2 class="text-gold font-serif tracking-widest display-4">SORA JEWELRY</h2>
          </div>
        </div>
        <button class="carousel-control-prev w-auto px-4" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="prev">
          <i class="bi bi-chevron-left fs-1 text-white opacity-75 hover-opacity-100 transition-all"></i>
        </button>
        <button class="carousel-control-next w-auto px-4" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="next">
          <i class="bi bi-chevron-right fs-1 text-white opacity-75 hover-opacity-100 transition-all"></i>
        </button>
      </div>
    </section>

    <section class="usps-section bg-primary-luxury text-white py-4 border-bottom border-gold border-2">
      <div class="container">
        <div class="row text-center g-4">
          <div class="col-6 col-md-3">
            <i class="bi bi-truck fs-3 mb-2 text-gold"></i>
            <h6 class="font-serif fw-bold mb-1">Giao Hàng Miễn Phí</h6>
            <small class="opacity-75" style="font-size: 0.75rem;">Toàn quốc cho đơn từ 2Tr</small>
          </div>
          <div class="col-6 col-md-3">
            <i class="bi bi-shield-check fs-3 mb-2 text-gold"></i>
            <h6 class="font-serif fw-bold mb-1">Bảo Hành Trọn Đời</h6>
            <small class="opacity-75" style="font-size: 0.75rem;">Đánh bóng, siêu âm miễn phí</small>
          </div>
          <div class="col-6 col-md-3">
            <i class="bi bi-gem fs-3 mb-2 text-gold"></i>
            <h6 class="font-serif fw-bold mb-1">Giấy Kiểm Định</h6>
            <small class="opacity-75" style="font-size: 0.75rem;">Chứng nhận kim cương quốc tế</small>
          </div>
          <div class="col-6 col-md-3">
            <i class="bi bi-arrow-return-left fs-3 mb-2 text-gold"></i>
            <h6 class="font-serif fw-bold mb-1">Thu Đổi Dễ Dàng</h6>
            <small class="opacity-75" style="font-size: 0.75rem;">Chính sách minh bạch 100%</small>
          </div>
        </div>
      </div>
    </section>

    <section class="coupons-section py-5" style="background-color: #f9f9f9;" v-if="data.coupons.length > 0">
      <div class="container py-4">
        <div class="text-center mb-5">
          <h3 class="font-serif fw-bold text-dark mb-2">Đặc Quyền Mua Sắm</h3>
          <div class="divider-gold mx-auto"></div>
        </div>
        <div class="coupon-scroll-container d-flex gap-4 pb-3">
          <div v-for="coupon in data.coupons" :key="coupon.id" class="coupon-card flex-shrink-0 position-relative bg-white border border-secondary border-opacity-25 rounded-0 shadow-sm">
            <div class="row g-0 h-100">
              <div class="col-4 bg-primary-luxury text-gold d-flex flex-column justify-content-center align-items-center p-3 border-end-dashed">
                <span class="fw-bold display-6 font-serif">{{ coupon.discount_type === 'percent' ? coupon.discount_value : formatShortCurrency(coupon.discount_value) }}</span>
                <span class="small">{{ coupon.discount_type === 'percent' ? '%' : 'VNĐ' }}</span>
              </div>
              <div class="col-8 p-4 d-flex flex-column justify-content-between">
                <div>
                  <h6 class="fw-bold text-primary-luxury mb-1 tracking-widest text-uppercase">{{ coupon.code }}</h6>
                  <small class="text-muted fst-italic">Đơn tối thiểu: {{ formatCurrency(coupon.min_order_value) }}</small>
                </div>
                <button @click="saveCoupon(coupon.code)" class="btn btn-sm btn-outline-primary-luxury mt-3 rounded-0 fw-bold tracking-widest text-uppercase">
                  Lưu Mã Nhận Ưu Đãi
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="categories-section py-5 bg-white">
      <div class="container py-4 text-center">
        <h6 class="text-primary-luxury tracking-widest text-uppercase fw-bold mb-2">Lựa Chọn Di Sản</h6>
        <h3 class="font-serif fw-bold text-dark mb-5 display-6">Danh Mục Trang Sức</h3>
        
        <div class="row g-4 justify-content-center">
          <div v-for="cat in data.categories" :key="cat.id" class="col-6 col-md-4 col-lg-2">
            <router-link :to="`/category/${cat.id}`" class="text-decoration-none group d-block">
              <div class="ratio ratio-1x1 overflow-hidden mx-auto mb-3 border border-1 border-secondary border-opacity-10 p-1">
                <img :src="getImageUrl(cat.image)" class="object-fit-cover transition-transform duration-500 group-hover-scale filter-brightness" alt="Category" @error="handleImageError">
              </div>
              <h6 class="text-dark font-serif fw-bold group-hover-text-primary transition-colors fs-5">{{ cat.name }}</h6>
              <div class="divider-gold mx-auto mt-2 scale-0 group-hover-scale-100 transition-transform"></div>
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <section class="brand-story-section py-5" style="background-color: #fcfaf8;">
      <div class="container py-5">
        <div class="row align-items-center g-5">
          <div class="col-lg-6">
            <div class="position-relative p-3">
              <div class="border border-gold position-absolute w-100 h-100 top-0 start-0 translate-middle-x ms-4 mt-4 z-index-1"></div>
              <img src="https://images.unsplash.com/photo-1617038220319-276d3cfab638?q=80&w=800&auto=format&fit=crop" class="w-100 position-relative z-index-2 shadow-sm" alt="Craftsmanship" loading="lazy">
            </div>
          </div>
          <div class="col-lg-6 ps-lg-5">
            <h6 class="text-gold tracking-widest text-uppercase fw-bold mb-2">Nghệ Thuật Chế Tác</h6>
            <h2 class="font-serif fw-bold text-primary-luxury display-5 mb-4">Tinh Hoa Hội Tụ<br>Trong Từng Giọt Vàng</h2>
            <p class="text-dark fw-light mb-4 lh-lg" style="font-size: 1.1rem;">
              Tại SORA, mỗi món trang sức không chỉ là vật trang điểm, mà là một tác phẩm nghệ thuật mang đậm dấu ấn cá nhân. Bằng đôi bàn tay tài hoa của những nghệ nhân kim hoàn hàng đầu, chúng tôi biến những viên đá thô ráp thành biểu tượng của sự sang trọng, quyền quý và trường tồn cùng thời gian.
            </p>
            <router-link to="/about" class="btn btn-outline-primary-luxury px-4 py-3 text-uppercase tracking-widest fw-bold rounded-0 border-2">
              Khám Phá Câu Chuyện
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <section class="products-section py-5 my-3 container bg-white">
      <div class="d-flex justify-content-between align-items-end mb-5 pb-3 border-bottom border-secondary border-opacity-10">
        <div>
          <h6 class="text-primary-luxury tracking-widest text-uppercase fw-bold mb-2">Xu Hướng</h6>
          <h3 class="font-serif fw-bold text-dark mb-0 display-6">Tuyệt Tác Mới Nhất</h3>
        </div>
        <router-link to="/products" class="btn btn-outline-primary-luxury rounded-0 px-4 py-2 text-uppercase tracking-widest text-sm fw-bold">
          Xem Bộ Sưu Tập
        </router-link>
      </div>

      <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
        <div class="col" v-for="product in data.products" :key="product.id">
          <div class="product-card h-100 position-relative group bg-white">
            
            <div class="position-relative overflow-hidden bg-light product-img-wrapper">
              <button class="wishlist-btn position-absolute top-0 end-0 m-3 z-index-2 border-0 bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-suit-heart fs-5 text-muted hover-text-accent transition-colors" style="margin-top: 2px;"></i>
              </button>
              
              <span v-if="product.is_new" class="badge bg-primary-luxury position-absolute top-0 start-0 m-3 px-3 py-2 rounded-0 tracking-widest shadow-sm z-index-2">MỚI</span>
              
              <router-link :to="`/product/${product.slug}`" class="d-block ratio ratio-1x1">
                <img :src="getImageUrl(product.thumbnail_image)" class="object-fit-cover transition-transform duration-700 hover-scale" alt="Product" @error="handleImageError">
              </router-link>
              
              <div class="quick-view-overlay position-absolute bottom-0 start-0 w-100 p-0 z-index-2 translate-y-100 group-hover-translate-y-0 transition-transform duration-300">
                <button class="btn btn-primary-luxury w-100 rounded-0 fw-bold tracking-widest text-uppercase py-3 shadow-none border-0 fs-6">
                  <i class="bi bi-bag me-2"></i> Thêm Vào Giỏ
                </button>
              </div>
            </div>

            <div class="text-center pt-4 pb-3 px-2">
              <router-link :to="`/product/${product.slug}`" class="text-decoration-none">
                <h6 class="text-dark font-serif text-truncate mb-2 cursor-pointer hover-text-primary" style="font-size: 1.15rem;">{{ product.name }}</h6>
              </router-link>
              <div class="price-wrap mt-2 d-flex justify-content-center align-items-center gap-2">
                <span class="text-primary-luxury fw-bold fs-5">{{ formatCurrency(product.base_price) }}</span>
                <span v-if="product.promotional_price" class="text-muted text-decoration-line-through small">{{ formatCurrency(product.promotional_price) }}</span>
              </div>

            </div>

          </div>
        </div>
      </div>
    </section>

    <section class="blog-section py-5" style="background-color: #f9f9f9;">
      <div class="container py-4">
        <div class="text-center mb-5">
          <h6 class="text-primary-luxury tracking-widest text-uppercase fw-bold mb-2">Cẩm Nang</h6>
          <h3 class="font-serif fw-bold text-dark mb-3 display-6">Kiến Thức Trang Sức</h3>
          <div class="divider-gold mx-auto"></div>
        </div>
        <div class="row g-4">
          <div class="col-md-4" v-for="i in 3" :key="i">
            <div class="blog-card group cursor-pointer bg-white p-3 shadow-sm border border-light">
              <div class="ratio ratio-4x3 overflow-hidden mb-3">
                <img :src="`https://images.unsplash.com/photo-1573408301145-b98c46544ea6?q=80&w=600&auto=format&fit=crop&sig=${i}`" loading="lazy" class="object-fit-cover transition-transform duration-700 group-hover-scale" alt="Blog">
              </div>
              <small class="text-gold tracking-widest text-uppercase fw-bold">Tư Vấn</small>
              <h5 class="font-serif fw-bold text-primary-luxury mt-2 group-hover-text-accent transition-colors">
                {{ ['Bí Quyết Chọn Nhẫn Cầu Hôn Kim Cương Hoàn Hảo', 'Cách Bảo Quản Trang Sức Vàng 18K Sáng Bóng Trọn Đời', 'Xu Hướng Trang Sức Ngọc Trai Lên Ngôi Năm Nay'][i-1] }}
              </h5>
              <p class="text-dark fw-light mt-2 mb-0" style="font-size: 0.9rem;">Khám phá những bí quyết độc quyền từ chuyên gia kim hoàn SORA để...</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="membership-banner py-5 position-relative" style="background-color: var(--color-primary);">
      <div class="container position-relative z-index-2 py-5 text-center">
        <i class="bi bi-gem text-gold display-4 mb-3"></i>
        <h2 class="font-serif fw-bold text-gold display-5 mb-4">SORA Privilege Club</h2>
        <p class="lead fw-light text-white opacity-100 max-w-600 mx-auto mb-5">Đăng ký thành viên để tận hưởng đặc quyền chăm sóc trang sức trọn đời và chiết khấu VIP dành riêng cho bạn.</p>
        
        <div class="row justify-content-center g-4 mb-5">
          <div class="col-md-3" v-for="tier in data.tiers" :key="tier.id">
            <div class="p-4 border border-gold rounded-0 bg-dark h-100 shadow-lg transition-transform hover-translate-up" style="background-color: #111 !important;">
              <h5 class="text-gold font-serif fw-bold display-6 mb-3">{{ tier.name }}</h5>
              <div class="divider-gold mx-auto mb-3" style="width: 30px; height: 1px;"></div>
              <ul class="list-unstyled text-center small mb-0 text-white opacity-100 lh-lg">
                <li>Chiết khấu đặc quyền {{ tier.discount_percent }}%</li>
                <li>{{ tier.yearly_service_quota }} lần Spa miễn phí/năm</li>
                <li>Ưu tiên nhận BST mới</li>
              </ul>
            </div>
          </div>
        </div>
        <router-link to="/register" class="btn btn-gold btn-lg px-5 py-3 text-uppercase tracking-widest fw-bold rounded-0 shadow-lg">
          Tạo Tài Khoản Ngay
        </router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
import { reactive, onMounted } from 'vue';
import Swal from 'sweetalert2';

const data = reactive({
  banners: [],
  coupons: [],
  categories: [],
  products: [],
  tiers: []
});

const API_BASE = 'http://127.0.0.1:8000/api'; 
const getImageUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : '/default-luxury.jpg';

const handleImageError = (e) => { 
  e.target.src = 'https://images.unsplash.com/photo-1605100804763-247f67b2548e?q=80&w=600&auto=format&fit=crop'; 
};

const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
const formatShortCurrency = (value) => {
  if (value >= 1000000) return (value / 1000000) + 'Tr';
  if (value >= 1000) return (value / 1000) + 'K';
  return value;
};

const fetchHomepageData = async () => {
  try {
    const response = await fetch(`${API_BASE}/client/home-data`, {
      headers: { 'Accept': 'application/json' }
    });
    
    const result = await response.json();

    if (result.success) {
      data.banners = result.data.banners || [];
      data.coupons = result.data.coupons || [];
      data.categories = result.data.categories || [];
      data.products = result.data.products || [];
      data.tiers = result.data.tiers || [];
    }
  } catch (error) {
    console.error("Lỗi tải trang chủ:", error);
  }
};

const saveCoupon = (code) => {
  Swal.fire({
    icon: 'success',
    title: 'Lưu mã thành công!',
    text: `Mã ${code} đã được thêm vào ví voucher của bạn.`,
    confirmButtonColor: '#9f273b'
  });
};

onMounted(() => {
  fetchHomepageData();
});
</script>

<style scoped>
:root {
  --color-primary: #9f273b; 
  --color-gold: #e7ce7d;    
  --color-accent: #cc1e2e;  
}

@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Montserrat:wght@300;400;500;600&display=swap');

.font-luxury { font-family: 'Montserrat', sans-serif; }
.font-serif { font-family: 'Playfair Display', serif; }
.tracking-widest { letter-spacing: 0.15em; }
.z-index-1 { z-index: 1; }
.z-index-2 { z-index: 2; }
.z-index-max { z-index: 9999; }

.text-gold { color: #e7ce7d !important; }
.text-primary-luxury { color: #9f273b !important; }
.bg-primary-luxury { background-color: #9f273b !important; }
.bg-accent-red { background-color: #cc1e2e !important; color: white; }
.border-gold { border-color: #e7ce7d !important; }
.divider-gold { width: 40px; height: 2px; background-color: #e7ce7d; }

.btn-primary-luxury {
  background-color: #9f273b; color: #fff; border: 1px solid #9f273b; transition: all 0.3s;
}
.btn-primary-luxury:hover {
  background-color: #111; border-color: #111; color: #fff;
}
.btn-outline-primary-luxury {
  background-color: transparent; color: #9f273b; border: 1px solid #9f273b; transition: all 0.3s;
}
.btn-outline-primary-luxury:hover {
  background-color: #9f273b; color: #fff;
}
.btn-outline-gold {
  background-color: transparent; color: #e7ce7d; border: 1px solid #e7ce7d; transition: all 0.3s;
}
.btn-outline-gold:hover {
  background-color: #e7ce7d; color: #111; border-color: #e7ce7d;
}
.btn-gold {
  background-color: #e7ce7d; color: #111; border: 1px solid #e7ce7d; transition: all 0.3s;
}
.btn-gold:hover {
  background-color: #d1b764; border-color: #d1b764; color: #000; transform: translateY(-2px);
}
.hover-gold-btn:hover {
  background-color: #e7ce7d !important; border-color: #e7ce7d !important; color: #111 !important;
}

.hero-carousel { height: 85vh; min-height: 600px; background: #111; }
.hero-img { height: 85vh; min-height: 600px; opacity: 0.6; }
.carousel-overlay { position: absolute; inset: 0; background: radial-gradient(circle, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.8) 100%); }
.shadow-text { text-shadow: 2px 2px 8px rgba(0,0,0,0.7); }

.coupon-scroll-container { overflow-x: auto; scrollbar-width: none; }
.coupon-scroll-container::-webkit-scrollbar { display: none; }
.coupon-card { width: 320px; }
.border-end-dashed { border-right: 1px dashed rgba(255,255,255,0.3); }

.group:hover .group-hover-scale { transform: scale(1.05); }
.group:hover .group-hover-text-primary { color: #9f273b !important; }
.group:hover .group-hover-text-accent { color: #cc1e2e !important; }
.scale-0 { transform: scaleX(0); }
.group:hover .group-hover-scale-100 { transform: scaleX(1); }
.transition-colors { transition: color 0.3s ease; }
.transition-transform { transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1); }
.hover-translate-up:hover { transform: translateY(-10px); }
.filter-brightness { filter: brightness(0.95); transition: filter 0.5s; }
.group:hover .filter-brightness { filter: brightness(1); }

/* --- PRODUCT CARD STYLES --- */
.product-card { 
  border: 1px solid transparent; 
  transition: all 0.4s ease; 
}
.product-card:hover { 
  box-shadow: 0 10px 30px rgba(0,0,0,0.06); 
  border-color: rgba(159, 39, 59, 0.15); 
}
.product-img-wrapper { border-bottom: 1px solid #f1f1f1; }

.wishlist-btn { opacity: 0.8; transition: all 0.3s ease; }
.group:hover .wishlist-btn { opacity: 1; transform: scale(1.05); }
.wishlist-btn:hover i { color: var(--color-accent) !important; }

.hover-scale { transition: transform 0.8s cubic-bezier(0.165, 0.84, 0.44, 1); }
.group:hover .hover-scale { transform: scale(1.1); }
.hover-text-primary:hover { color: #9f273b !important; }

.translate-y-100 { transform: translateY(100%); opacity: 0; }
.group:hover .group-hover-translate-y-0 { transform: translateY(0); opacity: 1; }
.quick-view-overlay { background: transparent; }
</style>