<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

// --- CONFIG ---
// Lấy Base URL cho API (Mặc định: http://127.0.0.1:8000/api)
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';
// Lấy URL gốc cho hình ảnh bằng cách cắt đuôi /api (VD: http://127.0.0.1:8000)
const BACKEND_URL = API_BASE_URL.replace(/\/api\/?$/, '');

const ITEMS_PER_PAGE = 4; 
const SITE_NAME = 'ThinkHub Blog'; 

// DANH SÁCH CATEGORY 
const CATEGORIES = [
    { name: 'Xu hướng trang sức', icon: 'bi-star' },
    { name: 'Bí quyết chọn trang sức', icon: 'bi-cpu' },
    { name: 'Trang sức theo dịp', icon: 'bi-tools' },
    { name: 'Kiến thức đá quý & kim loại', icon: 'bi-gift' }
];

// --- STATE ---
const posts = ref([]);
const popularPosts = ref([]); 
const isLoading = ref(true);
const searchQuery = ref(''); 
const authorQuery = ref(''); 
const categoryQuery = ref(''); 
const currentPage = ref(1);

// --- HELPER METHODS ---
const toSlug = (str) => {
    if (!str) return '';
    str = str.toLowerCase();
    str = str.replace(/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/g, 'a');
    str = str.replace(/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/g, 'e');
    str = str.replace(/(ì|í|ị|ỉ|ĩ)/g, 'i');
    str = str.replace(/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/g, 'o');
    str = str.replace(/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/g, 'u');
    str = str.replace(/(ỳ|ý|ỵ|ỷ|ỹ)/g, 'y');
    str = str.replace(/(đ)/g, 'd');
    str = str.replace(/([^0-9a-z-\s])/g, '');
    str = str.replace(/(\s+)/g, '-');
    str = str.replace(/^-+/g, '');
    str = str.replace(/-+$/g, '');
    return str;
};

const formatViews = (count) => {
    if (!count) return 0;
    if (count >= 1000000) return (count / 1000000).toFixed(1) + 'M';
    if (count >= 1000) return (count / 1000).toFixed(1) + 'K';
    return count.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
};

const updateListingSeo = () => {
    document.title = `Tin tức Công Nghệ & Thủ Thuật - ${SITE_NAME}`;
    const desc = "Cập nhật xu hướng công nghệ mới nhất, đánh giá sản phẩm, thủ thuật và mẹo hay từ đội ngũ chuyên gia.";
    const url = window.location.href;
    const image = 'https://placehold.co/1200x630?text=News+Page'; 

    const setMetaName = (name, content) => {
        let element = document.querySelector(`meta[name="${name}"]`);
        if (!element) {
            element = document.createElement('meta');
            element.setAttribute('name', name);
            document.head.appendChild(element);
        }
        element.setAttribute('content', content);
    };

    const setMetaProperty = (property, content) => {
        let element = document.querySelector(`meta[property="${property}"]`);
        if (!element) {
            element = document.createElement('meta');
            element.setAttribute('property', property);
            document.head.appendChild(element);
        }
        element.setAttribute('content', content);
    };

    let canonical = document.querySelector('link[rel="canonical"]');
    if (!canonical) {
        canonical = document.createElement('link');
        canonical.setAttribute('rel', 'canonical');
        document.head.appendChild(canonical);
    }
    canonical.setAttribute('href', url.split('?')[0]);

    setMetaName('description', desc);
    setMetaProperty('og:title', document.title);
    setMetaProperty('og:description', desc);
    setMetaProperty('og:image', image);
    setMetaProperty('og:url', url);
    setMetaProperty('og:type', 'website');

    setMetaName('twitter:card', 'summary_large_image');
    setMetaName('twitter:title', document.title);
    setMetaName('twitter:description', desc);
    setMetaName('twitter:image', image);
};

let debounceTimer = null;
const debounce = (func, delay) => {
    return function (...args) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
};

const getFullImage = (path) => {
    if (!path) return 'https://placehold.co/800x450?text=No+Image';
    if (path.startsWith('http')) return path;
    const cleanPath = path.startsWith('/') ? path.substring(1) : path;
    return `${BACKEND_URL}/${cleanPath}`;
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('vi-VN', {
        day: '2-digit', month: '2-digit', year: 'numeric'
    });
};

