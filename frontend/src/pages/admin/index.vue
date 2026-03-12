<template>
    <div class="admin-dashboard">
        <h1 class="text-center my-5">Chào mừng đến với Trang Quản Trị MyShop!</h1>
        <p class="text-center mb-4">Tại đây bạn có thể quản lý sản phẩm, đơn hàng, khách hàng và nhiều hơn nữa.</p>
        <div class="text-center">
        <router-link to="/admin/products" class="btn btn-primary mx-2">Quản lý Sản phẩm</router-link>
        <router-link to="/admin/orders" class="btn btn-secondary mx-2">Quản lý Đơn hàng</router-link>
        <router-link to="/admin/customers" class="btn btn-success mx-2">Quản lý Khách hàng</router-link>
        </div>
    </div>
</template>