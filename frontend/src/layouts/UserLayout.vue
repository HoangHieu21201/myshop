<script setup>
import userHeader from '../components/user/Header.vue';
import userFooter from '../components/user/Footer.vue';
</script>

<template>
    <userHeader />
    <router-view></router-view>
    <userFooter />
</template>

<style scoped>

</style>