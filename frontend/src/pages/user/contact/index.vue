<template>
  <div class="contact-page font-sans bg-light-custom pb-5">
    
    <!-- 1. Tiêu đề chính (Hero Section) -->
    <section class="hero-contact position-relative d-flex align-items-center justify-content-center text-center overflow-hidden">
      <div class="hero-overlay position-absolute w-100 h-100 top-0 start-0"></div>
      <div class="position-relative z-1 d-flex flex-column align-items-center px-3 pt-5">
        <span class="text-accent text-uppercase fw-bold mb-3 tracking-wide" style="font-size: 0.85rem; letter-spacing: 0.3em;">Dịch Vụ Khách Hàng</span>
        <h1 class="display-3 fw-bold text-white font-serif mb-3" style="letter-spacing: 0.05em;">Kết Nối Cùng SORA</h1>
        <div class="divider bg-accent mb-4 mx-auto"></div>
      </div>
    </section>

    <!-- Nội dung chính -->
    <section class="py-5" style="margin-top: -3rem; position: relative; z-index: 2;">
      <div class="container bg-white shadow-sm p-4 p-md-5">
        
        <!-- 2. Đoạn mô tả ngắn -->
        <div class="text-center max-w-2xl mb-5 mx-auto">
          <h2 class="h3 font-serif text-main mb-3">Trải Nghiệm Chăm Sóc Độc Bản</h2>
          <p class="text-secondary fw-light" style="line-height: 1.8;">
            Mỗi yêu cầu, mỗi câu chuyện của bạn đều là mảnh ghép quan trọng giúp SORA vươn tới sự hoàn mỹ. Đừng ngần ngại chia sẻ, đội ngũ chuyên viên của chúng tôi luôn ở đây, sẵn sàng lắng nghe và kiến tạo nên những khoảnh khắc trang sức dành riêng cho bạn.
          </p>
        </div>

        <div class="row g-5 mt-2">
          
          <!-- 3. Thông tin liên hệ -->
          <div class="col-lg-5 order-2 order-lg-1 pe-lg-5">
            <div class="contact-info-wrapper h-100 bg-light p-4 p-md-5 border-start border-accent border-4">
              <h3 class="h4 font-serif text-dark mb-4">Thông Tin Liên Hệ</h3>
              
              <ul class="list-unstyled mb-5 text-secondary fw-light">
                <!-- Địa chỉ -->
                <li class="d-flex align-items-start mb-4">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="text-main me-3 flex-shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                  <div>
                    <strong class="d-block text-dark fw-medium mb-1">SORA Flagship Store</strong>
                    Khu đô thị cao cấp, Quận 1, TP. Hồ Chí Minh
                  </div>
                </li>
                <!-- Điện thoại -->
                <li class="d-flex align-items-start mb-4">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="text-main me-3 flex-shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                  <div>
                    <strong class="d-block text-dark fw-medium mb-1">Dịch vụ khách hàng (24/7)</strong>
                    <a href="tel:0901234567" class="text-secondary text-decoration-none custom-link">090 123 4567</a>
                  </div>
                </li>
                <!-- Email -->
                <li class="d-flex align-items-start mb-4">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="text-main me-3 flex-shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                  <div>
                    <strong class="d-block text-dark fw-medium mb-1">Email tiếp nhận</strong>
                    <a href="mailto:concierge@sorajewelry.vn" class="text-secondary text-decoration-none custom-link">concierge@sorajewelry.vn</a>
                  </div>
                </li>
                <!-- Giờ làm việc -->
                <li class="d-flex align-items-start">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="text-main me-3 flex-shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                  <div>
                    <strong class="d-block text-dark fw-medium mb-1">Giờ mở cửa</strong>
                    09:00 - 21:00 (Từ Thứ 2 đến Chủ Nhật)
                  </div>
                </li>
              </ul>
              
              <!-- 5. Đoạn cam kết -->
              <div class="p-3 bg-white border border-light text-center mt-auto">
                <p class="mb-0 text-main font-serif fst-italic text-sm">
                  "Mọi yêu cầu của quý khách sẽ được Chuyên viên SORA phản hồi tận tâm muộn nhất trong vòng 2 giờ làm việc."
                </p>
              </div>
            </div>
          </div>

          <!-- 4. Form liên hệ -->
          <div class="col-lg-7 order-1 order-lg-2">
            <div class="px-md-4 py-3">
              <h3 class="h4 font-serif text-main mb-4">Gửi Lời Nhắn Cho Chúng Tôi</h3>
              <form @submit.prevent="submitContactForm">
                <div class="row g-4">
                  <div class="col-md-6">
                    <div class="form-floating custom-floating">
                      <input type="text" class="form-control custom-input" id="fullname" placeholder="Họ và tên" required>
                      <label for="fullname" class="text-muted fw-light">Danh xưng / Họ và tên *</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-floating custom-floating">
                      <input type="tel" class="form-control custom-input" id="phone" placeholder="Số điện thoại" required>
                      <label for="phone" class="text-muted fw-light">Số điện thoại liên hệ *</label>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-floating custom-floating">
                      <input type="email" class="form-control custom-input" id="email" placeholder="name@example.com" required>
                      <label for="email" class="text-muted fw-light">Địa chỉ Email *</label>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-floating custom-floating">
                      <textarea class="form-control custom-input" id="message" placeholder="Nội dung tin nhắn" style="height: 120px" required></textarea>
                      <label for="message" class="text-muted fw-light">Mong muốn hoặc thắc mắc của bạn...</label>
                    </div>
                  </div>
                  <div class="col-12 mt-5 text-end">
                    <!-- 6. CTA Button -->
                    <button type="submit" class="btn btn-main btn-lg text-uppercase fw-medium px-5 py-3 w-100 w-md-auto rounded-0" style="letter-spacing: 0.1em;">
                      Gửi Lời Nhắn
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Câu kết (Slogan) -->
    <section class="py-5 text-center">
      <div class="container py-3">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" class="text-accent mb-3 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
        </svg>
        <p class="h5 font-serif text-main fst-italic mb-2">SORA – Nơi khí chất độc bản được tôn vinh.</p>
        <p class="text-muted fw-light small">Hãy để chúng tôi đồng hành cùng phong cách vượt thời gian của bạn.</p>
      </div>
    </section>

  </div>