const getExcerpt = (post, length = 180) => {
    if (post.excerpt) return post.excerpt;
    if (post.content) {
        const plainText = post.content.replace(/<[^>]+>/g, '');
        return plainText.length > length ? plainText.substring(0, length) + '...' : plainText;
    }
    return 'Chưa có mô tả...';
};

// --- COMPUTED ---
const isSearching = computed(() => !!searchQuery.value || !!authorQuery.value || !!categoryQuery.value);
const featuredPost = computed(() => posts.value.length > 0 ? posts.value[0] : null);
const allLatestPosts = computed(() => posts.value.length > 0 ? posts.value.slice(1) : []);
const totalPages = computed(() => Math.ceil(allLatestPosts.value.length / ITEMS_PER_PAGE));

const paginatedPosts = computed(() => {
    const start = (currentPage.value - 1) * ITEMS_PER_PAGE;
    return allLatestPosts.value.slice(start, start + ITEMS_PER_PAGE);
});

// --- METHODS ---

// Cập nhật lại logic fetchPosts gọi trực tiếp Axios
const fetchPosts = async (query = '', author = '', category = '') => {
    isLoading.value = true;
    try {
        // Build tham số truyền lên API
        const params = {};
        if (query) params.q = query;
        if (author) params.author = author;
        if (category) params.category = category;

        const response = await axios.get(`${API_BASE_URL}/news`, { params });
        
        // Backend trả về: { status: 'success', data: [...] }
        const responseData = response.data.data ? response.data.data : response.data;
        posts.value = Array.isArray(responseData) ? responseData : [];
        currentPage.value = 1;

    } catch (error) {
        console.error("Lỗi tải danh sách bài viết:", error);
        posts.value = [];
    } finally {
        setTimeout(() => {
            isLoading.value = false;
        }, 500); // Giảm time timeout để user đỡ chờ lâu
    }
};

// Cập nhật lại logic fetchPopularPosts gọi trực tiếp Axios
const fetchPopularPosts = async () => {
    try {
        const response = await axios.get(`${API_BASE_URL}/news/popular`);
        // Lấy đúng mảng data từ API
        popularPosts.value = response.data.data ? response.data.data : response.data || [];
    } catch (error) {
        console.error("Lỗi tải bài viết phổ biến:", error);
        popularPosts.value = [];
    }
};

const triggerSearch = () => {
    fetchPosts(searchQuery.value, authorQuery.value, categoryQuery.value);
};

const searchByAuthor = (authorName) => {
    searchQuery.value = ''; categoryQuery.value = ''; authorQuery.value = authorName; 
    triggerSearch(); 
};

const searchByCategory = (catName) => {
    searchQuery.value = ''; authorQuery.value = ''; categoryQuery.value = catName;
    triggerSearch();
};

const handleSearch = () => {
    authorQuery.value = ''; categoryQuery.value = '';
    triggerSearch();
};

const changePage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        const listSection = document.getElementById('latest-news-section');
        if (listSection) listSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};

onMounted(() => {
    triggerSearch();
    fetchPopularPosts();
    updateListingSeo(); 
});

watch(searchQuery, debounce((newQuery) => {
    if (newQuery !== null) {
        authorQuery.value = ''; categoryQuery.value = '';
        triggerSearch();
    }
}, 400));

watch(authorQuery, (newAuthor) => { if (newAuthor === '') triggerSearch(); });
watch(categoryQuery, (newCat) => { if (newCat === '') triggerSearch(); });
</script>

<template>
    <section class="blog-page">
        <!-- Hero Section -->
        <header class="page-hero">
            <div class="hero-inner">
                <p class="hero-title" style="color: rgb(159,39,59); font-style: italic;">
  SORA - Chạm đến sự hoàn mỹ
