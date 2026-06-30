<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ProductGrid from '@/Components/ProductGrid.vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { StarIcon, HeartIcon, ShoppingBagIcon } from '@heroicons/vue/24/solid';
import { HeartIcon as HeartOutline, ShareIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    product: Object,
    relatedProducts: Array,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const activeImageIndex = ref(0);
const quantity = ref(1);
const selectedValues = ref({});
const addingToCart = ref(false);
const wishlistActive = ref(props.product.is_in_wishlist ?? false);

const images = computed(() => props.product.images?.length ? props.product.images : [{ path: null }]);

// Group variants by attribute (Couleur, Taille, État, etc.)
const attributeGroups = computed(() => {
    const groups = {};
    for (const variant of props.product.variants ?? []) {
        for (const av of variant.attribute_values ?? []) {
            const attr = av.attribute;
            if (!groups[attr.id]) groups[attr.id] = { id: attr.id, name: attr.name, type: attr.type ?? 'default', values: new Map() };
            groups[attr.id].values.set(av.id, av);
        }
    }
    return groups;
});

const selectedVariant = computed(() => {
    if (!props.product.variants?.length) return null;
    const selectedCount = Object.keys(selectedValues.value).length;
    const totalGroups = Object.keys(attributeGroups.value).length;
    if (selectedCount < totalGroups) return null;

    return props.product.variants.find((variant) => {
        const variantValueIds = new Set((variant.attribute_values ?? []).map(v => `${v.attribute_id}:${v.id}`));
        return Object.entries(selectedValues.value).every(([attrId, valId]) => variantValueIds.has(`${attrId}:${valId}`));
    }) ?? null;
});

const effectivePrice = computed(() => Number(selectedVariant.value?.price ?? props.product.price));
const effectiveStock = computed(() => selectedVariant.value ? selectedVariant.value.stock : props.product.stock);
const canAddToCart = computed(() => {
    if (props.product.variants?.length && !selectedVariant.value) return false;
    return effectiveStock.value > 0 || !props.product.track_stock;
});

// Check which values are available given current selections
function isValueAvailable(attrId, valId) {
    // Simple: always allow selection; only disable if no variant exists at all
    return props.product.variants?.some(v => {
        const ids = (v.attribute_values ?? []).map(av => ({ attrId: av.attribute_id, valId: av.id }));
        const thisAttr = ids.find(x => x.attrId == attrId && x.valId == valId);
        if (!thisAttr) return false;
        // Check other selected values also match
        return Object.entries(selectedValues.value).every(([aId, vId]) => {
            if (aId == attrId) return true;
            return ids.some(x => x.attrId == aId && x.valId == vId);
        });
    }) ?? true;
}

function selectAttributeValue(attrId, valId) {
    if (selectedValues.value[attrId] === valId) {
        const newVals = { ...selectedValues.value };
        delete newVals[attrId];
        selectedValues.value = newVals;
    } else {
        selectedValues.value = { ...selectedValues.value, [attrId]: valId };
    }
}

// Color detection
const colorKeywords = ['couleur', 'color'];
const sizeKeywords = ['taille', 'size', 'pointure'];
const conditionKeywords = ['état', 'condition', 'etat'];

function isColorGroup(group) { return colorKeywords.some(k => group.name.toLowerCase().includes(k)); }
function isSizeGroup(group) { return sizeKeywords.some(k => group.name.toLowerCase().includes(k)); }
function isConditionGroup(group) { return conditionKeywords.some(k => group.name.toLowerCase().includes(k)); }

const colorMap = {
    'blanc': '#ffffff', 'noir': '#000000', 'rouge': '#ef4444', 'bleu': '#3b82f6',
    'vert': '#22c55e', 'jaune': '#eab308', 'orange': '#f97316', 'rose': '#ec4899',
    'violet': '#a855f7', 'marron': '#92400e', 'gris': '#6b7280', 'beige': '#d6b896',
    'or': '#d4af37', 'argent': '#c0c0c0', 'bordeaux': '#7f1d1d', 'turquoise': '#06b6d4',
    'white': '#ffffff', 'black': '#000000', 'red': '#ef4444', 'blue': '#3b82f6',
    'green': '#22c55e', 'yellow': '#eab308',
};

function colorHex(name) {
    return colorMap[(name || '').toLowerCase()] || '#9ca3af';
}

function isLightColor(name) {
    const hex = colorHex(name);
    if (hex === '#ffffff') return true;
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return (r * 299 + g * 587 + b * 114) / 1000 > 180;
}

function formatPrice(value) {
    return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
}

function addToCart() {
    addingToCart.value = true;
    router.post(route('cart.store'), {
        product_id: props.product.id,
        product_variant_id: selectedVariant.value?.id ?? null,
        quantity: quantity.value,
    }, {
        preserveScroll: true,
        onFinish: () => setTimeout(() => { addingToCart.value = false; }, 2000),
    });
}

function toggleWishlist() {
    if (!user.value) { router.visit(route('login')); return; }
    wishlistActive.value = !wishlistActive.value;
    router.post(route('wishlist.toggle', props.product.id), {}, { preserveScroll: true });
}

function prevImage() { activeImageIndex.value = (activeImageIndex.value - 1 + images.value.length) % images.value.length; }
function nextImage() { activeImageIndex.value = (activeImageIndex.value + 1) % images.value.length; }

function shareProduct() {
    if (navigator.share) {
        navigator.share({ title: props.product.title, url: window.location.href });
    } else {
        navigator.clipboard?.writeText(window.location.href);
    }
}

const averageRating = computed(() => Number(props.product.average_rating ?? 0));

const conditionBadge = computed(() => {
    const c = props.product.condition;
    if (c === 'fripe') return { text: '♻️ Fripe / Seconde main', cls: 'bg-emerald-100 text-emerald-700 border-emerald-200' };
    if (c === 'neuf') return { text: '🆕 Neuf', cls: 'bg-blue-100 text-blue-700 border-blue-200' };
    return null;
});
</script>

<template>
    <ShopLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 text-xs text-gray-400 mb-5 flex-wrap">
                <Link :href="route('home')" class="hover:text-sezy-dark transition">Accueil</Link>
                <span>/</span>
                <Link :href="route('products.index', { category: product.category?.slug })" class="hover:text-sezy-dark transition">{{ product.category?.name }}</Link>
                <span>/</span>
                <span class="text-gray-600 truncate max-w-[200px]">{{ product.title }}</span>
            </nav>

            <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">
                <!-- Gallery -->
                <div class="space-y-3">
                    <!-- Main image -->
                    <div class="relative aspect-square bg-gray-50 rounded-2xl overflow-hidden group">
                        <img
                            v-if="images[activeImageIndex]?.path"
                            :src="`/storage/${images[activeImageIndex].path}`"
                            :alt="product.title"
                            class="w-full h-full object-cover"
                            @error="$event.target.src='/images/placeholder-product.svg'"
                        />
                        <div v-else class="w-full h-full flex flex-col items-center justify-center gap-3 text-gray-300">
                            <img src="/images/placeholder-product.svg" class="w-24 h-24 opacity-40" alt="placeholder" />
                            <span class="text-sm">Aucune image</span>
                        </div>

                        <!-- Nav arrows (multiple images) -->
                        <template v-if="images.length > 1">
                            <button @click="prevImage"
                                    class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/90 backdrop-blur-sm rounded-full p-2 shadow-md opacity-0 group-hover:opacity-100 transition hover:bg-white">
                                <ChevronLeftIcon class="w-5 h-5 text-gray-700" />
                            </button>
                            <button @click="nextImage"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/90 backdrop-blur-sm rounded-full p-2 shadow-md opacity-0 group-hover:opacity-100 transition hover:bg-white">
                                <ChevronRightIcon class="w-5 h-5 text-gray-700" />
                            </button>
                            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5">
                                <button v-for="(_, i) in images" :key="i" @click="activeImageIndex = i"
                                        class="w-2 h-2 rounded-full transition"
                                        :class="i === activeImageIndex ? 'bg-sezy-dark scale-125' : 'bg-white/70'" />
                            </div>
                        </template>

                        <!-- Discount badge -->
                        <span v-if="product.discount_percent > 0" class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">
                            -{{ product.discount_percent }}%
                        </span>
                    </div>

                    <!-- Thumbnails -->
                    <div v-if="images.length > 1" class="flex gap-2 overflow-x-auto pb-1">
                        <button v-for="(img, i) in images" :key="i" @click="activeImageIndex = i"
                                class="w-16 h-16 rounded-xl overflow-hidden border-2 shrink-0 transition"
                                :class="i === activeImageIndex ? 'border-sezy-dark shadow-md' : 'border-gray-200 hover:border-gray-300'">
                            <img :src="`/storage/${img.path}`" class="w-full h-full object-cover"
                                 @error="$event.target.src='/images/placeholder-product.svg'" />
                        </button>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="flex flex-col gap-4">
                    <!-- Brand + Category -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400 uppercase tracking-wider font-medium">{{ product.brand?.name ?? product.category?.name }}</span>
                        <span v-if="conditionBadge" class="text-xs font-semibold px-2.5 py-0.5 rounded-full border" :class="conditionBadge.cls">
                            {{ conditionBadge.text }}
                        </span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 leading-tight">{{ product.title }}</h1>

                    <!-- Rating -->
                    <div class="flex items-center gap-2">
                        <div class="flex">
                            <StarIcon v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= Math.round(averageRating) ? 'text-amber-400' : 'text-gray-200'" />
                        </div>
                        <span class="text-sm text-gray-500">{{ averageRating.toFixed(1) }} <span class="text-gray-300">·</span> {{ product.reviews_count }} avis</span>
                    </div>

                    <!-- Price -->
                    <div class="flex items-baseline gap-3 bg-sezy-50 rounded-2xl px-4 py-3">
                        <span class="text-3xl font-extrabold text-sezy-dark">{{ formatPrice(effectivePrice) }}</span>
                        <span v-if="product.has_discount" class="text-lg text-gray-400 line-through">{{ formatPrice(product.compare_at_price) }}</span>
                        <span v-if="product.discount_percent > 0" class="bg-red-100 text-red-600 text-xs font-bold px-2 py-0.5 rounded-full">
                            économisez {{ formatPrice(Number(product.compare_at_price) - effectivePrice) }}
                        </span>
                    </div>

                    <!-- Short description -->
                    <p v-if="product.short_description" class="text-gray-600 text-sm leading-relaxed">{{ product.short_description }}</p>

                    <!-- Variant selectors -->
                    <div v-for="(group, attrId) in attributeGroups" :key="attrId" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-bold text-gray-800">{{ group.name }}</p>
                            <span v-if="selectedValues[attrId]" class="text-xs text-sezy-dark font-medium">
                                {{ [...group.values.values()].find(v => v.id == selectedValues[attrId])?.value }}
                            </span>
                        </div>

                        <!-- Color swatches -->
                        <div v-if="isColorGroup(group)" class="flex flex-wrap gap-2">
                            <button v-for="[valId, val] in group.values" :key="valId"
                                    @click="selectAttributeValue(attrId, valId)"
                                    :title="val.value"
                                    :disabled="!isValueAvailable(attrId, valId)"
                                    class="relative w-9 h-9 rounded-full border-3 transition focus:outline-none"
                                    :class="[
                                        selectedValues[attrId] == valId ? 'ring-2 ring-sezy-dark ring-offset-2 scale-110 shadow-md' : 'hover:scale-105',
                                        !isValueAvailable(attrId, valId) ? 'opacity-30 cursor-not-allowed' : 'cursor-pointer',
                                        isLightColor(val.value) ? 'border-gray-300' : 'border-transparent',
                                    ]"
                                    :style="{ background: colorHex(val.value) }">
                                <!-- Checkmark for selected -->
                                <span v-if="selectedValues[attrId] == valId"
                                      class="absolute inset-0 flex items-center justify-center text-xs font-bold"
                                      :class="isLightColor(val.value) ? 'text-gray-800' : 'text-white'">✓</span>
                            </button>
                        </div>

                        <!-- Condition (Neuf / Fripe) -->
                        <div v-else-if="isConditionGroup(group)" class="flex gap-2">
                            <button v-for="[valId, val] in group.values" :key="valId"
                                    @click="selectAttributeValue(attrId, valId)"
                                    :disabled="!isValueAvailable(attrId, valId)"
                                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl border-2 text-sm font-semibold transition"
                                    :class="[
                                        selectedValues[attrId] == valId ? 'border-sezy-dark bg-sezy-50 text-sezy-dark shadow-sm' : 'border-gray-200 text-gray-600 hover:border-sezy',
                                        !isValueAvailable(attrId, valId) ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer',
                                    ]">
                                <span>{{ val.value.toLowerCase().includes('fripe') || val.value.toLowerCase().includes('seconde') ? '♻️' : '🆕' }}</span>
                                {{ val.value }}
                            </button>
                        </div>

                        <!-- Size chips -->
                        <div v-else-if="isSizeGroup(group)" class="flex flex-wrap gap-2">
                            <button v-for="[valId, val] in group.values" :key="valId"
                                    @click="selectAttributeValue(attrId, valId)"
                                    :disabled="!isValueAvailable(attrId, valId)"
                                    class="min-w-[44px] px-3 py-2 rounded-xl border-2 text-sm font-bold transition text-center"
                                    :class="[
                                        selectedValues[attrId] == valId ? 'border-sezy-dark bg-sezy-dark text-white shadow-sm' : 'border-gray-200 text-gray-700 hover:border-sezy bg-white',
                                        !isValueAvailable(attrId, valId) ? 'opacity-40 cursor-not-allowed line-through' : 'cursor-pointer',
                                    ]">
                                {{ val.value }}
                            </button>
                        </div>

                        <!-- Generic selector -->
                        <div v-else class="flex flex-wrap gap-2">
                            <button v-for="[valId, val] in group.values" :key="valId"
                                    @click="selectAttributeValue(attrId, valId)"
                                    :disabled="!isValueAvailable(attrId, valId)"
                                    class="px-4 py-2 rounded-xl border-2 text-sm font-medium transition"
                                    :class="[
                                        selectedValues[attrId] == valId ? 'border-sezy-dark bg-sezy-50 text-sezy-dark shadow-sm' : 'border-gray-200 text-gray-600 hover:border-sezy',
                                        !isValueAvailable(attrId, valId) ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer',
                                    ]">
                                {{ val.value }}
                            </button>
                        </div>
                    </div>

                    <!-- Stock warnings -->
                    <div v-if="product.variants?.length && !selectedVariant" class="flex items-center gap-2 text-sm text-amber-600 bg-amber-50 rounded-xl px-3 py-2">
                        <span>⚠️</span> Veuillez sélectionner toutes les options ci-dessus.
                    </div>
                    <div v-else-if="effectiveStock <= 5 && effectiveStock > 0" class="flex items-center gap-2 text-sm text-amber-600 bg-amber-50 rounded-xl px-3 py-2">
                        <span>⏳</span> Plus que <strong>{{ effectiveStock }}</strong> en stock !
                    </div>
                    <div v-else-if="effectiveStock === 0 && product.track_stock" class="flex items-center gap-2 text-sm text-red-600 bg-red-50 rounded-xl px-3 py-2">
                        <span>❌</span> Ce produit est en rupture de stock.
                    </div>

                    <!-- Quantity + CTA -->
                    <div class="flex items-center gap-3 mt-2">
                        <div class="flex items-center border-2 border-gray-200 rounded-xl overflow-hidden">
                            <button @click="quantity = Math.max(1, quantity - 1)"
                                    class="px-3 py-2.5 text-gray-600 hover:bg-gray-50 font-bold text-lg transition">−</button>
                            <span class="px-4 font-bold text-gray-900 min-w-[40px] text-center">{{ quantity }}</span>
                            <button @click="quantity++"
                                    class="px-3 py-2.5 text-gray-600 hover:bg-gray-50 font-bold text-lg transition">+</button>
                        </div>
                        <button
                            class="flex-1 btn-primary text-sm py-3 gap-2"
                            :disabled="!canAddToCart || addingToCart"
                            @click="addToCart"
                        >
                            <ShoppingBagIcon class="w-4 h-4" />
                            <span v-if="addingToCart">Ajouté au panier ✓</span>
                            <span v-else>Ajouter au panier</span>
                        </button>
                        <button @click="toggleWishlist"
                                class="p-3 border-2 rounded-xl transition"
                                :class="wishlistActive ? 'border-red-300 bg-red-50 text-red-500' : 'border-gray-200 text-gray-400 hover:border-red-200 hover:text-red-400'">
                            <HeartIcon v-if="wishlistActive" class="w-5 h-5" />
                            <HeartOutline v-else class="w-5 h-5" />
                        </button>
                        <button @click="shareProduct"
                                class="p-3 border-2 border-gray-200 rounded-xl text-gray-400 hover:border-sezy hover:text-sezy-dark transition">
                            <ShareIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Guarantees -->
                    <div class="grid grid-cols-3 gap-2 mt-2">
                        <div class="flex flex-col items-center gap-1 bg-gray-50 rounded-xl p-2.5 text-center">
                            <span class="text-lg">🔒</span>
                            <span class="text-[10px] text-gray-500 font-medium leading-tight">Paiement sécurisé</span>
                        </div>
                        <div class="flex flex-col items-center gap-1 bg-gray-50 rounded-xl p-2.5 text-center">
                            <span class="text-lg">🚚</span>
                            <span class="text-[10px] text-gray-500 font-medium leading-tight">Livraison Bénin</span>
                        </div>
                        <div class="flex flex-col items-center gap-1 bg-gray-50 rounded-xl p-2.5 text-center">
                            <span class="text-lg">↩️</span>
                            <span class="text-[10px] text-gray-500 font-medium leading-tight">Retours faciles</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div v-if="product.description" class="mt-12 bg-white rounded-2xl border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Description du produit</h2>
                <div class="prose prose-sm max-w-none text-gray-600" v-html="product.description" />
            </div>

            <!-- Verified Reviews -->
            <div class="mt-8 bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Avis clients vérifiés</h2>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="flex">
                                <StarIcon v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= Math.round(averageRating) ? 'text-amber-400' : 'text-gray-200'" />
                            </div>
                            <span class="text-sm text-gray-500">{{ averageRating.toFixed(1) }}/5 · {{ product.reviews_count }} avis</span>
                        </div>
                    </div>
                </div>

                <div v-if="product.approved_reviews?.length" class="space-y-4">
                    <div v-for="review in product.approved_reviews" :key="review.id"
                         class="border border-gray-100 rounded-xl p-4 hover:border-sezy-100 transition">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-sezy-dark flex items-center justify-center text-white text-xs font-bold">
                                    {{ (review.user?.first_name ?? 'A').charAt(0) }}
                                </div>
                                <span class="font-semibold text-sm text-gray-800">{{ review.user?.first_name }}</span>
                                <span class="text-xs text-gray-400 bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full font-medium">✓ Achat vérifié</span>
                            </div>
                            <div class="flex">
                                <StarIcon v-for="i in 5" :key="i" class="w-3.5 h-3.5" :class="i <= review.rating ? 'text-amber-400' : 'text-gray-200'" />
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ review.comment }}</p>
                        <p v-if="review.created_at" class="text-xs text-gray-300 mt-2">
                            {{ new Date(review.created_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                        </p>
                    </div>
                </div>
                <div v-else class="text-center py-10">
                    <div class="text-4xl mb-3">⭐</div>
                    <p class="text-gray-500 font-medium">Aucun avis pour le moment</p>
                    <p class="text-gray-400 text-sm mt-1">Soyez le premier à partager votre expérience !</p>
                </div>
            </div>

            <!-- Related products -->
            <div v-if="relatedProducts?.length" class="mt-8">
                <h2 class="text-xl font-bold text-gray-900 mb-5">Produits similaires</h2>
                <ProductGrid :products="relatedProducts" />
            </div>
        </div>
    </ShopLayout>
</template>
