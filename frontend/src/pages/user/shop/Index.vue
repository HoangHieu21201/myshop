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

    <!-- DANH MỤC NỔI BẬT (CATEGORY SECTION) - Đã căn giữa -->
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
          <div class="spinner-border text-dark" style="width: 2rem; height: 2rem; border-width: 0.15em;" role="status"></div>
        </div>

        <div v-else class="row g-3 g-md-4">
          <div class="col-6 col-md-3" v-for="cat in categories.slice(0, 4)" :key="cat.id">
            <div class="category-card group position-relative overflow-hidden cursor-pointer bg-light" @click="filterByCategory(cat.value || cat.slug)">
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
            <div class="spinner-border text-dark" style="width: 2.5rem; height: 2.5rem; border-width: 0.15em;" role="status"></div>
          </div>

          <div v-else class="product-grid">
            <!-- Render danh sách sản phẩm -->
            <template v-for="(product, index) in allProducts" :key="product.id">
              
              <!-- EDITORIAL BLOCK 1: NHẪN CẦU HÔN (Kèm Video) -->
              <div v-if="index === 3" class="editorial-block bg-light d-flex flex-column flex-md-row align-items-stretch w-100 overflow-hidden mb-4 mt-2">
                <!-- Text Section -->
                <div class="ed-text d-flex flex-column justify-content-center p-5 bg-white" style="flex: 1;">
                  <h2 class="playfair-font fw-normal text-dark mb-3" style="font-size: 2.5rem;">Nhẫn Cầu Hôn</h2>
                  <p class="text-muted mb-4 pe-lg-4" style="font-size: 0.95rem; line-height: 1.6;">Một chiếc nhẫn lấp lánh trao tay, mở ra hành trình yêu thương viên mãn và vĩnh cửu.</p>
                  <div>
                    <button class="btn btn-outline-dark rounded-0 px-4 py-2 text-uppercase fw-bold ls-wider transition-colors hover-bg-dark hover-text-white" style="font-size: 0.75rem;">
                      Khám Phá
                    </button>
                  </div>
                </div>
                
                <div class="ed-image bg-light" style="flex: 1;">
                  <img src="https://images.unsplash.com/photo-1605100804763-247f66126e28?q=80&w=800&auto=format&fit=crop" class="w-100 h-100 object-fit-cover" alt="Nhẫn cầu hôn">
                </div>
                <div class="ed-video bg-dark" style="flex: 1;">
                  <video autoplay loop muted playsinline class="w-100 h-100 object-fit-cover" style="opacity: 0.9;">
                    <source src="https://cdn.pixabay.com/video/2020/02/24/32822-394132332_large.mp4" type="video/mp4">
                  </video>
                </div>
              </div>

             
              <div v-if="index === 7" class="editorial-block bg-light d-flex flex-column flex-md-row align-items-stretch w-100 overflow-hidden mb-4 mt-2">
                 
                 <div class="ed-image" style="flex: 1.5;">
                  <img src="https://images.unsplash.com/photo-1596944924616-7b38e7cfac36?q=80&w=1000&auto=format&fit=crop" class="w-100 h-100 object-fit-cover" alt="Couple Rings">
                </div>
              
                
                <div class="ed-image bg-white d-flex align-items-center justify-content-center p-4" style="flex: 1;">
                  <img src="https://images.unsplash.com/photo-1611591437281-460bfbe1220a?q=80&w=600&auto=format&fit=crop" class="img-fluid object-fit-contain mix-blend-multiply" alt="Wedding Bands">
                </div>
             
                
                <div class="ed-text d-flex flex-column justify-content-center p-5 bg-light" style="flex: 1;">
                  <h2 class="playfair-font fw-normal text-dark mb-3" style="font-size: 2.5rem;">Nhẫn Cưới</h2>
                  <p class="text-muted mb-4" style="font-size: 0.95rem; line-height: 1.6;">Biểu tượng của tình yêu và lời thề ước, khi hai tâm hồn trở thành một.</p>
                  <div>
                    <button class="btn btn-outline-dark rounded-0 px-4 py-2 text-uppercase fw-bold ls-wider transition-colors hover-bg-dark hover-text-white" style="font-size: 0.75rem;">
                      Xem Thêm
                    </button>
                  </div>
                </div>
              </div>

              
              <div class="product-card group cursor-pointer d-flex flex-column mb-3 bg-white position-relative">
                
              
                <div class="img-wrapper position-relative bg-light mb-3 overflow-hidden d-flex align-items-center justify-content-center" style="aspect-ratio: 1/1;">
                  
                  <!-- Tags -->
                  <div class="position-absolute top-0 start-0 w-100 p-3 d-flex justify-content-between z-3 pointer-events-none">
                    <span v-if="product.is_new || index % 5 === 0" class="badge-custom bg-dark text-white shadow-sm">MỚI</span>
                    <span v-if="product.promotional_price" class="badge-custom bg-danger text-white shadow-sm ms-auto">SALE</span>
                  </div>
                  
                  <!-- Ảnh Chính (Hiển thị mặc định, mờ đi khi hover) -->
                  <img :src="getImageUrl(product.thumbnail_image)" 
                       :alt="product.name" 
                       class="product-img-main w-100 h-100 object-fit-cover transition-all duration-500 bg-white">
                  
                  <!-- Ảnh Phụ (Hiển thị khi hover) -->
                  <img :src="getHoverImageUrl(product)" 
                       :alt="product.name + ' on hand'" 
                       class="product-img-hover position-absolute top-0 start-0 w-100 h-100 object-fit-cover transition-all duration-500 opacity-0 group-hover-opacity-100 z-2">
                  
                  <!-- Nút Thêm Vào Giỏ (Đã fix lỗi nhảy div CSS) -->
                  <div class="quick-action position-absolute start-0 w-100 px-3 z-3 text-center transition-all duration-300">
                    <button class="btn w-100 text-uppercase fw-bold ls-wider py-2 shadow-sm btn-add-cart" style="font-size: 0.75rem;">
                      Thêm Vào Giỏ
                    </button>
                  </div>
                </div>
                
                <!-- Thông tin sản phẩm -->
                <div class="product-info text-start d-flex flex-column flex-grow-1 px-1">
                  <h3 class="product-name text-dark fw-bold mb-1" style="font-size: 0.95rem;">{{ product.name }}</h3>
                  <p class="product-desc text-muted mb-2" style="font-size: 0.8rem;">{{ product.short_description || 'Vàng 18K, Kim cương tự nhiên' }}</p>
                  
                  <div class="mt-auto d-flex align-items-baseline gap-2">
                    <p class="product-price fw-bold text-dark fs-6 mb-0">
                      {{ formatPrice(product.promotional_price || product.base_price) }}
                    </p>
                    <p v-if="product.promotional_price" class="text-muted text-decoration-line-through mb-0" style="font-size: 0.8rem;">
                      {{ formatPrice(product.base_price) }}
                    </p>
                  </div>
                </div>
              </div>

            </template>
          </div>

          <!-- Không có sản phẩm -->
          <div v-if="allProducts.length === 0 && !isLoadingProducts" class="text-center py-5">
            <i class="fa-regular fa-folder-open fs-1 text-muted mb-3"></i>
            <p class="text-muted playfair-font fs-5">Không tìm thấy sản phẩm nào phù hợp với bộ lọc.</p>
            <button @click="resetFilters" class="btn btn-dark text-uppercase ls-widest mt-2 px-4 py-2 hover-bg-light transition-colors">Xóa Bộ Lọc</button>
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


    <!-- SECTION: NHẬN TƯ VẤN (FORM) -->
 

    <!-- SECTION: DỊCH VỤ -->
    <section class="container-fluid px-4 py-5 my-3 text-center">
      <h3 class="playfair-font fs-2 text-dark mb-5">Dịch vụ</h3>
      <div class="row g-4 justify-content-center px-md-5">
        <div class="col-md-4">
          <div class="mb-3">
            <i class="fa-solid fa-truck-fast fs-1 text-dark opacity-75"></i>
          </div>
          <h5 class="fw-bold fs-6 text-dark mb-2">Giao hàng miễn phí toàn quốc</h5>
          <p class="text-muted mx-auto mb-2" style="font-size: 0.85rem; max-width: 250px;">Giao hàng nhanh trên toàn quốc hoàn toàn miễn phí</p>
          <a href="#" class="text-dark text-decoration-none fw-bold" style="font-size: 0.75rem;">Khám phá ngay <i class="fa-solid fa-chevron-right ms-1" style="font-size: 0.6rem;"></i></a>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <i class="fa-regular fa-gem fs-1 text-dark opacity-75"></i>
          </div>
          <h5 class="fw-bold fs-6 text-dark mb-2">Chế độ thu đổi đến 100%</h5>
          <p class="text-muted mx-auto mb-2" style="font-size: 0.85rem; max-width: 250px;">Thu đổi nhanh chóng, với chính sách lên đến 100%</p>
          <a href="#" class="text-dark text-decoration-none fw-bold" style="font-size: 0.75rem;">Khám phá ngay <i class="fa-solid fa-chevron-right ms-1" style="font-size: 0.6rem;"></i></a>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <i class="fa-solid fa-shield-halved fs-1 text-dark opacity-75"></i>
          </div>
          <h5 class="fw-bold fs-6 text-dark mb-2">Bảo hành trọn đời</h5>
          <p class="text-muted mx-auto mb-2" style="font-size: 0.85rem; max-width: 250px;">Miễn phí bảo hành trọn đời cho tất cả trang sức</p>
          <a href="#" class="text-dark text-decoration-none fw-bold" style="font-size: 0.75rem;">Khám phá ngay <i class="fa-solid fa-chevron-right ms-1" style="font-size: 0.6rem;"></i></a>
        </div>
      </div>
    </section>

    <!-- SECTION: HỆ THỐNG CỬA HÀNG -->
    <section class="container-fluid px-4 py-5 mb-4">
      <div class="row align-items-center bg-light ms-md-2 me-md-2" style="min-height: 450px;">
        <div class="col-md-6 p-0 h-100">
          <img src="https://quocanhdiamond.vn/wp-content/uploads/2023/03/Lac-tay-kim-cuong-nu-bong-hoa-mat-troi-sieu-vip-711x400.jpg" class="w-100 h-100 object-fit-cover" alt="Hệ thống cửa hàng" style="min-height: 450px;">
        </div>
        <div class="col-md-6 p-5">
          <div class="ps-md-4">
            <h3 class="playfair-font fs-2 text-dark mb-3">Hệ thống cửa hàng</h3>
            <p class="text-muted mb-4" style="font-size: 1.4rem; line-height: 1.6;">Thế giới trang sức cùng không gian mua sắm tuyệt vời đang chờ bạn ghé thăm.</p>
              <p class="text-muted mb-4" style="font-size: 1rem; line-height: 1.6;">Trang sức thiết kế riêng mang đến vẻ đẹp độc bản, giúp bạn thể hiện cá tính và dấu ấn riêng trong từng chi tiết. Mỗi sản phẩm được chế tác tỉ mỉ theo yêu cầu, từ kiểu dáng đến chất liệu, đảm bảo không trùng lặp và phù hợp hoàn hảo với người sở hữu. Đây không chỉ là món trang sức, mà còn là câu chuyện và phong cách của chính bạn.</p>
           
            <br>
            <button class="btn btn-outline-dark rounded-0 px-4 py-2 text-uppercase fw-bold ls-wider hover-bg-dark hover-text-white transition-colors" style="font-size: 0.8rem; border-color: #ddd;">
              Đặt Lịch Hẹn
            </button>
          </div>
        </div>
      </div>
    </section>
   <section class="py-5" style="background-color: #fcf9f5;">
      <div class="container py-4 text-center" style="max-width: 650px;">
        <h3 class="playfair-font fs-2 text-dark mb-2">Nhận tư vấn từ SORA</h3>
        <p class="text-muted mb-4 pb-2" style="font-size: 0.95rem;">Đăng ký ngay bên dưới để nhận được sự hỗ trợ từ chúng tôi.</p>
        
        <!-- Icon nhỏ trang trí ở giữa -->
        <div class="d-flex align-items-center justify-content-center mb-4 opacity-50">
          <hr class="w-25 border-dark m-0">
          <i class="fa-regular fa-gem mx-3 fs-5 text-dark"></i>
          <hr class="w-25 border-dark m-0">
        </div>

        <form @submit.prevent class="row g-3 text-start">
          <div class="col-12">
            <input type="text" class="form-control rounded-0 custom-input py-2 px-3" placeholder="Họ và tên">
          </div>
          <div class="col-md-6">
            <input type="tel" class="form-control rounded-0 custom-input py-2 px-3" placeholder="Số điện thoại">
          </div>
          <div class="col-md-6">
            <select class="form-select rounded-0 custom-input py-2 px-3 text-muted">
              <option selected disabled>Chọn tỉnh thành</option>
              <option value="hn">Hà Nội</option>
              <option value="hcm">TP. Hồ Chí Minh</option>
              <option value="dn">Đà Nẵng</option>
            </select>
          </div>
          <div class="col-12">
            <select class="form-select rounded-0 custom-input py-2 px-3 text-muted">
              <option selected disabled>Sản phẩm cần tư vấn</option>
              <option value="nhan-cuoi">Nhẫn cưới</option>
              <option value="nhan-cau-hon">Nhẫn cầu hôn</option>
              <option value="trang-suc">Trang sức khác</option>
            </select>
          </div>
          <div class="col-12 text-center mt-4">
            <button class="btn btn-consult px-5 py-2 text-uppercase fw-bold text-white ls-wider">Tư Vấn Ngay</button>
          </div>
        </form>
      </div>
    </section>
  </div>
  
  <div v-else class="vh-100 w-100 d-flex flex-column align-items-center justify-content-center bg-white">
      <div class="spinner-border text-dark mb-4" style="width: 3rem; height: 3rem; border-width: 0.15em;" role="status"></div>
      <h2 class="playfair-font fw-bold ls-widest text-uppercase fs-5 text-dark">Đang Tải Dữ Liệu...</h2>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useRoute } from 'vue-router'; 

