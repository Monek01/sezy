<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    cart: Object,
});

const subtotal = computed(() => props.cart.items.reduce((sum, item) => sum + item.unit_price * item.quantity, 0));

function formatPrice(value) {
    return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
}

function updateQuantity(item, quantity) {
    router.patch(route('cart.update', item.id), { quantity }, { preserveScroll: true });
}

function removeItem(item) {
    router.delete(route('cart.destroy', item.id), { preserveScroll: true });
}

function imageUrl(item) {
    const img = item.product?.primary_image;
    return img ? `/storage/${img.path}` : '/images/placeholder-product.svg';
}
</script>

<template>
    <ShopLayout>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Mon panier</h1>

            <div v-if="!cart.items.length" class="text-center py-20">
                <p class="text-gray-500 mb-4">Votre panier est vide.</p>
                <Link :href="route('products.index')" class="btn-primary">Découvrir le catalogue</Link>
            </div>

            <div v-else class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-3">
                    <div v-for="item in cart.items" :key="item.id" class="card p-4 flex gap-4">
                        <img :src="imageUrl(item)" class="w-20 h-20 rounded-lg object-cover bg-gray-50 shrink-0" />
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="font-medium text-sm truncate">{{ item.product?.title }}</p>
                                    <p v-if="item.variant" class="text-xs text-gray-400">{{ item.variant.label }}</p>
                                    <p class="text-sezy-dark font-bold mt-1">{{ formatPrice(item.unit_price) }}</p>
                                </div>
                                <button @click="removeItem(item)" class="text-gray-300 hover:text-red-500 shrink-0">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                            <div class="flex items-center border border-gray-200 rounded-lg w-fit mt-2">
                                <button class="px-3 py-1 text-lg" @click="updateQuantity(item, item.quantity - 1)">−</button>
                                <span class="px-3 text-sm font-medium">{{ item.quantity }}</span>
                                <button class="px-3 py-1 text-lg" @click="updateQuantity(item, item.quantity + 1)">+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card p-5 h-fit sticky top-20">
                    <h2 class="font-bold mb-4">Récapitulatif</h2>
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span>Sous-total</span>
                        <span>{{ formatPrice(subtotal) }}</span>
                    </div>
                    <p class="text-xs text-gray-400 mb-4">Frais de livraison calculés à l'étape suivante.</p>
                    <div class="border-t pt-3 flex justify-between font-bold text-lg mb-5">
                        <span>Total</span>
                        <span class="text-sezy-dark">{{ formatPrice(subtotal) }}</span>
                    </div>
                    <Link :href="route('checkout.shipping')" class="btn-primary w-full">
                        Passer la commande
                    </Link>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>
