<template>
  <div class="order-index-wrapper pb-5 mb-5">
    
    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ThinkHub</h1>
      <p class="text-muted fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải dữ liệu đơn hàng...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      <div class="row mb-4 align-items-center">
        <div class="col-md-6">
          <h3 class="fw-bold text-dark mb-0">Quản lý Đơn Hàng</h3>
          <p class="text-muted mb-0">Theo dõi, xử lý và cập nhật trạng thái hóa đơn</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end align-items-center gap-3">
          <div class="border rounded px-3 py-1 bg-white shadow-sm text-muted small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>
          <!-- Làm mới dữ liệu một cách mượt mà (Silent) -->
          <button class="btn btn-light border shadow-sm fw-bold text-dark px-4 py-2" @click="fetchData(1, true)">
            <i class="bi bi-arrow-clockwise me-1"></i> Làm mới
          </button>
        </div>
      </div>

      <!-- TABS PHÂN LOẠI -->
      <div class="mb-3">
        <ul class="nav nav-underline border-bottom mb-2 pb-1" style="flex-wrap: wrap !important; gap: 8px;">
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'all' }" @click.prevent="switchTab('all')">
              <i class="bi bi-grid-fill me-2"></i> Tất cả
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'all'}">{{ statusCounts['all'] || 0 }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'pending' }" @click.prevent="switchTab('pending')">
              <i class="bi bi-hourglass-split me-2 text-warning"></i> Chờ duyệt
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'pending'}">{{ statusCounts['pending'] || 0 }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'confirmed' }" @click.prevent="switchTab('confirmed')">
              <i class="bi bi-check-circle me-2 text-info"></i> Đã xác nhận
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'confirmed'}">{{ statusCounts['confirmed'] || 0 }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'processing' }" @click.prevent="switchTab('processing')">
              <i class="bi bi-box-seam-fill me-2 text-primary"></i> Đang chuẩn bị
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'processing'}">{{ statusCounts['processing'] || 0 }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'shipping' }" @click.prevent="switchTab('shipping')">
              <i class="bi bi-truck me-2 text-primary"></i> Đang giao
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'shipping'}">{{ statusCounts['shipping'] || 0 }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'delivered' }" @click.prevent="switchTab('delivered')">
              <i class="bi bi-check-circle-fill me-2 text-success"></i> Đã giao
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'delivered'}">{{ statusCounts['delivered'] || 0 }}</span>
            </a>
          </li>
          <li class="nav-item ms-auto">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab text-danger" href="#" :class="{ 'active-tab': activeTab === 'cancelled' }" @click.prevent="switchTab('cancelled')">
              <i class="bi bi-x-circle-fill me-2"></i> Đã hủy
              <span class="badge ms-2 rounded-pill bg-danger text-white">{{ statusCounts['cancelled'] || 0 }}</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- BỘ LỌC ĐƠN HÀNG -->
      <div class="d-flex flex-wrap gap-3 mb-4 align-items-center">
        <div class="d-flex align-items-center bg-white px-3 py-2 rounded-pill border shadow-sm">
          <span class="text-muted small fw-semibold me-2"><i class="bi bi-credit-card-fill text-brand"></i> Thanh toán:</span>
          <select class="form-select form-select-sm border-0 bg-transparent fw-bold p-0 pe-4 cursor-pointer" style="width: auto; box-shadow: none;" v-model="filters.payment_status" @change="fetchData(1, true)">
            <option value="all">Tất cả</option>
            <option value="unpaid">Chưa thanh toán</option>
            <option value="paid">Đã thanh toán</option>
          </select>
        </div>

        <div class="d-flex align-items-center bg-white px-3 py-1 rounded-pill border shadow-sm">
          <span class="text-muted small fw-semibold me-2"><i class="bi bi-calendar-range text-brand"></i> Lọc Ngày:</span>
          <input type="date" class="form-control form-control-sm border-0 bg-transparent fw-bold p-1" style="box-shadow: none; width: 130px;" v-model="filters.start_date" @change="fetchData(1, true)">
          <span class="mx-1 text-muted">-</span>
          <input type="date" class="form-control form-control-sm border-0 bg-transparent fw-bold p-1" style="box-shadow: none; width: 130px;" v-model="filters.end_date" @change="fetchData(1, true)">
        </div>
      </div>

      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <h6 class="fw-bold mb-0 text-dark d-flex align-items-center">
            <i class="bi bi-receipt me-2"></i>Danh sách Đơn hàng
            <div v-if="isSilentLoading" class="spinner-border spinner-border-sm text-brand ms-2" role="status"></div>
          </h6>
          <div class="search-box position-relative" style="width: 300px; max-width: 100%;">
            <input type="text" class="form-control rounded-pill pe-5 shadow-sm bg-light border-0" v-model="searchQuery" @keyup.enter="fetchData(1, true)" placeholder="Tìm Mã đơn...">
            <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted cursor-pointer" @click="fetchData(1, true)"></i>
          </div>
        </div>
        
        <div class="card-body p-0 mt-2">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 1200px;">
              <thead class="bg-light">
                <tr>
                  <th class="py-3 px-4 text-secondary border-0" style="width: 18%;">Mã Đơn / Ngày</th>
                  <th class="py-3 px-4 text-secondary border-0" style="width: 22%;">Khách hàng</th>
                  <th class="py-3 px-4 text-secondary border-0 text-end" style="width: 12%;">Tổng tiền</th>
                  <th class="py-3 px-4 text-secondary border-0 text-center" style="width: 20%;">Thanh toán (Sửa nhanh)</th>
                  <th class="py-3 px-4 text-secondary border-0 text-center" style="width: 20%;">Trạng thái (Sửa nhanh)</th>
                  <th class="py-3 px-4 text-secondary text-center border-0" style="width: 8%;">Chi tiết</th>
                </tr>
              </thead>
              <tbody :class="{'pe-none': isSilentLoading}">
                <tr v-if="orders.length === 0 && !isSilentLoading">
                  <td colspan="6" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Không tìm thấy đơn hàng nào.
                  </td>
                </tr>
                
                <template v-else>
                  <tr v-for="order in displayedOrders" :key="order.id" :class="{'bg-light opacity-75': order.status === 'cancelled' || order.status === 'returned'}">
                    
                    <td class="px-4 py-3">
                      <div class="fw-bold text-brand fs-6 mb-1 font-monospace cursor-pointer" @click="openQuickView(order.id)">{{ order.order_code }}</div>
                      <div class="text-muted small"><i class="bi bi-clock me-1"></i>{{ formatDate(order.created_at) }}</div>
                    </td>
                    
                    <td class="px-4 overflow-hidden">
                      <div class="fw-bold text-dark text-truncate mb-1"><i class="bi bi-person-fill me-1 text-secondary"></i> {{ order.customer_name }}</div>
                      <div class="small text-muted text-truncate"><i class="bi bi-telephone-fill me-1"></i> {{ order.customer_phone }}</div>
                    </td>
                    
                    <td class="px-4 text-end">
                      <div class="fw-bold text-success">{{ formatCurrency(order.total_amount) }}</div>
                      <div class="small text-muted mt-1">{{ order.items_count || 0 }} Món</div>
                    </td>

                    <!-- CỘT SỬA NHANH THANH TOÁN -->
                    <td class="px-3 text-center">
                      <div class="d-flex flex-column align-items-center">
                        <div class="d-flex align-items-center justify-content-center gap-1 w-100 flex-nowrap">
                          <!-- BẢO VỆ GIAO DIỆN: Khóa cứng dropdown nếu đơn Đã Giao, Hủy hoặc Hoàn trả -->
                          <select class="form-select form-select-sm border shadow-sm fw-bold cursor-pointer text-dark bg-white" 
                                  style="width: 120px; font-size: 0.75rem; border-color: #ced4da !important; flex-shrink: 0;"
                                  :class="getPaymentSelectClass(order.localPaymentStatus || order.payment_status)"
                                  v-model="order.localPaymentStatus"
                                  @change="checkPaymentStatusChange(order)"
                                  :disabled="order.isUpdatingPayment || ['delivered', 'cancelled', 'returned'].includes(order.status) || order.payment_status === 'refunded'">
                            <option value="unpaid" :hidden="!canPaymentTransitionTo(order.payment_status, 'unpaid')">Chưa TT</option>
                            <option value="paid" :hidden="!canPaymentTransitionTo(order.payment_status, 'paid')">Đã TT</option>
                            <option value="refunded" :hidden="!canPaymentTransitionTo(order.payment_status, 'refunded')">Đã hoàn tiền</option>
                            <option value="failed" :hidden="!canPaymentTransitionTo(order.payment_status, 'failed')">Thất bại</option>
                          </select>
                          
                          <div class="d-flex align-items-center justify-content-start" style="min-width: 55px; height: 28px; flex-shrink: 0 !important;">
                            <div v-if="order.isUpdatingPayment" class="spinner-border text-brand ms-1" style="width: 1.25rem; height: 1.25rem; border-width: 0.15em; flex-shrink: 0 !important;" role="status"></div>
                            
                            <template v-else-if="order.isPaymentStatusChanged">
                              <button @click="savePaymentStatus(order)" class="btn btn-sm btn-success rounded-circle shadow-sm d-flex align-items-center justify-content-center ms-1" style="width: 24px; height: 24px; padding: 0; flex-shrink: 0 !important;" title="Lưu thanh toán">
                                <i class="bi bi-check-lg fw-bold" style="font-size: 0.7rem;"></i>
                              </button>
                              <button @click="cancelPaymentStatusChange(order)" class="btn btn-sm btn-light rounded-circle shadow-sm text-danger border d-flex align-items-center justify-content-center ms-1" style="width: 24px; height: 24px; padding: 0; flex-shrink: 0 !important;" title="Hủy">
                                <i class="bi bi-x-lg fw-bold" style="font-size: 0.7rem;"></i>
                              </button>
                            </template>
                          </div>
                        </div>
                        <div class="small fw-semibold text-muted text-uppercase mt-1" style="font-size: 0.65rem;">
                            Phương thức: {{ order.payment_method }}
                        </div>
                      </div>
                    </td>

                    <!-- CỘT SỬA NHANH TRẠNG THÁI ĐƠN -->
                    <td class="px-3 text-center">
                      <div class="d-flex align-items-center justify-content-center gap-1 w-100 flex-nowrap">
                        <!-- BẢO VỆ GIAO DIỆN: Khóa cứng dropdown nếu đơn Đã Giao, Hủy hoặc Hoàn trả -->
                        <select class="form-select form-select-sm border shadow-sm fw-bold cursor-pointer text-dark bg-white" 
                                style="width: 120px; font-size: 0.75rem; border-color: #ced4da !important; flex-shrink: 0;"
                                :class="getOrderStatusClass(order.localStatus || order.status)"
                                v-model="order.localStatus"
                                @change="checkStatusChange(order)"
                                :disabled="order.isUpdatingStatus || ['delivered', 'cancelled', 'returned'].includes(order.status)">
                          <option value="pending" :hidden="!canTransitionTo(order.status, 'pending')">Chờ duyệt</option>
                          <option value="confirmed" :hidden="!canTransitionTo(order.status, 'confirmed')">Đã xác nhận</option>
                          <option value="processing" :hidden="!canTransitionTo(order.status, 'processing')">Đang chuẩn bị</option>
                          <option value="shipping" :hidden="!canTransitionTo(order.status, 'shipping')">Đang giao</option>
                          <option value="delivered" :hidden="!canTransitionTo(order.status, 'delivered')">Đã giao</option>
                          <option value="cancelled" :hidden="!canTransitionTo(order.status, 'cancelled')">Hủy đơn</option>
                        </select>
                        
                        <div class="d-flex align-items-center justify-content-start" style="min-width: 55px; height: 28px; flex-shrink: 0 !important;">
                          <div v-if="order.isUpdatingStatus" class="spinner-border text-brand ms-1" style="width: 1.25rem; height: 1.25rem; border-width: 0.15em; flex-shrink: 0 !important;" role="status"></div>
                          
                          <template v-else-if="order.isStatusChanged">
                            <button @click="saveOrderStatus(order)" class="btn btn-sm btn-success rounded-circle shadow-sm d-flex align-items-center justify-content-center ms-1" style="width: 24px; height: 24px; padding: 0; flex-shrink: 0 !important;" title="Lưu trạng thái">
                              <i class="bi bi-check-lg fw-bold" style="font-size: 0.7rem;"></i>
                            </button>
                            <button @click="cancelStatusChange(order)" class="btn btn-sm btn-light rounded-circle shadow-sm text-danger border d-flex align-items-center justify-content-center ms-1" style="width: 24px; height: 24px; padding: 0; flex-shrink: 0 !important;" title="Hủy">
                              <i class="bi bi-x-lg fw-bold" style="font-size: 0.7rem;"></i>
                            </button>
                          </template>
                        </div>
                      </div>
                    </td>

                    <td class="px-4 text-center">
                      <button class="btn btn-sm btn-light text-brand shadow-sm border" @click="openQuickView(order.id)" title="Xem chi tiết đơn"><i class="bi bi-eye-fill"></i></button>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2" v-if="pagination.lastPage > 1 && !isTableLoading">
        <span class="text-muted small">Hiển thị trang {{ pagination.currentPage }} / {{ pagination.lastPage }}</span>
        <nav>
          <ul class="pagination pagination-sm mb-0 shadow-sm">
            <li class="page-item" :class="{ disabled: pagination.currentPage === 1 }"><button class="page-link text-brand" @click="fetchData(pagination.currentPage - 1, true)"><i class="bi bi-chevron-left"></i></button></li>
            <li class="page-item" v-for="page in pagination.lastPage" :key="page" :class="{ active: pagination.currentPage === page }"><button class="page-link" :class="pagination.currentPage === page ? 'bg-brand border-brand text-white' : 'text-dark'" @click="fetchData(page, true)">{{ page }}</button></li>
            <li class="page-item" :class="{ disabled: pagination.currentPage === pagination.lastPage }"><button class="page-link text-brand" @click="fetchData(pagination.currentPage + 1, true)"><i class="bi bi-chevron-right"></i></button></li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- MODAL QUICK VIEW -->
    <div class="modal fade" id="quickViewOrderModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="modal-header border-bottom pb-3 bg-light rounded-top-4">
            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-receipt text-brand me-2"></i>Chi Tiết Đơn Hàng <span class="text-brand font-monospace">{{ selectedOrder?.order_code }}</span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          
          <div class="modal-body p-4 bg-white" v-if="selectedOrder">
            <div class="row g-4">
                <div class="col-lg-4 border-end">
                    <h6 class="fw-bold text-muted text-uppercase mb-3"><i class="bi bi-person-badge me-2"></i>Người nhận</h6>
                    <div class="mb-2"><span class="text-muted">Họ tên:</span> <strong class="text-dark float-end">{{ selectedOrder.customer_name }}</strong></div>
                    <div class="mb-2"><span class="text-muted">Điện thoại:</span> <strong class="text-dark float-end">{{ selectedOrder.customer_phone }}</strong></div>
                    <div class="mb-3">
                        <span class="text-muted d-block mb-1">Địa chỉ giao hàng:</span> 
                        <div class="p-2 bg-light border rounded small">{{ selectedOrder.customer_address }}</div>
                    </div>
                    <div class="mb-4">
                        <span class="text-muted d-block mb-1">Ghi chú của khách:</span> 
                        <div class="p-2 bg-warning bg-opacity-10 text-dark fw-medium border border-warning rounded small fst-italic">{{ selectedOrder.order_note || 'Không có ghi chú' }}</div>
                    </div>

                    <h6 class="fw-bold text-muted text-uppercase mb-3 border-top pt-4"><i class="bi bi-credit-card me-2"></i>Thanh toán</h6>
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <span class="text-muted">Phương thức:</span> 
                        <span class="badge bg-secondary text-uppercase">{{ selectedOrder.payment_method }}</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <span class="text-muted">Trạng thái TT:</span> 
                        <span class="badge" :class="getPaymentBadge(selectedOrder.payment_status)">{{ formatPaymentStatus(selectedOrder.payment_status) }}</span>
                    </div>
                </div>

                <div class="col-lg-8">
                    <h6 class="fw-bold text-muted text-uppercase mb-3"><i class="bi bi-box-seam me-2"></i>Sản phẩm đã đặt</h6>
                    <div class="table-responsive border rounded mb-4" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th class="ps-3">Sản phẩm</th>
                                    <th class="text-center">Đơn giá</th>
                                    <th class="text-center">SL</th>
                                    <th class="text-end pe-3">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in selectedOrder.items" :key="item.id">
                                    <td class="ps-3 py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <img :src="item.variant_image ? `http://127.0.0.1:8000/storage/${item.variant_image}` : 'https://placehold.co/40'" class="rounded border" style="width: 40px; height: 40px; object-fit: cover;">
                                            <div>
                                                <div class="fw-bold text-dark text-wrap" style="max-width: 250px;">{{ item.product_name }}</div>
                                                <div class="text-muted" style="font-size: 0.7rem;">SKU: {{ item.variant_sku }}</div>
                                                
                                                <div class="text-brand mt-1" style="font-size: 0.7rem;" v-if="item.combo_id">
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="bi bi-stars text-brand me-1"></i> Combo ({{ parseCombo(item.combo_selections).length }} món)
                                                    </span>
                                                </div>
                                                <div class="text-brand mt-1" style="font-size: 0.7rem;" v-else-if="item.variant_attributes">
                                                    <span v-for="(val, key) in parseAttributes(item.variant_attributes)" :key="key" class="me-2">[{{ key }}: {{ val }}]</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center fw-medium">{{ formatCurrency(item.price) }}</td>
                                    <td class="text-center fw-bold text-brand">x{{ item.quantity }}</td>
                                    <td class="text-end pe-3 fw-bold text-success">{{ formatCurrency(item.total_price) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded border">
                                <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Tạm tính:</span> <strong>{{ formatCurrency(selectedOrder.sub_total) }}</strong></div>
                                <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Phí giao hàng:</span> <strong>{{ formatCurrency(selectedOrder.shipping_fee) }}</strong></div>
                                <div class="d-flex justify-content-between mb-2 small text-danger"><span class="text-muted">Giảm giá <span v-if="selectedOrder.coupon_code">({{ selectedOrder.coupon_code }})</span>:</span> <strong>- {{ formatCurrency(selectedOrder.discount_amount) }}</strong></div>
                                <div class="d-flex justify-content-between mt-3 pt-2 border-top"><span class="fw-bold text-dark">TỔNG CỘNG:</span> <strong class="fs-5 text-success">{{ formatCurrency(selectedOrder.total_amount) }}</strong></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6 class="fw-bold text-muted text-uppercase mb-3"><i class="bi bi-clock-history me-2"></i>Lịch sử cập nhật</h6>
                        <ul class="list-group list-group-flush border rounded custom-scrollbar-y" style="max-height: 200px; overflow-y: auto;">
                            <li class="list-group-item d-flex justify-content-between align-items-start bg-light" v-for="history in selectedOrder.histories" :key="history.id">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold text-dark small">
                                        <span class="text-muted fw-normal me-1">{{ formatDateTime(history.created_at) }}</span>
                                        Chuyển sang <span class="badge ms-1 text-dark border">{{ formatOrderStatus(history.new_status) }}</span>
                                    </div>
                                    <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                        <i class="bi bi-person-fill me-1"></i>Bởi: <strong>{{ history.changer?.fullName || 'Hệ thống/Khách' }}</strong>
                                        <span v-if="history.note" class="ms-2 fst-italic text-danger fw-semibold">- "{{ history.note }}"</span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
          </div>

          <div class="modal-footer bg-light border-top-0 rounded-bottom-4">
             <button type="button" class="btn btn-outline-brand rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Đóng</button>
             <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" @click="printOrder"><i class="bi bi-printer me-2"></i> In Hóa Đơn</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';

const route = useRoute();
const orders = ref([]);
const systemModules = ref([]); 

const isFirstLoad = ref(true);
const isTableLoading = ref(false);
const isSilentLoading = ref(false); 

const searchQuery = ref('');
const activeTab = ref('all');
const filters = ref({ 
    payment_status: 'all',
    start_date: '',
    end_date: ''
});
const currentPageLevel = ref(null);

const pagination = ref({ currentPage: 1, lastPage: 1, total: 0 });

const selectedOrder = ref(null);
let quickViewModalInstance = null;
let isUnmounted = false;

const statusCounts = ref({
    all: 0, pending: 0, confirmed: 0, processing: 0, shipping: 0, delivered: 0, cancelled: 0, returned: 0
});

const tabCache = ref({});

onBeforeUnmount(() => {
  isUnmounted = true;
  if (quickViewModalInstance) quickViewModalInstance.hide();
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.className = '';
  document.body.style = '';
});

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

const formatCurrency = (val) => {
  if (val === null || val === undefined || val === '' || isNaN(val)) return '---';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(val);
};
const formatDate = (dateString) => { if (!dateString) return ''; const d = new Date(dateString); return d.toLocaleDateString('vi-VN'); };
const formatDateTime = (dateString) => { if (!dateString) return ''; const d = new Date(dateString); return d.toLocaleString('vi-VN'); };

const parseAttributes = (attr) => {
  if (!attr) return {};
  if (typeof attr === 'object') return attr;
  try { return JSON.parse(attr); } catch { return {}; }
};

const parseCombo = (combo) => {
  if (!combo) return [];
  if (typeof combo === 'object') return combo;
  try { return JSON.parse(combo); } catch { return []; }
};

const getLevelColor = (level) => {
  if(!level) return 'bg-secondary';
  const l = parseInt(level);
  switch (l) {
    case 1: return 'bg-danger text-white border-danger shadow-sm';        
    case 2: return 'bg-warning text-dark border-warning';                  
    case 3: return 'bg-info text-dark border-info';                        
    case 4: return 'bg-primary bg-opacity-10 text-primary border-primary'; 
    case 5: return 'bg-success bg-opacity-10 text-success border-success'; 
    default: return 'bg-light text-secondary border-secondary'; 
  }
};

const formatPaymentStatus = (status) => {
    const map = { 'unpaid': 'Chưa TT', 'paid': 'Đã TT', 'refunded': 'Đã hoàn', 'failed': 'Thất bại' };
    return map[status] || status;
};

const getPaymentSelectClass = (status) => {
    const map = { 
      'unpaid': 'text-warning border-warning bg-warning bg-opacity-10', 
      'paid': 'text-success border-success bg-success bg-opacity-10', 
      'refunded': 'text-info border-info bg-info bg-opacity-10', 
      'failed': 'text-danger border-danger bg-danger bg-opacity-10' 
    };
    return map[status] || 'bg-light text-secondary';
};

const getPaymentBadge = (status) => {
    const map = { 'unpaid': 'bg-warning text-dark border-warning', 'paid': 'bg-success text-white border-success', 'refunded': 'bg-info text-dark border-info', 'failed': 'bg-danger text-white border-danger' };
    return map[status] || 'bg-secondary';
};

const formatOrderStatus = (status) => {
    const map = { 'pending': 'Chờ duyệt', 'confirmed': 'Đã xác nhận', 'processing': 'Đang chuẩn bị', 'shipping': 'Đang giao', 'delivered': 'Đã giao', 'cancelled': 'Đã hủy', 'returned': 'Hoàn trả' };
    return map[status] || status;
};

const getOrderStatusClass = (status) => {
  const map = { 
    'pending': 'text-warning border-warning bg-warning bg-opacity-10', 
    'confirmed': 'text-info border-info bg-info bg-opacity-10', 
    'processing': 'text-primary border-primary bg-primary bg-opacity-10', 
    'shipping': 'text-primary border-primary bg-primary bg-opacity-10', 
    'delivered': 'text-success border-success bg-success bg-opacity-10',
    'cancelled': 'text-danger border-danger bg-danger bg-opacity-10',
    'returned': 'text-secondary border-secondary bg-secondary bg-opacity-10'
  }; 
  return map[status] || 'bg-light text-secondary'; 
};

const allowedTransitions = {
    'pending': ['pending', 'confirmed', 'cancelled'],
    'confirmed': ['confirmed', 'processing', 'cancelled'],
    'processing': ['processing', 'shipping', 'cancelled'],
    'shipping': ['shipping', 'delivered', 'cancelled'],
    'delivered': ['delivered'],
    'cancelled': ['cancelled'],
    'returned': ['returned']
};

const canTransitionTo = (currentStatus, targetStatus) => {
    return allowedTransitions[currentStatus]?.includes(targetStatus);
};

const checkStatusChange = (order) => { order.isStatusChanged = (order.localStatus !== order.status); };
const cancelStatusChange = (order) => { order.localStatus = order.status; order.isStatusChanged = false; };

const saveOrderStatus = async (order) => {
  // BẢO VỆ DOANH THU
  if (order.localStatus === 'delivered' && order.payment_status !== 'paid') {
      Swal.fire({
          title: 'Khoan đã! Chưa thu tiền',
          text: 'Để đảm bảo doanh thu, vui lòng cập nhật trạng thái Thanh toán thành "Đã TT" trước khi xác nhận Giao hàng Hoàn tất.',
          icon: 'warning',
          confirmButtonColor: '#009981',
          confirmButtonText: 'Đã hiểu'
      });
      cancelStatusChange(order);
      return;
  }

  const isRequireNote = order.localStatus === 'cancelled';
  
  const { value: noteText, isDismissed } = await Swal.fire({
    title: 'Cập nhật Trạng thái',
    text: `Chuyển đơn hàng sang: ${formatOrderStatus(order.localStatus)}`,
    input: 'textarea',
    inputPlaceholder: 'Nhập ghi chú / lý do cập nhật (Bắt buộc nếu Hủy đơn)...',
    showCancelButton: true,
    confirmButtonColor: '#009981',
    cancelButtonText: 'Hủy bỏ',
    confirmButtonText: 'Lưu cập nhật',
    inputValidator: (value) => {
        if (isRequireNote && !value) return 'Bạn cần nhập lý do cho thao tác này!';
    }
  });

  if (isDismissed) {
      cancelStatusChange(order);
      return;
  }

  order.isUpdatingStatus = true;
  const payload = {
      status: order.localStatus,
      payment_status: order.payment_status,
      note: noteText || ''
  };

  await sendUpdateRequest(order, payload, 'isUpdatingStatus', 'status', order.localStatus, 'isStatusChanged');
};

const allowedPaymentTransitions = {
    'unpaid': ['unpaid', 'paid', 'failed'],
    'paid': ['paid', 'refunded'], 
    'failed': ['failed', 'unpaid', 'paid'], 
    'refunded': ['refunded'] 
};

const canPaymentTransitionTo = (currentStatus, targetStatus) => {
    return allowedPaymentTransitions[currentStatus]?.includes(targetStatus);
};

const checkPaymentStatusChange = (order) => { order.isPaymentStatusChanged = (order.localPaymentStatus !== order.payment_status); };
const cancelPaymentStatusChange = (order) => { order.localPaymentStatus = order.payment_status; order.isPaymentStatusChanged = false; };

const savePaymentStatus = async (order) => {
  const { isDismissed } = await Swal.fire({
    title: 'Cập nhật Thanh toán',
    text: `Xác nhận đổi trạng thái thu tiền thành: ${formatPaymentStatus(order.localPaymentStatus)}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#009981',
    cancelButtonText: 'Hủy bỏ',
    confirmButtonText: 'Đồng ý cập nhật'
  });

  if (isDismissed) {
      cancelPaymentStatusChange(order);
      return;
  }

  order.isUpdatingPayment = true;
  const payload = {
      status: order.status,
      payment_status: order.localPaymentStatus,
      note: `Kế toán cập nhật thanh toán: ${formatPaymentStatus(order.localPaymentStatus)}`
  };

  await sendUpdateRequest(order, payload, 'isUpdatingPayment', 'payment_status', order.localPaymentStatus, 'isPaymentStatusChanged');
};

// ĐÃ NÂNG CẤP: BẮT LỖI 422 VÀ HIỂN THỊ CÂU CHỬI CỦA LARAVEL
const sendUpdateRequest = async (order, payload, loadingFlag, targetField, newValue, changedFlag) => {
  try {
    const res = await axios.put(`http://127.0.0.1:8000/api/admin/orders/${order.id}/status`, payload, { 
      headers: getHeaders() 
    });
    
    order[targetField] = newValue; 
    order[changedFlag] = false;
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Cập nhật hệ thống thành công', showConfirmButton: false, timer: 1500 });
    
    tabCache.value = {}; 
    fetchCounts(); 
    
  } catch (error) { 
    if (targetField === 'status') cancelStatusChange(order); else cancelPaymentStatusChange(order);
    
    if (error.response) {
      if (error.response.status === 422) {
          // Bắt đúng lỗi Validation từ Backend trả về
          const errors = error.response.data.errors;
          let errorMsg = error.response.data.message || 'Dữ liệu không hợp lệ!';
          
          if (errors) {
              // Lấy thông báo lỗi đầu tiên trong mảng errors
              errorMsg = Object.values(errors)[0][0]; 
          }
          
          Swal.fire({
              icon: 'error',
              title: 'Không được phép!',
              text: errorMsg,
              confirmButtonColor: '#009981'
          });
      } else if (error.response.status === 401) {
          Swal.fire('Lỗi xác thực', 'Phiên đăng nhập đã hết hạn!', 'error');
      } else {
          Swal.fire('Lỗi', `Máy chủ từ chối cập nhật (Lỗi ${error.response.status})`, 'error');
      }
    } else {
      Swal.fire('Lỗi', 'Lỗi kết nối mạng', 'error'); 
    }
  } finally {
    order[loadingFlag] = false;
  }
};

const openQuickView = async (id) => {
  try {
    const res = await axios.get(`http://127.0.0.1:8000/api/admin/orders/${id}`, { headers: getHeaders() });
    if(!isUnmounted) {
      selectedOrder.value = res.data.data;
      if(!quickViewModalInstance) quickViewModalInstance = new window.bootstrap.Modal(document.getElementById('quickViewOrderModal'));
      quickViewModalInstance.show();
    }
  } catch(e){}
};

const fetchCounts = async () => {
    try {
        const statuses = ['all', 'pending', 'confirmed', 'processing', 'shipping', 'delivered', 'cancelled', 'returned'];
        const requests = statuses.map(status => {
            let url = `http://127.0.0.1:8000/api/admin/orders?page=1`;
            if (status !== 'all') url += `&status=${status}`;
            return axios.get(url, { headers: getHeaders() }).then(res => res.data);
        });

        const results = await Promise.all(requests);
        statuses.forEach((status, index) => {
            if (results[index] && results[index].data) {
                statusCounts.value[status] = results[index].data.total;
            }
        });
        statusCounts.value.all = statusCounts.value.all - (statusCounts.value.returned || 0);
    } catch (e) { console.error(e); }
};

const fetchData = async (page = 1, silent = false) => {
  const cacheKey = `${activeTab.value}_${page}_${filters.value.payment_status}_${filters.value.start_date}_${filters.value.end_date}`;

  if (tabCache.value[cacheKey]) {
      orders.value = tabCache.value[cacheKey].data;
      pagination.value = tabCache.value[cacheKey].pagination;
      isSilentLoading.value = true;
  } else {
      if (silent) isSilentLoading.value = true;
      else if (!isFirstLoad.value) isTableLoading.value = true; 
  }
  
  let queryParams = new URLSearchParams({ page });
  if (activeTab.value !== 'all') queryParams.append('status', activeTab.value);
  if (filters.value.payment_status !== 'all') queryParams.append('payment_status', filters.value.payment_status);
  if (filters.value.start_date) queryParams.append('start_date', filters.value.start_date);
  if (filters.value.end_date) queryParams.append('end_date', filters.value.end_date);

  try {
    const [resOrders, resModules] = await Promise.all([
      axios.get(`http://127.0.0.1:8000/api/admin/orders?${queryParams.toString()}`, { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/admin/modules', { headers: getHeaders() })
    ]);
    
    if (isUnmounted) return;

    const sysModules = resModules.data.data;
    const currentModule = sysModules.find(m => m.module_code === (route.meta.moduleCode || 'admin_orders'));
    if (currentModule) currentPageLevel.value = currentModule.required_level;

    const result = resOrders.data;
    const dataPayload = result.data.data ? result.data.data : result.data; 
    
    const mappedOrders = dataPayload.map(o => ({
      ...o, 
      localStatus: o.status, 
      isStatusChanged: false, 
      isUpdatingStatus: false,
      localPaymentStatus: o.payment_status,
      isPaymentStatusChanged: false,
      isUpdatingPayment: false
    }));

    const newPagination = result.data.last_page ? {
        currentPage: result.data.current_page,
        lastPage: result.data.last_page,
        total: result.data.total
    } : pagination.value;

    orders.value = mappedOrders;
    pagination.value = newPagination;

    tabCache.value[cacheKey] = { data: mappedOrders, pagination: newPagination };

  } catch (error) { 
    console.error('Lỗi Axios Load Data:', error);
  } finally { 
    if(!isUnmounted) {
      isFirstLoad.value = false;
      isTableLoading.value = false;
      isSilentLoading.value = false;
    }
  }
};

const switchTab = (tabId) => { 
    activeTab.value = tabId; 
    fetchData(1, true); 
};

const printOrder = () => {
    Swal.fire({icon: 'info', title: 'Đang phát triển', text: 'Tính năng in hóa đơn PDF sẽ được ra mắt trong bản cập nhật tới.'});
};

const displayedOrders = computed(() => {
    if (activeTab.value === 'all') {
        return orders.value.filter(o => o.status !== 'returned');
    }
    return orders.value;
});

onMounted(() => { 
    fetchData(1); 
    fetchCounts();
});
</script>

<style scoped>
.custom-tab { font-weight: 600 !important; color: #6c757d; border-bottom: 2px solid transparent !important; margin-bottom: -1px; transition: color 0.2s ease; }
.custom-tab:hover { color: #009981; }
.custom-tab.active-tab { color: #009981 !important; border-bottom: 2px solid #009981 !important; }
.tab-badge { font-size: 0.75rem; font-weight: 600; background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; transition: all 0.2s ease; }
.active-badge { background-color: #e6f5f2 !important; color: #009981 !important; border-color: #009981 !important; }

.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, #009981 30%, #4dffdf 50%, #009981 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }

.bg-brand { background-color: #009981 !important; } .text-brand { color: #009981 !important; } .border-brand { border-color: #009981 !important; }
.btn-brand { background-color: #009981; border: none; transition: 0.2s; } .btn-brand:hover { background-color: #007a67; }
.btn-outline-brand { color: #009981; border-color: #009981; transition: 0.2s; } .btn-outline-brand:hover { background-color: #009981; color: white; }

.cursor-pointer { cursor: pointer; }
.custom-scrollbar-y::-webkit-scrollbar { width: 4px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: #e0e0e0; border-radius: 10px; }
</style>