const route = useRoute();
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


const filterGroups = ref([
  {
    title: 'Danh Mục', key: 'categories', isOpen: true,
    options: [
      { label: 'Nhẫn Cầu Hôn', value: 'nhan-cau-hon' },
      { label: 'Nhẫn Cưới', value: 'nhan-cuoi' },
      { label: 'Dây Chuyền & Mặt Dây', value: 'day-chuyen' },
      { label: 'Hoa Tai', value: 'hoa-tai' },
      { label: 'Vòng & Lắc Tay', value: 'vong-tay' }
    ]
  },
  {
    title: 'Bộ Sưu Tập', key: 'collections', isOpen: true,
    options: [
      { label: 'Solitaire Classic', value: 'solitaire' },
      { label: 'Halo Brilliance', value: 'halo' },
      { label: 'Vintage Inspired', value: 'vintage' },
      { label: 'Eternity Bands', value: 'eternity' }
    ]
  },
  {
    title: 'Chất Liệu', key: 'metals', isOpen: false,
    options: [
      { label: 'Platinum (Bạch Kim)', value: 'platinum' },
      { label: 'Vàng Trắng 18K', value: 'vang-trang' },
      { label: 'Vàng Hồng 18K', value: 'vang-hong' },
      { label: 'Vàng Vàng 18K', value: 'vang-vang' }
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

const getHoverImageUrl = (product) => {
  if (product.variants && product.variants.length > 0 && product.variants[0].image_url) {
     return getImageUrl(product.variants[0].image_url);
  }
  const dummyLifestyleImages = [
    'https://images.unsplash.com/photo-1596944924616-7b38e7cfac36?q=80&w=600&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?q=80&w=600&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1601121141461-9d6647bca1ed?q=80&w=600&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1611591437281-460bfbe1220a?q=80&w=600&auto=format&fit=crop'
  ];
  return dummyLifestyleImages[product.id % 4];
};

// === CALL API DANH MỤC ===
const fetchCategories = async () => {
    isLoadingCategories.value = true;
    try {
        const response = await fetch(`${API_BASE_URL}/api/shop/${shopSlug.value}/categories`);
        const data = await response.json();
        if(data && data.success && data.data.length > 0) {
            categories.value = data.data;
        } else {
            generateFakeCategories();
        }
    } catch (error) {
        console.error("Lỗi lấy danh mục, sử dụng dữ liệu mẫu:", error);
        generateFakeCategories();
    } finally {
        isLoadingCategories.value = false;
    }
};

const generateFakeCategories = () => {
    categories.value = [
        { id: 1, name: 'Nhẫn Cầu Hôn', value: 'nhan-cau-hon', thumbnail: 'https://images.unsplash.com/photo-1605100804763-247f66126e28?q=80&w=600&auto=format&fit=crop' },
        { id: 2, name: 'Nhẫn Cưới', value: 'nhan-cuoi', thumbnail: 'https://images.unsplash.com/photo-1596944924616-7b38e7cfac36?q=80&w=600&auto=format&fit=crop' },
        { id: 3, name: 'Dây Chuyền', value: 'day-chuyen', thumbnail: 'https://images.unsplash.com/photo-1599643478524-fb66f70d00f0?q=80&w=600&auto=format&fit=crop' },
        { id: 4, name: 'Hoa Tai', value: 'hoa-tai', thumbnail: 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?q=80&w=600&auto=format&fit=crop' }
    ];
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
             generateFakeProducts();
        }
    } catch (error) {
        console.error("Lỗi lấy sản phẩm, sử dụng dữ liệu mẫu:", error);
        generateFakeProducts();
    } finally {
        isLoadingProducts.value = false;
    }
};

const generateFakeProducts = () => {
   const fakes = [];
   for(let i=1; i<=12; i++) {
      fakes.push({
         id: i,
         name: i % 2 === 0 ? 'Nhẫn Kim Cương Solitaire' : 'Nhẫn Cưới Halo Platinum',
         short_description: 'Vàng trắng 18K, Kim cương thiên nhiên',
         base_price: 45000000 + (i * 1000000),
         promotional_price: i % 3 === 0 ? 39000000 + (i * 500000) : null,
         is_new: i % 4 === 0,
         thumbnail_image: null 
      });
   }
   allProducts.value = fakes;
   pagination.value = { current_page: 1, last_page: 3, total: 36 };
};



const filterByCategory = (categoryValue) => {
  if (!filters.categories.includes(categoryValue)) {
    filters.categories.push(categoryValue);
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

onMounted(() => { 
  Promise.all([fetchCategories(), fetchProducts(1)]).then(() => {
      isPageLoading.value = false;
  });
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap');


.shop-page {
  font-family: 'Inter', sans-serif;
  color: #333;
}
.playfair-font {
  font-family: 'Playfair Display', serif;
}
.ls-widest { letter-spacing: 0.15em; }
.ls-wider { letter-spacing: 0.08em; }

/* === UTILITIES & TRANSITIONS === */
.cursor-pointer { cursor: pointer; }
.transition-all { transition: all 0.4s ease; }
.transition-colors { transition: color 0.3s ease, background-color 0.3s ease, border-color 0.3s ease; }
.transition-transform { transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94); }
.duration-300 { transition-duration: 0.3s; }
.duration-500 { transition-duration: 0.5s; }
.duration-700 { transition-duration: 0.7s; }

.rotate-180 { transform: rotate(180deg); }
.opacity-0 { opacity: 0; }
.pointer-events-none { pointer-events: none; }
.mix-blend-multiply { mix-blend-mode: multiply; }
.text-shadow-sm { text-shadow: 0 2px 10px rgba(0,0,0,0.5); }

.hover-text-dark:hover { color: #000 !important; }
.hover-text-gray:hover { color: #666 !important; }

/* === BUTTONS === */
.hover-bg-light:hover {
  background-color: #f8f9fa !important;
  color: #000 !important;
  border-color: #f8f9fa !important;
}
.hover-bg-dark:hover {
  background-color: #111 !important;
  color: #fff !important;
  border-color: #111 !important;
}

/* Nút thêm vào giỏ UI mới */
.btn-add-cart {
  background-color: #111;
  color: #fff;
  border: 1px solid #111;
  transition: all 0.3s;
}
.btn-add-cart:hover {
  background-color: #f8f9fa !important;
  color: #111 !important;
  border-color: #111 !important;
}

/* Nút Submit Form màu nâu sữa (Beige/Brown) */
.btn-consult {
  background-color: #bfa188;
  border: 1px solid #bfa188;
  transition: all 0.3s;
}
.btn-consult:hover {
  background-color: #a88a72;
  border-color: #a88a72;
}

/* Input form tuỳ chỉnh */
.custom-input {
  border: 1px solid #ccc;
  background-color: transparent;
}
.custom-input:focus {
  box-shadow: none;
  border-color: #bfa188;
}

/* === CATEGORY SECTION CAO CẤP === */
.category-section {
  background-color: #fafafa;
}
.overlay-gradient {
  background: linear-gradient(to top, rgba(0,0,0,0.65) 0%, rgba(0,0,0,0) 45%);
  transition: background 0.5s ease;
}
.group:hover .overlay-gradient {
  background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 60%);
}
.group:hover .group-hover-scale { transform: scale(1.08); }
.group-hover-translate-y { transform: translateY(10px); }
.group:hover .group-hover-translate-y { transform: translateY(0); }

/* === BỘ LỌC (SIDEBAR) CAO CẤP === */
.filter-title {
  color: #111;
  letter-spacing: 0.02em;
}
.filter-options label {
  display: block;
  position: relative;
  padding-left: 32px;
  margin-bottom: 16px;
  cursor: pointer;
  font-size: 0.95rem;
  color: #444;
  user-select: none;
  font-weight: 400;
}
.filter-options label:hover { color: #000; }
.filter-options input { position: absolute; opacity: 0; cursor: pointer; height: 0; width: 0; }

/* Checkbox Vuông */
.custom-checkbox .checkmark {
  position: absolute; top: 2px; left: 0; height: 18px; width: 18px;
  border: 1px solid #ccc; background-color: transparent; border-radius: 2px;
}
.custom-checkbox:hover input ~ .checkmark { border-color: #000; }
.custom-checkbox input:checked ~ .checkmark { background-color: #000; border-color: #000; }
.custom-checkbox input:checked ~ .checkmark:after {
  content: ""; position: absolute; display: block;
  left: 6px; top: 2px; width: 5px; height: 10px;
  border: solid white; border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

/* Radio Tròn */
.custom-radio .checkmark {
  position: absolute; top: 3px; left: 0; height: 18px; width: 18px;
  border: 1px solid #ccc; border-radius: 50%; background-color: transparent;
}
.custom-radio:hover input ~ .checkmark { border-color: #000; }
.custom-radio input:checked ~ .checkmark { border: 5px solid #000; }

/* === GRID SẢN PHẨM & HIỆU ỨNG HOVER === */
.product-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2.5rem 1.5rem;
}

/* Hiệu ứng Đổi ảnh khi Hover */
.product-img-main {
  z-index: 1;
}
.group:hover .product-img-main {
  opacity: 0;
}
.group:hover .group-hover-opacity-100 {
  opacity: 1;
}

/* Slider Ngang Ẩn Thanh Cuộn */
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none; 
}

/* === BUG FIX: Nút "Thêm vào giỏ" === */
/* Xóa bỏ translateY cũ gây vỡ giao diện Bootstrap. Sử dụng bottom để thay thế */
.quick-action {
  bottom: -20px; /* Nằm ẩn dưới thẻ */
  opacity: 0;
  visibility: hidden;
}
.group:hover .quick-action {
  bottom: 15px; /* Trượt nhẹ lên */
  opacity: 1;
  visibility: visible;
}


/* Badge (MỚI, SALE) */
.badge-custom {
  font-size: 0.65rem; font-weight: 600; letter-spacing: 0.1em;
  padding: 5px 12px; border-radius: 0;
}

/* === EDITORIAL BLOCKS (Tràn cột) === */
.editorial-block {
  grid-column: 1 / -1; 
  min-height: 450px;
}
.editorial-block .ed-text h2 {
  letter-spacing: -0.02em;
}

/* Responsive Grid */
@media (max-width: 1200px) {
  .product-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .product-grid { grid-template-columns: repeat(1, 1fr); gap: 1.5rem; }
  .editorial-block { flex-direction: column !important; min-height: auto; }
  .editorial-block > div { min-height: 300px; }
}
</style>