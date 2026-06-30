<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ProductGrid from '@/Components/ProductGrid.vue';
import { router, Link } from '@inertiajs/vue3';
import { ref, reactive, watch, computed } from 'vue';
import { FunnelIcon, XMarkIcon, AdjustmentsHorizontalIcon, ChevronDownIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    products: Object,
    categories: Array,
    brands: Array,
    filters: Object,
    availableColors: { type: Array, default: () => [] },
    availableSizes: { type: Array, default: () => [] },
});

const filtersOpen = ref(false);
const form = reactive({
    q: props.filters.q ?? '',
    category: props.filters.category ?? '',
    brand: props.filters.brand ?? '',
    min_price: props.filters.min_price ?? '',
    max_price: props.filters.max_price ?? '',
    sort: props.filters.sort ?? '',
    in_stock_only: props.filters.in_stock_only ?? false,
    color: props.filters.color ?? '',
    size: props.filters.size ?? '',
    condition: props.filters.condition ?? '',
});

function applyFilters() {
    router.get(route('products.index'), { ...form }, { preserveState: true, preserveScroll: true });
    filtersOpen.value = false;
}

function resetFilters() {
    Object.keys(form).forEach((k) => (form[k] = k === 'in_stock_only' ? false : ''));
    applyFilters();
}

const activeFiltersCount = computed(() => {
    let count = 0;
    if (form.category) count++;
    if (form.brand) count++;
    if (form.min_price || form.max_price) count++;
    if (form.in_stock_only) count++;
    if (form.color) count++;
    if (form.size) count++;
    if (form.condition) count++;
    return count;
});

const colorMap = {
    'blanc': '#ffffff', 'noir': '#000000', 'rouge': '#ef4444', 'bleu': '#3b82f6',
    'vert': '#22c55e', 'jaune': '#eab308', 'orange': '#f97316', 'rose': '#ec4899',
    'violet': '#a855f7', 'marron': '#92400e', 'gris': '#6b7280', 'beige': '#d6b896',
    'or': '#d4af37', 'argent': '#c0c0c0', 'bordeaux': '#7f1d1d', 'turquoise': '#06b6d4',
};

function colorHex(colorName) {
    const key = (colorName || '').toLowerCase();
    return colorMap[key] || '#9ca3af';
}

// Detect if we're browsing a clothing/mode category
const isClothingCategory = computed(() => {
    const clothingKeywords = ['mode', 'vetement', 'vêtement', 'fripe', 'habit', 'robe', 'chemise', 'pantalon', 'chaussure'];
    const cat = (form.category || '').toLowerCase();
    return clothingKeywords.some(k => cat.includes(k));
});

const sizesNeuf = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', '36', '38', '40', '42', '44', '46', '48'];
const sizesFrepe = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'Unique'];
const standardSizes = computed(() => form.condition === 'fripe' ? sizesFrepe : sizesNeuf);
</script>

