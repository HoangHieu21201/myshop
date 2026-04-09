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

// (Hoặc import LiveChatWidget from '@/components/LiveChatWidget.vue'; tùy cách sếp setup)
import Header from '../components/user/Header.vue';
import Footer from '../components/user/Footer.vue';
import SoraChatbot from '@/components/user/SoraChatbot.vue';
import router from '@/router';
</script>