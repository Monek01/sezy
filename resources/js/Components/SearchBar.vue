<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const query = ref('');
const results = ref({ products: [], categories: [] });
const open = ref(false);
const loading = ref(false);
const input = ref(null);
let debounce = null;

watch(query, (val) => {
    clearTimeout(debounce);
    if (val.trim().length < 2) {
        results.value = { products: [], categories: [] };
        open.value = false;
        return;
    }
    loading.value = true;
    debounce = setTimeout(async () => {
        try {
            const { data } = await axios.get(route('search.autocomplete'), { params: { q: val } });
            results.value = data;
            open.value = data.products.length > 0 || data.categories.length > 0;
        } finally {
            loading.value = false;
        }
    }, 250);
});

function go(href) {
    open.value = false;
    query.value = '';
    router.visit(href);
}

function submit() {
    if (!query.value.trim()) return;
    open.value = false;
    router.get(route('search'), { q: query.value.trim() });
    query.value = '';
}

function close(e) {
    if (!e.target.closest('.search-bar-container')) {
        open.value = false;
    }
}

onMounted(() => document.addEventListener('click', close));
onUnmounted(() => document.removeEventListener('click', close));

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v) + ' FCFA'; }
</script>

<template>
    <div class="relative search-bar-container w-full max-w-md">
        <form @submit.prevent="submit" class="relative">
            <input
                ref="input"
                v-model="query"
                type="text"
                placeholder="Rechercher…"
                class="w-full pl-4 pr-10 py-2 rounded-xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-sezy focus:border-sezy transition"
                @focus="open = results.products.length > 0 || results.categories.length > 0"
            />
            <button type="submit" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-sezy-dark transition">
                <svg v-if="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
            </button>
        </form>

        <!-- Dropdown -->
        <Transition enter-active-class="transition duration-150" enter-from-class="opacity-0 -translate-y-1 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100" leave-active-class="transition duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="open" class="absolute top-full left-0 right-0 mt-1.5 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden">
                <!-- Categories -->
                <div v-if="results.categories.length" class="border-b border-gray-50">
                    <p class="px-4 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase">Catégories</p>
                    <button v-for="cat in results.categories" :key="cat.id"
                            @click="go(route('products.index', { category: cat.slug }))"
                            class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 transition">
                        <span class="w-5 h-5 rounded bg-sezy-50 text-sezy-dark flex items-center justify-center text-xs">📁</span>
                        {{ cat.name }}
                    </button>
                </div>
                <!-- Products -->
                <div v-if="results.products.length">
                    <p class="px-4 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase">Produits</p>
                    <button v-for="p in results.products" :key="p.id"
                            @click="go(route('products.show', p.slug))"
                            class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center gap-3 transition">
                        <img :src="p.image ? `/storage/${p.image}` : '/images/placeholder-product.svg'"
                             class="w-9 h-9 rounded-lg object-cover shrink-0"
                             @error="$event.target.src='/images/placeholder-product.svg'" />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ p.title }}</p>
                            <p class="text-xs text-sezy-dark font-semibold">{{ fmt(p.price) }}</p>
                        </div>
                    </button>
                </div>
                <!-- See all -->
                <button @click="submit"
                        class="w-full px-4 py-2.5 text-center text-sm text-sezy-dark font-semibold hover:bg-sezy-50 transition border-t border-gray-50">
                    Voir tous les résultats pour "{{ query }}" →
                </button>
            </div>
        </Transition>
    </div>
</template>