<template>
    <ShopLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
            <!-- Page header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900">
                        {{ filters.q ? `"${filters.q}"` : filters.category ? categories.find(c => c.slug === filters.category)?.name ?? 'Catalogue' : 'Tous les produits' }}
                    </h1>
                    <p class="text-gray-400 text-sm mt-0.5">{{ products.total }} produit{{ products.total > 1 ? 's' : '' }} trouvé{{ products.total > 1 ? 's' : '' }}</p>
                </div>

                <div class="flex items-center gap-2">
                    <select v-model="form.sort" @change="applyFilters" class="input-field text-sm w-auto min-w-[160px]">
                        <option value="">Pertinence</option>
                        <option value="newest">Nouveautés</option>
                        <option value="price_asc">Prix croissant</option>
                        <option value="price_desc">Prix décroissant</option>
                        <option value="rating">Meilleures notes</option>
                    </select>

                    <button class="lg:hidden flex items-center gap-2 btn-secondary !px-4 !py-2 relative" @click="filtersOpen = true">
                        <AdjustmentsHorizontalIcon class="w-4 h-4" />
                        <span class="text-sm">Filtres</span>
                        <span v-if="activeFiltersCount > 0" class="absolute -top-1.5 -right-1.5 bg-sezy-dark text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ activeFiltersCount }}</span>
                    </button>
                </div>
            </div>

            <!-- Active filter chips -->
            <div v-if="activeFiltersCount > 0" class="flex flex-wrap gap-2 mb-4">
                <span v-if="form.condition"
                      class="inline-flex items-center gap-1.5 bg-sezy-50 text-sezy-dark border border-sezy-100 text-xs font-semibold rounded-full px-3 py-1">
                    {{ form.condition === 'neuf' ? '🆕 Neuf' : '♻️ Fripe' }}
                    <button @click="form.condition = ''; applyFilters()" class="hover:text-red-500">×</button>
                </span>
                <span v-if="form.color"
                      class="inline-flex items-center gap-1.5 bg-sezy-50 text-sezy-dark border border-sezy-100 text-xs font-semibold rounded-full px-3 py-1">
                    <span class="w-3 h-3 rounded-full border border-white shadow" :style="{ background: colorHex(form.color) }"></span>
                    {{ form.color }}
                    <button @click="form.color = ''; applyFilters()" class="hover:text-red-500">×</button>
                </span>
                <span v-if="form.size"
                      class="inline-flex items-center gap-1.5 bg-sezy-50 text-sezy-dark border border-sezy-100 text-xs font-semibold rounded-full px-3 py-1">
                    Taille {{ form.size }}
                    <button @click="form.size = ''; applyFilters()" class="hover:text-red-500">×</button>
                </span>
                <span v-if="form.brand"
                      class="inline-flex items-center gap-1.5 bg-sezy-50 text-sezy-dark border border-sezy-100 text-xs font-semibold rounded-full px-3 py-1">
                    {{ brands?.find(b => b.slug === form.brand)?.name ?? form.brand }}
                    <button @click="form.brand = ''; applyFilters()" class="hover:text-red-500">×</button>
                </span>
                <button @click="resetFilters" class="text-xs text-red-500 hover:text-red-700 font-medium underline">Tout effacer</button>
            </div>

            <div class="flex gap-6">
                <!-- Sidebar desktop -->
                <aside class="hidden lg:block w-64 shrink-0 space-y-5">

                    <!-- Catégories -->
                    <div class="bg-white rounded-xl border border-gray-100 p-4">
                        <h3 class="font-bold text-sm text-gray-800 mb-3">Catégories</h3>
                        <div class="space-y-1">
                            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer hover:text-sezy-dark py-1 group">
                                <input type="radio" value="" v-model="form.category" @change="applyFilters" class="text-sezy focus:ring-sezy" />
                                <span class="group-hover:text-sezy-dark transition font-medium">Tout</span>
                            </label>
                            <label v-for="cat in categories" :key="cat.id" class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer hover:text-sezy-dark py-1 group">
                                <input type="radio" :value="cat.slug" v-model="form.category" @change="applyFilters" class="text-sezy focus:ring-sezy" />
                                <span class="group-hover:text-sezy-dark transition">{{ cat.name }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Condition Neuf/Fripe (pour vêtements) -->
                    <div class="bg-white rounded-xl border border-gray-100 p-4">
                        <h3 class="font-bold text-sm text-gray-800 mb-3">État</h3>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                @click="form.condition = form.condition === 'neuf' ? '' : 'neuf'; applyFilters()"
                                class="flex flex-col items-center gap-1 p-2.5 rounded-xl border-2 text-xs font-semibold transition"
                                :class="form.condition === 'neuf' ? 'border-sezy-dark bg-sezy-50 text-sezy-dark' : 'border-gray-200 text-gray-600 hover:border-sezy'">
                                <span class="text-lg">🆕</span>
                                Neuf
                            </button>
                            <button
                                @click="form.condition = form.condition === 'fripe' ? '' : 'fripe'; applyFilters()"
                                class="flex flex-col items-center gap-1 p-2.5 rounded-xl border-2 text-xs font-semibold transition"
                                :class="form.condition === 'fripe' ? 'border-sezy-dark bg-sezy-50 text-sezy-dark' : 'border-gray-200 text-gray-600 hover:border-sezy'">
                                <span class="text-lg">♻️</span>
                                Fripe
                            </button>
                        </div>
                    </div>

                    <!-- Couleurs -->
                    <div v-if="availableColors.length" class="bg-white rounded-xl border border-gray-100 p-4">
                        <h3 class="font-bold text-sm text-gray-800 mb-3">Couleur</h3>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="color in availableColors" :key="color"
                                    @click="form.color = form.color === color ? '' : color; applyFilters()"
                                    :title="color"
                                    class="w-7 h-7 rounded-full border-2 transition"
                                    :class="form.color === color ? 'border-sezy-dark scale-110 shadow-md' : 'border-gray-200 hover:border-gray-400'"
                                    :style="{ background: colorHex(color) }">
                            </button>
                        </div>
                        <p v-if="form.color" class="text-xs text-gray-500 mt-2">{{ form.color }} <button @click="form.color=''; applyFilters()" class="text-sezy-dark underline">Effacer</button></p>
                    </div>

                    <!-- Taille -->
                    <div class="bg-white rounded-xl border border-gray-100 p-4">
                        <h3 class="font-bold text-sm text-gray-800 mb-3">Taille</h3>
                        <div class="flex flex-wrap gap-1.5">
                            <button v-for="sz in (availableSizes.length ? availableSizes : standardSizes)" :key="sz"
                                    @click="form.size = form.size === sz ? '' : sz; applyFilters()"
                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold border transition"
                                    :class="form.size === sz ? 'border-sezy-dark bg-sezy-50 text-sezy-dark' : 'border-gray-200 text-gray-600 hover:border-sezy'">
                                {{ sz }}
                            </button>
                        </div>
                    </div>

                    <!-- Marque -->
                    <div v-if="brands?.length" class="bg-white rounded-xl border border-gray-100 p-4">
                        <h3 class="font-bold text-sm text-gray-800 mb-3">Marque</h3>
                        <select v-model="form.brand" @change="applyFilters" class="input-field text-sm">
                            <option value="">Toutes les marques</option>
                            <option v-for="b in brands" :key="b.id" :value="b.slug">{{ b.name }}</option>
                        </select>
                    </div>

                    <!-- Prix -->
                    <div class="bg-white rounded-xl border border-gray-100 p-4">
                        <h3 class="font-bold text-sm text-gray-800 mb-3">Prix (FCFA)</h3>
                        <div class="flex gap-2 mb-3">
                            <input v-model="form.min_price" type="number" placeholder="Min" class="input-field text-sm" @change="applyFilters" />
                            <input v-model="form.max_price" type="number" placeholder="Max" class="input-field text-sm" @change="applyFilters" />
                        </div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <button @click="form.min_price=''; form.max_price=5000; applyFilters()" class="text-xs border border-gray-200 rounded-lg py-1 hover:border-sezy text-gray-600 hover:text-sezy-dark transition">— 5 000</button>
                            <button @click="form.min_price=5000; form.max_price=20000; applyFilters()" class="text-xs border border-gray-200 rounded-lg py-1 hover:border-sezy text-gray-600 hover:text-sezy-dark transition">5k – 20k</button>
                            <button @click="form.min_price=20000; form.max_price=50000; applyFilters()" class="text-xs border border-gray-200 rounded-lg py-1 hover:border-sezy text-gray-600 hover:text-sezy-dark transition">20k – 50k</button>
                            <button @click="form.min_price=50000; form.max_price=''; applyFilters()" class="text-xs border border-gray-200 rounded-lg py-1 hover:border-sezy text-gray-600 hover:text-sezy-dark transition">50k +</button>
                        </div>
                    </div>

                    <!-- Stock -->
                    <div class="bg-white rounded-xl border border-gray-100 p-4">
                        <label class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" v-model="form.in_stock_only" @change="applyFilters" class="rounded text-sezy focus:ring-sezy w-4 h-4" />
                            <span class="font-medium">En stock uniquement</span>
                        </label>
                    </div>

                    <button v-if="activeFiltersCount > 0" @click="resetFilters" class="w-full text-sm text-red-500 hover:text-red-700 font-semibold py-2 border border-red-200 rounded-xl hover:bg-red-50 transition">
                        Réinitialiser tous les filtres
                    </button>
                </aside>

                <!-- Product grid -->
                <div class="flex-1 min-w-0">
                    <ProductGrid v-if="products.data.length" :products="products.data" />
                    <div v-else class="text-center py-20 bg-white rounded-2xl border border-gray-100">
                        <div class="text-5xl mb-4">🔍</div>
                        <p class="text-gray-600 font-semibold">Aucun produit trouvé</p>
                        <p class="text-gray-400 text-sm mt-1 mb-4">Essayez de modifier vos filtres ou votre recherche</p>
                        <button @click="resetFilters" class="btn-secondary text-sm">Réinitialiser les filtres</button>
                    </div>

                    <!-- Pagination -->
                    <div v-if="products.last_page > 1" class="flex justify-center gap-1 mt-8 flex-wrap">
                        <Link v-for="link in products.links" :key="link.label"
                              :href="link.url ?? '#'"
                              :class="['px-4 py-2 rounded-xl text-sm font-medium transition', link.active ? 'bg-sezy-dark text-white shadow-md' : 'bg-white text-gray-600 hover:bg-sezy-50 border border-gray-200', !link.url && 'opacity-40 pointer-events-none']"
                              v-html="link.label" preserve-scroll />
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile filters drawer -->
        <Transition name="slide-right">
            <div v-if="filtersOpen" class="fixed inset-0 z-50 lg:hidden">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="filtersOpen = false" />
                <div class="absolute right-0 top-0 h-full w-80 bg-gray-50 overflow-y-auto shadow-2xl">
                    <div class="sticky top-0 bg-white border-b border-gray-100 px-5 py-4 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900">
                            Filtres
                            <span v-if="activeFiltersCount > 0" class="ml-1.5 bg-sezy-dark text-white text-xs rounded-full px-2 py-0.5">{{ activeFiltersCount }}</span>
                        </h3>
                        <button @click="filtersOpen = false" class="p-1 rounded-lg hover:bg-gray-100"><XMarkIcon class="w-5 h-5" /></button>
                    </div>

                    <div class="p-4 space-y-4">
                        <!-- Condition -->
                        <div class="bg-white rounded-xl border border-gray-100 p-4">
                            <h4 class="font-bold text-sm text-gray-800 mb-3">État</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <button @click="form.condition = form.condition === 'neuf' ? '' : 'neuf'"
                                        class="flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 text-xs font-semibold transition"
                                        :class="form.condition === 'neuf' ? 'border-sezy-dark bg-sezy-50 text-sezy-dark' : 'border-gray-200 text-gray-600'">
                                    🆕 Neuf
                                </button>
                                <button @click="form.condition = form.condition === 'fripe' ? '' : 'fripe'"
                                        class="flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 text-xs font-semibold transition"
                                        :class="form.condition === 'fripe' ? 'border-sezy-dark bg-sezy-50 text-sezy-dark' : 'border-gray-200 text-gray-600'">
                                    ♻️ Fripe
                                </button>
                            </div>
                        </div>

                        <!-- Couleur -->
                        <div v-if="availableColors.length" class="bg-white rounded-xl border border-gray-100 p-4">
                            <h4 class="font-bold text-sm text-gray-800 mb-3">Couleur</h4>
                            <div class="flex flex-wrap gap-2">
                                <button v-for="color in availableColors" :key="color"
                                        @click="form.color = form.color === color ? '' : color"
                                        :title="color"
                                        class="w-8 h-8 rounded-full border-2 transition"
                                        :class="form.color === color ? 'border-sezy-dark scale-110 shadow' : 'border-gray-200'"
                                        :style="{ background: colorHex(color) }">
                                </button>
                            </div>
                        </div>

                        <!-- Taille -->
                        <div class="bg-white rounded-xl border border-gray-100 p-4">
                            <h4 class="font-bold text-sm text-gray-800 mb-3">Taille</h4>
                            <div class="flex flex-wrap gap-2">
                                <button v-for="sz in (availableSizes.length ? availableSizes : standardSizes)" :key="sz"
                                        @click="form.size = form.size === sz ? '' : sz"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition"
                                        :class="form.size === sz ? 'border-sezy-dark bg-sezy-50 text-sezy-dark' : 'border-gray-200 text-gray-600'">
                                    {{ sz }}
                                </button>
                            </div>
                        </div>

                        <!-- Catégories -->
                        <div class="bg-white rounded-xl border border-gray-100 p-4">
                            <h4 class="font-bold text-sm text-gray-800 mb-3">Catégories</h4>
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-sm cursor-pointer py-1">
                                    <input type="radio" value="" v-model="form.category" class="text-sezy" /> Tout
                                </label>
                                <label v-for="cat in categories" :key="cat.id" class="flex items-center gap-2 text-sm cursor-pointer py-1">
                                    <input type="radio" :value="cat.slug" v-model="form.category" class="text-sezy" /> {{ cat.name }}
                                </label>
                            </div>
                        </div>

                        <!-- Prix -->
                        <div class="bg-white rounded-xl border border-gray-100 p-4">
                            <h4 class="font-bold text-sm text-gray-800 mb-3">Prix (FCFA)</h4>
                            <div class="flex gap-2">
                                <input v-model="form.min_price" type="number" placeholder="Min" class="input-field text-sm" />
                                <input v-model="form.max_price" type="number" placeholder="Max" class="input-field text-sm" />
                            </div>
                        </div>

                        <label class="bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" v-model="form.in_stock_only" class="rounded text-sezy w-4 h-4" />
                            <span class="text-sm font-medium text-gray-700">En stock uniquement</span>
                        </label>
                    </div>

                    <div class="sticky bottom-0 p-4 bg-white border-t border-gray-100 flex gap-3">
                        <button v-if="activeFiltersCount > 0" @click="resetFilters" class="flex-1 btn-secondary text-sm">Effacer</button>
                        <button @click="applyFilters" class="flex-1 btn-primary text-sm">Appliquer les filtres</button>
                    </div>
                </div>
            </div>
        </Transition>
    </ShopLayout>
</template>

<style scoped>
.slide-right-enter-active { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.slide-right-leave-active { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
.slide-right-enter-from { opacity: 0; }
.slide-right-leave-to { opacity: 0; }
</style>
