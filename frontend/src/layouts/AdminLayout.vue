
<script setup>
import Sidebar from '../components/admin/Sidebar.vue';
import Header from '../components/admin/Header.vue';
import Footer from '../components/admin/Footer.vue';
</script>

<template>
  <div class="admin-layout-wrapper d-flex" style="min-height: 100vh;">
    
    <Sidebar />

    <div class="content-wrapper flex-grow-1 d-flex flex-column">
      
      <Header />

      <main class="main-content flex-grow-1">
        <div class="container-fluid">
          <router-view v-slot="{ Component }">
            <transition name="fade" mode="out-in">
              <component :is="Component" />
            </transition>
          </router-view>
        </div>
      </main>

      <Footer />
      
    </div>
  </div>
</template>


<style scoped>
.admin-layout-wrapper {
  background-color: #f4f6f9; 
  overflow-x: hidden;
}

.content-wrapper {
  width: calc(100% - 250px);
  min-height: 100vh;
}

.main-content {
  background-color: #f4f6f9;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>