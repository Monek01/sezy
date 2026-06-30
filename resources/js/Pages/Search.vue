<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    products: Object,
    query:    String,
});

const searchInput = ref(props.query);

function doSearch() {
    if (!searchInput.value.trim()) return;
    router.get(route('search'), { q: searchInput.value.trim() }, { preserveState: false });
}
</script>

<template>
    <ShopLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
            <!-- Search bar -->
            <div class="mb-8 max-w-xl mx-auto">
                <form @submit.prevent="doSearch" class="flex gap-2">
                    <input v-model="searchInput" type="text"
                           placeholder="Rechercher un produit…"
                           class="input-field flex-1 text-base py-3 px-4 rounded-2xl" />
                    <button type="submit" class="btn-primary rounded-2xl px-5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form>
            </div>

            <div v-if="query">
                <div class="flex items-center justify-between mb-5">
                    <h1 class="text-lg font-bold text-gray-900">
                        Résultats pour "<span class="text-sezy-dark">{{ query }}</span>"
                    </h1>
                    <p class="text-sm text-gray-400">{{ products.total }} résultat{{ products.total > 1 ? 's' : '' }}</p>
                </div>

                <div v-if="products.data.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
                    <ProductCard v-for="p in products.data" :key="p.id" :product="p" />
                </div>

                <div v-else class="text-center py-20">
                    <p class="text-4xl mb-4">🔍</p>
                    <h2 class="text-xl font-bold text-gray-700 mb-2">Aucun résultat trouvé</h2>
                    <p class="text-gray-400 mb-6">Essayez avec d'autres mots-clés ou naviguez dans nos catégories.</p>
                    <Link :href="route('products.index')" class="btn-primary">Voir tous les produits</Link>
                </div>

                <!-- Pagination -->
                <div v-if="products.last_page > 1" class="flex justify-center gap-1">
                    <a v-for="link in products.links" :key="link.label"
                       :href="link.url ?? '#'"
                       @click.prevent="link.url && router.get(link.url)"
                       :class="['px-3 py-1.5 rounded-lg text-sm transition',
                                link.active ? 'bg-sezy-dark text-white' : 'text-gray-600 hover:bg-gray-100',
                                !link.url ? 'opacity-40 pointer-events-none' : '']"
                       v-html="link.label" />
                </div>
            </div>

            <div v-else class="text-center py-20">
                <p class="text-4xl mb-4">🛍️</p>
                <p class="text-gray-500 text-lg">Que recherchez-vous ?</p>
                <p class="text-gray-400 text-sm mt-2">Tapez un nom de produit, une marque ou une catégorie.</p>
            </div>
        </div>
    </ShopLayout>
</template>
