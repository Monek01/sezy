<script setup>
import { Link, router } from '@inertiajs/vue3';
import { HeartIcon, StarIcon } from '@heroicons/vue/24/solid';
import { HeartIcon as HeartOutline, ShoppingBagIcon } from '@heroicons/vue/24/outline';
import { computed, ref } from 'vue';

const props = defineProps({
    product: { type: Object, required: true },
});

const adding = ref(false);

const imageUrl = computed(() => {
    const img = props.product.primary_image ?? props.product.images?.[0];
    return img ? `/storage/${img.path}` : '/images/placeholder-product.svg';
});

const conditionLabel = computed(() => {
    const cond = props.product.condition;
    if (cond === 'fripe') return { text: '♻️ Fripe', cls: 'bg-emerald-100 text-emerald-700' };
    if (cond === 'neuf') return { text: '🆕 Neuf', cls: 'bg-blue-100 text-blue-700' };
    return null;
});

function formatPrice(value) {
    return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
}

function quickAdd() {
    adding.value = true;
    router.post(route('cart.store'), {
        product_id: props.product.id,
        quantity: 1,
    }, {
        preserveScroll: true,
        onFinish: () => setTimeout(() => { adding.value = false; }, 1500),
    });
}
</script>

<template>
    <div class="group relative flex flex-col bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg border border-gray-100 hover:border-sezy-100 transition-all duration-300">
        <!-- Image -->
        <Link :href="route('products.show', product.slug)" class="block relative overflow-hidden bg-gray-50" style="aspect-ratio: 1">
            <img :src="imageUrl" :alt="product.title"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                 loading="lazy"
                 @error="$event.target.src = '/images/placeholder-product.svg'" />

            <!-- Badges -->
            <div class="absolute top-2 left-2 flex flex-col gap-1">
                <span v-if="product.discount_percent > 0"
                      class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow">
                    -{{ product.discount_percent }}%
                </span>
                <span v-if="conditionLabel"
                      class="text-[10px] font-semibold px-2 py-0.5 rounded-full shadow"
                      :class="conditionLabel.cls">
                    {{ conditionLabel.text }}
                </span>
            </div>

            <!-- Out of stock overlay -->
            <div v-if="!product.is_in_stock" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center">
                <span class="bg-gray-800 text-white text-xs font-semibold px-3 py-1.5 rounded-full">Rupture de stock</span>
            </div>

            <!-- Quick add button (hover) -->
            <div class="absolute bottom-2 inset-x-2 opacity-0 group-hover:opacity-100 transition-all duration-200 translate-y-2 group-hover:translate-y-0">
                <button
                    @click.prevent="quickAdd"
                    :disabled="!product.is_in_stock || adding"
                    class="w-full flex items-center justify-center gap-2 bg-sezy-dark text-white text-xs font-semibold py-2 rounded-xl hover:bg-sezy-orange disabled:opacity-50 disabled:cursor-not-allowed shadow-lg transition"
                >
                    <ShoppingBagIcon class="w-3.5 h-3.5" />
                    <span v-if="adding">Ajouté ✓</span>
                    <span v-else>Ajouter au panier</span>
                </button>
            </div>
        </Link>

        <!-- Info -->
        <div class="p-3 flex flex-col flex-1">
            <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">{{ product.category?.name }}</p>
            <Link :href="route('products.show', product.slug)"
                  class="font-semibold text-sm text-gray-900 line-clamp-2 mt-0.5 hover:text-sezy-dark transition leading-snug">
                {{ product.title }}
            </Link>

            <!-- Stars -->
            <div v-if="product.average_rating > 0" class="flex items-center gap-1 mt-1.5">
                <div class="flex">
                    <StarIcon v-for="i in 5" :key="i" class="w-3 h-3" :class="i <= Math.round(product.average_rating) ? 'text-amber-400' : 'text-gray-200'" />
                </div>
                <span class="text-[10px] text-gray-400">({{ product.reviews_count }})</span>
            </div>

            <!-- Price -->
            <div class="mt-auto pt-2 flex items-end justify-between gap-2">
                <div>
                    <p class="font-bold text-sezy-dark text-sm">{{ formatPrice(product.price) }}</p>
                    <p v-if="product.has_discount" class="text-[10px] text-gray-400 line-through">{{ formatPrice(product.compare_at_price) }}</p>
                </div>
                <!-- Mobile quick-add button -->
                <button
                    @click="quickAdd"
                    :disabled="!product.is_in_stock || adding"
                    class="md:hidden bg-sezy-dark text-white rounded-xl w-8 h-8 flex items-center justify-center hover:bg-sezy-900 disabled:opacity-40 disabled:cursor-not-allowed shrink-0 transition"
                    aria-label="Ajouter au panier"
                >
                    <span v-if="adding" class="text-xs">✓</span>
                    <span v-else class="text-base leading-none font-bold">+</span>
                </button>
            </div>
        </div>
    </div>
</template>
