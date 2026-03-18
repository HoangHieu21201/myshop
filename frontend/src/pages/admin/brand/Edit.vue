<template>
  <div class="brand-edit-wrapper pb-5 mb-5">
    <div class="container-fluid py-4" v-if="!isPageLoading">
      
      <div class="row mb-4 align-items-center">
        <div class="col-md-6 d-flex align-items-center">
          <router-link :to="{ name: 'admin-brands' }" class="btn btn-light shadow-sm me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
            <i class="bi bi-arrow-left fw-bold"></i>
          </router-link>
          <div class="d-flex flex-column">
            <h3 class="fw-bold text-dark mb-0">Cập nhật Thương hiệu</h3>
            <p class="text-muted small mb-0 mt-1">Chỉnh sửa thông tin đối tác</p>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4 p-md-5">
          <form @submit.prevent="updateBrand">
            <div class="row g-4">
              <div class="col-lg-8">
                <div class="p-4 bg-light rounded-4 border h-100">
                  <h6 class="fw-bold mb-4 text-dark form-section-title"><i class="bi bi-info-circle me-2"></i>Thông tin chung</h6>
                  <div class="row g-3">
                    <div class="col-md-12">
                      <label class="form-label fw-bold">Tên thương hiệu <span class="text-danger">*</span></label>
                      <input type="text" class="form-control form-control-lg" v-model="form.name" @input="generateSlug" required>
                    </div>
                    <div class="col-md-12">
                      <label class="form-label fw-bold">Đường dẫn (Slug)</label>
                      <input type="text" class="form-control bg-white text-muted font-monospace" v-model="form.slug" readonly>
                    </div>
                    <div class="col-md-12">
                      <label class="form-label fw-bold">Mô tả thương hiệu</label>
                      <textarea class="form-control" v-model="form.description" rows="4"></textarea>
                    </div>
                    <div class="col-md-6 mt-4">
                      <div class="form-check form-switch fs-5">
                        <input class="form-check-input cursor-pointer" type="checkbox" id="statusSwitch" v-model="form.isActive">
                        <label class="form-check-label fw-bold ms-2 cursor-pointer" for="statusSwitch">
                          <span :class="form.isActive ? 'text-success' : 'text-secondary'">{{ form.isActive ? 'Đang hoạt động' : 'Đang ẩn' }}</span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-lg-4">
                <div class="p-4 bg-light rounded-4 border text-center h-100">
                  <h6 class="fw-bold mb-3 text-start form-section-title"><i class="bi bi-image me-2"></i>Logo Thương hiệu</h6>
                  <div class="mb-3 position-relative border rounded-4 overflow-hidden bg-white shadow-sm" style="height: 250px; padding: 1rem;">
                    <img v-if="logoPreview" :src="logoPreview" class="w-100 h-100 object-fit-contain">
                    <div v-else class="d-flex flex-column justify-content-center align-items-center h-100 text-muted">
                      <i class="bi bi-building fs-1 mb-2 opacity-50"></i>
                      <span class="small fw-semibold text-secondary">Chưa có Logo</span>
                    </div>
                  </div>
                  <input type="file" class="d-none" id="logoUpload" accept="image/*" @change="handleLogoUpload">
                  <label for="logoUpload" class="btn btn-outline-brand rounded-pill w-100 fw-semibold cursor-pointer"><i class="bi bi-upload me-1"></i> Thay đổi Logo</label>
                </div>
              </div>
              
              <div class="col-12 text-end border-top pt-4 mt-2">
                <button type="submit" class="btn btn-brand px-5 fw-bold text-white rounded-pill shadow-sm py-2" :disabled="isSaving || !form.name">
                  <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> CẬP NHẬT THƯƠNG HIỆU
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <div v-else class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ThinkHub</h1>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Swal from 'sweetalert2';

const route = useRoute();
const router = useRouter();
const brandId = route.params.id;
const isPageLoading = ref(true);
const isSaving = ref(false);

const form = ref({
  name: '', slug: '', description: '', isActive: true
});
const logoFile = ref(null);
const logoPreview = ref(null);

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

const generateSlug = () => {
  let s = form.value.name.toLowerCase();
  s = s.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
  s = s.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
  s = s.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
  s = s.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
  s = s.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
  s = s.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
  s = s.replace(/đ/gi, 'd');
  form.value.slug = s.replace(/\s+/g, '-').replace(/[^a-z0-9\-]/g, '').replace(/\-\-+/g, '-');
};

const handleLogoUpload = (e) => {
  const f = e.target.files[0];
  if(f) { 
      if(f.size > 2 * 1024 * 1024) { Swal.fire('Lỗi', 'Ảnh tối đa 2MB', 'error'); return; }
      logoFile.value = f; 
      logoPreview.value = URL.createObjectURL(f); 
  }
};

const fetchData = async () => {
    try {
        const res = await fetch(`http://127.0.0.1:8000/api/admin/brands/${brandId}`, { headers: getHeaders() });
        if(res.ok) {
            const b = (await res.json()).data;
            form.value.name = b.name;
            form.value.slug = b.slug;
            form.value.description = b.description || '';
            form.value.isActive = b.status === 'active';
            if(b.logo) logoPreview.value = `http://127.0.0.1:8000/storage/${b.logo}`;
        } else {
            router.push({ name: 'admin-brands' });
        }
    } catch(e){} finally { isPageLoading.value = false; }
};

const updateBrand = async () => {
  isSaving.value = true;
  try {
    const formData = new FormData();
    formData.append('_method', 'PUT'); // Laravel Update method
    formData.append('name', form.value.name);
    formData.append('slug', form.value.slug);
    formData.append('status', form.value.isActive ? 'active' : 'hidden');
    if (form.value.description) formData.append('description', form.value.description);
    if (logoFile.value) formData.append('logo', logoFile.value);

    const res = await fetch(`http://127.0.0.1:8000/api/admin/brands/${brandId}`, {
      method: 'POST', headers: getHeaders(), body: formData
    });

    const data = await res.json();
    if (res.ok) {
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Cập nhật thành công', showConfirmButton: false, timer: 1500 }).then(() => {
        router.push({ name: 'admin-brands' });
      });
    } else {
      const errorMsg = data.errors ? Object.values(data.errors).flat().join('\n') : data.message;
      Swal.fire('Lỗi', errorMsg, 'error');
    }
  } catch(e) { Swal.fire('Lỗi', 'Mất kết nối', 'error'); } finally { isSaving.value = false; }
};

onMounted(() => fetchData());
</script>

<style scoped>
.form-section-title { font-size: 0.85rem; font-weight: 700; color: #6c757d; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; }
.bg-brand { background-color: #009981 !important; } .text-brand { color: #009981 !important; } .border-brand { border-color: #009981 !important; }
.btn-brand { background-color: #009981; color: white; transition: 0.2s; } .btn-brand:hover { background-color: #007a67; color: white; }
.btn-outline-brand { color: #009981; border-color: #009981; transition: 0.2s; } .btn-outline-brand:hover { background-color: #009981; color: white; }
.cursor-pointer { cursor: pointer; }
.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, #009981 30%, #4dffdf 50%, #009981 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }
</style>