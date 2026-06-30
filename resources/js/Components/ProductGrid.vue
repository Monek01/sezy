<script setup>
import ProductCard from '@/Components/ProductCard.vue';

defineProps({
    products: { type: Array, required: true },
});
</script>

<template>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5">
        <ProductCard v-for="product in products" :key="product.id" :product="product" />
    </div>
</template>