</template>

<script setup>
// Giao diện tĩnh, xử lý form liên hệ sau này gọi API tại đây
const submitContactForm = () => {
  // Logic gọi API gửi form sẽ code tại đây nhé sếp
  alert("Cảm ơn bạn đã liên hệ SORA. Chuyên viên của chúng tôi sẽ phản hồi sớm nhất!");
}
</script>

<style scoped>
/* Màu sắc thương hiệu SORA */
.bg-light-custom { background-color: #faf9f8 !important; }
.bg-main { background-color: #9f273b !important; }
.text-main { color: #9f273b !important; }
.bg-accent { background-color: #e7ce7d !important; }
.text-accent { color: #e7ce7d !important; }
.text-danger-custom { color: #cc1e2e !important; }
.border-accent { border-color: #e7ce7d !important; }

/* Font Serif cho tiêu đề */
.font-serif {
  font-family: "Playfair Display", "Merriweather", serif;
}
.max-w-2xl { max-width: 42rem; }

/* Hero Section */
.hero-contact {
  height: 40vh; /* Ngắn hơn trang About một chút */
  background-color: #1a1a1a;
  background-image: url('https://images.unsplash.com/photo-1599643478524-fb66f4568dbb?q=80&w=2000&auto=format&fit=crop'); /* Có thể thay ảnh nhẫn kim cương tối màu vào đây */
  background-size: cover;
  background-position: center 30%;
}
.hero-overlay {
  background: rgba(40, 0, 10, 0.75); /* Ám đỏ đô tối */
}
.divider {
  width: 3rem;
  height: 2px;
}

/* Custom Link Hover */
.custom-link {
  transition: color 0.3s ease;
}
.custom-link:hover {
  color: #9f273b !important;
}

/* Custom Input Style (Luxury Minimal) */
.custom-input {
  border: none;
  border-bottom: 1px solid #dee2e6;
  border-radius: 0;
  padding-left: 0;
  padding-right: 0;
  background-color: transparent;
  box-shadow: none !important;
  font-weight: 300;
  transition: border-color 0.3s ease;
}
.custom-input:focus {
  border-bottom: 2px solid #9f273b;
  background-color: transparent;
}

/* Floating Label Tweaks for Border Bottom Input */
.custom-floating > label {
  padding-left: 0;
}
.custom-floating > .form-control:focus ~ label,
.custom-floating > .form-control:not(:placeholder-shown) ~ label {
  color: #9f273b;
  transform: scale(0.85) translateY(-0.75rem) translateX(0);
}

/* Nút bấm (Button) */
.btn-main {
  background-color: #9f273b;
  color: white;
  border: 1px solid #9f273b;
  transition: all 0.3s ease;
}
.btn-main:hover {
  background-color: #cc1e2e;
  border-color: #cc1e2e;
  color: white;
  box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
}

/* Tiện ích thiết bị */
@media (min-width: 768px) {
  .w-md-auto { width: auto !important; }
}
</style>