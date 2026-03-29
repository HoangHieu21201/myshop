<template>
  <div class="user-layout d-flex flex-column min-vh-100">
    <Header />

    <main class="flex-grow-1">
      <router-view></router-view>
    </main>

    <Footer />

    <SoraChatbot />
  </div>
</template>

<script setup>
import Header from '../components/user/Header.vue';
import Footer from '../components/user/Footer.vue';
import SoraChatbot from '@/components/user/SoraChatbot.vue';
import router from '@/router';
</script>