</p>
                <h1 v-if="searchQuery">Tìm kiếm: "{{ searchQuery }}"</h1>
                <h1 v-else-if="authorQuery">Tác giả: "{{ authorQuery }}"</h1>
                <h1 v-else-if="categoryQuery">Danh mục: "{{ categoryQuery }}"</h1>
                <h1 v-else>SORA</h1>
                <p class="hero-subtitle">
                    Khám phá xu hướng trang sức & bí quyết làm đẹp tinh tế mỗi ngày.
                </p>
            </div>
        </header>

        <main class="page-container container">
            <!-- Skeleton Loading -->
            <div v-if="isLoading" class="page-layout fade-in">
                <section class="content-column">
                    <div class="featured-heading skeleton-box skeleton-text w-50 mb-4 shimmer"></div>
                    <div class="featured-post card-style skeleton-card">
                        <div class="featured-image-wrap skeleton-box img-box shimmer">
                            <span class="skeleton-placeholder-text-large">ThinkHub</span>
                        </div>
                        <div class="featured-body">
                            <div class="skeleton-box skeleton-text w-25 mb-3 shimmer"></div>
                            <div class="skeleton-box skeleton-title mb-3 shimmer"></div>
                            <div class="skeleton-box skeleton-text w-100 mb-2 shimmer"></div>
                            <div class="skeleton-box skeleton-text w-75 mb-4 shimmer"></div>
                            <div class="d-flex justify-content-between mt-auto">
                                <div class="skeleton-box skeleton-text w-25 shimmer"></div>
                                <div class="skeleton-box skeleton-text w-25 shimmer"></div>
                            </div>
                        </div>
                    </div>
                    <div class="latest-section">
                        <div class="skeleton-box skeleton-text w-25 mb-4 shimmer"></div>
                        <div class="latest-posts-grid">
                            <div v-for="n in 4" :key="n" class="post-card card-style skeleton-card">
                                <div class="card-img-top skeleton-box img-box shimmer" style="aspect-ratio: 16/9;"></div>
                                <div class="card-body">
                                    <div class="skeleton-box skeleton-text w-50 mb-3 shimmer"></div>
                                    <div class="skeleton-box skeleton-title mb-3 shimmer"></div>
                                    <div class="skeleton-box skeleton-text w-100 mb-2 shimmer"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <aside class="sidebar-column">
                    <div class="sidebar-widget skeleton-card mb-4">
                        <div class="skeleton-box skeleton-title w-50 mb-3 shimmer"></div>
                        <div class="skeleton-box skeleton-input shimmer" style="height: 45px;"></div>
                    </div>
                    <div class="sidebar-widget skeleton-card">
                        <div class="skeleton-box skeleton-title w-50 mb-3 shimmer"></div>
                        <div v-for="i in 4" :key="i" class="skeleton-box skeleton-text w-100 mb-2 shimmer"></div>
                    </div>
                </aside>
            </div>

            <!-- Dữ liệu thực tế -->
            <div v-else class="page-layout fade-in">
                <section class="content-column">
                    <!-- Trạng thái trống -->
                    <div v-if="posts.length === 0" class="empty-state card-style">
                        <i class="bi bi-newspaper display-4 text-muted mb-3"></i>
                        <h3 v-if="searchQuery">Không tìm thấy kết quả cho "{{ searchQuery }}"</h3>
                        <h3 v-else-if="authorQuery">Không tìm thấy bài viết của "{{ authorQuery }}"</h3>
                        <h3 v-else-if="categoryQuery">Chưa có bài viết trong "{{ categoryQuery }}"</h3>
                        <h3 v-else>Chưa có tin tức nào</h3>
                        <p class="text-muted">Vui lòng thử lại với từ khóa khác hoặc quay lại sau.</p>
                        <button v-if="isSearching" @click="handleSearch" class="btn btn-outline-primary mt-3">Xem tất cả bài viết</button>
                    </div>

                    <template v-else>
                        <h3 class="featured-heading">
                            <i class="bi bi-bullseye me-2 icon-color"></i>
                            {{ isSearching ? 'Kết quả lọc' : 'Tin tức nổi bật' }}
                        </h3>

                        <!-- Bài viết nổi bật (Mới nhất) -->
                        <article v-if="featuredPost" class="featured-post card-style">
                            <div class="featured-image-wrap">
                                <router-link :to="{ name: 'PostDetailt', params: { slug: featuredPost.slug || toSlug(featuredPost.title) } }" class="full-link">
                                    <div class="featured-image" :style="{ backgroundImage: `url(${getFullImage(featuredPost.image_url)})` }">
                                        <div class="cat-badge" v-if="featuredPost.category">{{ featuredPost.category }}</div>
                                    </div>
                                </router-link>
                            </div>
                            <div class="featured-body">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="badge-custom me-2" v-if="isSearching">KẾT QUẢ TÌM KIẾM</span>
                                    <span class="badge-custom me-2" v-else>MỚI NHẤT</span>
                                    <span class="date-meta"><i class="bi bi-calendar3 icon-color"></i> {{ formatDate(featuredPost.created_at) }}</span>
                                </div>

                                <h2 class="featured-title">
                                    <router-link :to="{ name: 'PostDetailt', params: { slug: featuredPost.slug || toSlug(featuredPost.title) } }" class="text-reset">
                                        {{ featuredPost.title }}
                                    </router-link>
                                </h2>
                                <p class="excerpt">{{ getExcerpt(featuredPost, 180) }}</p>

                                <div class="post-footer">
                                    <span class="author">
                                        <i class="bi bi-person-fill icon-color"></i>
                                        <a href="#" @click.prevent="searchByAuthor(featuredPost.author_name || 'Admin')" class="author-link">
                                            {{ featuredPost.author_name || 'Admin' }}
                                        </a>
                                    </span>
                                    <router-link :to="{ name: 'PostDetailt', params: { slug: featuredPost.slug || toSlug(featuredPost.title) } }" class="read-more-btn">
                                        Đọc tiếp <i class="bi bi-arrow-right ms-1"></i>
                                    </router-link>
                                </div>
                            </div>
                        </article>

                        <!-- Lưới bài viết khác -->
                        <div v-if="allLatestPosts.length > 0" class="latest-section" id="latest-news-section">
                            <h3 class="section-heading">
                                <i class="bi bi-grid-fill me-2"></i> {{ isSearching ? 'Các kết quả khác' : 'Tin cũ hơn' }}
                            </h3>

                            <div class="latest-posts-grid">
                                <article v-for="post in paginatedPosts" :key="post.id" class="post-card card-style">
                                    <div class="card-img-top">
                                        <router-link :to="{ name: 'PostDetailt', params: { slug: post.slug || toSlug(post.title) } }" class="full-link">
                                            <div class="img-bg" :style="{ backgroundImage: `url(${getFullImage(post.image_url)})` }">
                                                <div class="cat-badge-small" v-if="post.category">{{ post.category }}</div>
                                            </div>
                                        </router-link>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-meta">
                                            <span class="date"><i class="bi bi-calendar-event icon-color"></i> {{ formatDate(post.created_at) }}</span>
                                        </div>
                                        <h4 class="card-title">
                                            <router-link :to="{ name: 'PostDetailt', params: { slug: post.slug || toSlug(post.title) } }" class="text-reset">
                                                {{ post.title }}
                                            </router-link>
                                        </h4>
                                        <p class="card-excerpt">{{ getExcerpt(post, 90) }}</p>
                                        <div class="card-footer-custom">
                                            <router-link :to="{ name: 'PostDetailt', params: { slug: post.slug || toSlug(post.title) } }" class="card-link">
                                                Xem chi tiết <i class="bi bi-chevron-right small-icon"></i>
                                            </router-link>
                                        </div>
                                    </div>
                                </article>
                            </div>

                            <!-- Pagination -->
                            <div v-if="totalPages > 1" class="pagination-wrapper">
                                <button class="page-btn prev" :disabled="currentPage === 1" @click="changePage(currentPage - 1)">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <div class="page-numbers">
                                    <button v-for="page in totalPages" :key="page" class="page-btn"
                                        :class="{ active: currentPage === page }" @click="changePage(page)">
                                        {{ page }}
                                    </button>
                                </div>
                                <button class="page-btn next" :disabled="currentPage === totalPages" @click="changePage(currentPage + 1)">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </section>

                <aside class="sidebar-column">
                    <!-- Widget Tìm kiếm -->
                    <div class="sidebar-widget search-widget">
                        <h4><i class="bi bi-search me-2"></i> Tìm kiếm</h4>
                        <div class="search-box">
                            <input type="text" v-model="searchQuery" placeholder="Nhập từ khóa...">
                            <button @click="handleSearch"><i class="bi bi-search"></i></button>
                        </div>
                        
                        <div v-if="authorQuery" class="alert alert-info mt-3 py-2 px-3 d-flex justify-content-between align-items-center small">
                            <span>Tác giả: <strong>{{ authorQuery }}</strong></span>
                            <button @click="authorQuery = ''" class="btn-close ms-2" aria-label="Close"></button>
                        </div>
                        <div v-if="categoryQuery" class="alert alert-info mt-3 py-2 px-3 d-flex justify-content-between align-items-center small">
                            <span>Danh mục: <strong>{{ categoryQuery }}</strong></span>
                            <button @click="categoryQuery = ''" class="btn-close ms-2" aria-label="Close"></button>
                        </div>
                    </div>

                    <!-- Widget Danh mục -->
                    <div class="sidebar-widget category-widget">
                        <h4><i class="bi bi-tags-fill me-2"></i> Danh mục</h4>
                        <ul>
                            <li v-for="cat in CATEGORIES" :key="cat.name">
                                <a href="#" @click.prevent="searchByCategory(cat.name)" :class="{ 'active-cat': categoryQuery === cat.name }">
                                    <i class="bi me-2" :class="cat.icon || 'bi-caret-right-fill'"></i> {{ cat.name }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Widget Phổ biến -->
                    <div class="sidebar-widget popular-widget">
                        <h4><i class="bi bi-star-fill me-2"></i> Phổ biến</h4>
                        
                        <div v-if="popularPosts.length === 0" class="text-center py-3 text-muted small">
                            Chưa có dữ liệu nổi bật
                        </div>

                        <div v-for="(post, index) in popularPosts" :key="post.id" class="popular-post-item">
                            <div class="pop-number">{{ index + 1 }}</div>
                            <div class="flex-grow-1">
                                <router-link :to="{ name: 'PostDetailt', params: { slug: post.slug || toSlug(post.title) } }" class="text-reset text-decoration-none">
                                    <p>{{ post.title }}</p>
                                </router-link>
                                <span class="post-meta-small">
                                    <i class="bi bi-eye"></i> {{ formatViews(post.views) }} lượt xem
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Widget Đăng ký -->
                    <div class="sidebar-widget support-box mt-4">
                        <div class="email-primary d-flex align-items-center mb-3">
                            <i class="bi bi-envelope-paper-fill fs-3 me-3"></i>
                            <div>
                                <h6 class="mb-0 fw-bold">Đăng ký nhận tin</h6>
                                <small class="text-muted">Nhận bài viết mới qua email</small>
                            </div>
                        </div>
                        <button class="btn btn-email-primary w-100 btn-sm fw-bold">Đăng ký ngay</button>
                    </div>
                </aside>
            </div>
        </main>
    </section>
</template>

<style scoped>
/* Màu chủ đạo (có thể chỉnh lại theo theme) */
:root {
    --primary: #9F273B;
    --primary-dark: #007a67;
    --accent: #00483D;
    --text-dark: #2c3e50;
    --text-gray: #636e72;
    --bg-light: #F8F9FA;
    --white: #FFFFFF;
}

.email-primary { color: var(--primary); }
.btn-email-primary { background-color: var(--primary); color: white; }
.btn-email-primary:hover { background-color: var(--primary-dark); }

.full-link { display: block; width: 100%; height: 100%; text-decoration: none; position: relative; }

.cat-badge { position: absolute; top: 20px; left: 20px; background: rgba(0, 153, 129, 0.9); color: white; padding: 5px 15px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; border-radius: 4px; z-index: 2; }
.cat-badge-small { position: absolute; top: 10px; left: 10px; background: rgba(0, 153, 129, 0.9); color: white; padding: 3px 10px; font-size: 0.7rem; font-weight: 700; border-radius: 3px; z-index: 2; }

.author-link { color: var(--text-dark); text-decoration: underline; font-weight: 600; cursor: pointer; transition: color 0.2s; }
.author-link:hover { color: var(--primary); }

.alert-info { background-color: #e0f7fa; color: #004d40; border: 1px solid #b2ebf2; border-radius: 8px; }
.btn-close { opacity: 0.8; padding: 0.2em; font-size: 0.75rem; }
.btn-close:hover { opacity: 1; }

.blog-page { font-family: 'Inter', system-ui, sans-serif; background-color: #F8F9FA; min-height: 100vh; color: #2c3e50; display: flex; flex-direction: column; }
.text-reset { text-decoration: none; color: inherit; transition: color 0.2s; }
.text-reset:hover { color: #9F273B; }
.icon-color { color: #9F273B; margin-right: 5px; }

.page-hero { background: linear-gradient(135deg, #e0f2f1 0%, #ffffff 100%); padding: 60px 20px; text-align: center; border-bottom: 1px solid #e0e0e0; }
.hero-inner { max-width: 800px; margin: 0 auto; }
.hero-pre-title { color: #9F273B; font-weight: 700; letter-spacing: 2px; font-size: 0.85rem; margin-bottom: 10px; }
.page-hero h1 { font-size: 2.5rem; font-weight: 800; color: #00483D; margin-bottom: 15px; }
.hero-subtitle { color: #636e72; font-size: 1.1rem; line-height: 1.6; }

.page-container { margin: 50px auto; flex-grow: 1; }
.page-layout { display: grid; grid-template-columns: 1fr 320px; gap: 48px; align-items: start; position: relative; }
.card-style { background: #FFFFFF; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04); border: 1px solid rgba(0, 0, 0, 0.03); overflow: hidden; }
.empty-state { padding: 60px 0; text-align: center; grid-column: 1 / -1; }

.featured-heading { font-size: 1.5rem; font-weight: 700; margin-bottom: 20px; color: #00483D; display: flex; align-items: center; padding-bottom: 10px; border-bottom: 2px solid #eee; }
.featured-post { display: flex; flex-direction: row; min-height: 400px; margin-bottom: 50px; transition: transform 0.3s ease, box-shadow 0.3s ease; }
.featured-post:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); }
.featured-image-wrap { width: 60%; position: relative; overflow: hidden; display: flex; }
.featured-image { width: 100%; height: 100%; background-size: cover; background-position: center; transition: transform 0.5s ease; min-height: 100%; }
.featured-post:hover .featured-image { transform: scale(1.05); }
.featured-body { width: 40%; padding: 40px; display: flex; flex-direction: column; justify-content: center; }

.badge-custom { background-color: #9F273B; color: white; padding: 5px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; }
.date-meta { font-size: 0.85rem; color: #999; }
.featured-title { font-size: 1.8rem; font-weight: 700; line-height: 1.3; margin-bottom: 15px; }
.excerpt { color: #636e72; margin-bottom: 25px; line-height: 1.6; font-size: 1rem; }
.post-footer { display: flex; justify-content: space-between; align-items: center; margin-top: auto; }
.read-more-btn { color: #9F273B; font-weight: 700; text-decoration: none; font-size: 1rem; display: flex; align-items: center; transition: padding-left 0.2s; }
.read-more-btn:hover { color: #00483D; padding-left: 5px; }

.section-heading { font-size: 1.5rem; font-weight: 700; margin-bottom: 25px; color: #00483D; display: flex; align-items: center; }
.latest-posts-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; }
.post-card { display: flex; flex-direction: column; height: 100%; transition: transform 0.3s, box-shadow 0.3s; }
.post-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08); }
.card-img-top { width: 100%; aspect-ratio: 16/9; overflow: hidden; position: relative; }
.img-bg { width: 100%; height: 100%; background-size: cover; background-position: center; transition: transform 0.5s; }
.post-card:hover .img-bg { transform: scale(1.1); }
.card-body { padding: 25px; flex-grow: 1; display: flex; flex-direction: column; }
.card-meta { font-size: 0.8rem; color: #aaa; margin-bottom: 12px; }
.card-title { font-size: 1.2rem; font-weight: 700; margin-bottom: 12px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.card-excerpt { font-size: 0.95rem; color: #636e72; margin-bottom: 20px; flex-grow: 1; line-height: 1.5; }
.card-footer-custom { border-top: 1px solid #eee; padding-top: 15px; }
.card-link { font-size: 0.9rem; color: #9F273B; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 5px; }
.card-link:hover { color: #00483D; }

.sidebar-column { display: flex; flex-direction: column; gap: 30px; position: sticky; top: 20px; }
.sidebar-widget { background: #FFFFFF; padding: 25px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.03); border: 1px solid rgba(0, 0, 0, 0.03); }
.sidebar-widget h4 { font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; color: #007a67; border-bottom: 1px dashed #eee; padding-bottom: 15px; display: flex; align-items: center; }
.search-box { display: flex; gap: 8px; }
.search-box input { flex-grow: 1; padding: 10px 15px; border: 1px solid #eee; border-radius: 8px; outline: none; background: #fdfdfd; transition: border 0.2s; }
.search-box input:focus { border-color: #9F273B; background: #fff; }
.search-box button { background: #9F273B; color: white; border: none; width: 45px; border-radius: 8px; cursor: pointer; transition: background-color 0.2s; }
.search-box button:hover { background: #00483D; }

.category-widget ul { list-style: none; padding: 0; margin: 0; }
.category-widget li { margin-bottom: 8px; }
.category-widget a { text-decoration: none; color: #2c3e50; font-weight: 500; padding: 8px 10px; border-radius: 6px; display: flex; align-items: center; transition: all 0.2s; }
.category-widget a:hover, .category-widget a.active-cat { background-color: rgba(0, 153, 129, 0.08); color: #9F273B; padding-left: 15px; }

.popular-post-item { display: flex; align-items: flex-start; gap: 15px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f5f5f5; cursor: pointer; }
.popular-post-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.pop-number { font-size: 1.2rem; font-weight: 900; color: #eee; line-height: 1; min-width: 20px; }
.popular-post-item:hover .pop-number { color: #9F273B; }
.popular-post-item p { font-weight: 600; font-size: 0.95rem; margin-bottom: 5px; transition: color 0.2s; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.popular-post-item:hover p { color: #9F273B; }
.post-meta-small { font-size: 0.8rem; color: #999; }

.pagination-wrapper { display: flex; justify-content: center; align-items: center; margin-top: 40px; gap: 15px; }
.page-numbers { display: flex; gap: 8px; }
.page-btn { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 1px solid #eee; background: #FFFFFF; border-radius: 8px; color: #2c3e50; cursor: pointer; font-weight: 600; transition: all 0.2s; }
.page-btn:hover:not(:disabled) { border-color: #9F273B; color: #9F273B; }
.page-btn.active { background: #9F273B; color: white; border-color: #9F273B; }
.page-btn:disabled { background: #f8f9fa; color: #ccc; cursor: not-allowed; border-color: #eee; }

@media (max-width: 992px) {
    .page-layout { grid-template-columns: 1fr; gap: 40px; }
    .featured-post { flex-direction: column; min-height: auto; }
    .featured-image-wrap { width: 100%; height: 250px; }
    .featured-body { width: 100%; padding: 25px; }
    .featured-title { font-size: 1.5rem; }
    .sidebar-column { order: 1; }
}

.fade-in { animation: fadeIn 0.3s ease-in; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.shimmer { background: #f6f7f8; background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%); background-repeat: no-repeat; background-size: 800px 100%; animation: placeholderShimmer 1.5s linear infinite forwards; }
@keyframes placeholderShimmer { 0% { background-position: -468px 0; } 100% { background-position: 468px 0; } }

.skeleton-box { background-color: #eee; border-radius: 4px; }
.skeleton-text { height: 14px; border-radius: 4px; }
.skeleton-title { height: 24px; border-radius: 4px; }
.skeleton-input { border-radius: 8px; }
.skeleton-card { border: 1px solid #eee; pointer-events: none; }
.skeleton-box.img-box { background-color: #ddd; display: flex; align-items: center; justify-content: center; }
.skeleton-placeholder-text-large { font-size: 3rem; font-weight: 900; color: #e5e7eb; text-transform: uppercase; letter-spacing: 2px; opacity: 0.8; }
.skeleton-placeholder-text-small { font-size: 1.5rem; font-weight: 900; color: #e5e7eb; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; }
</style>