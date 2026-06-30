<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ProductGrid from '@/Components/ProductGrid.vue';
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    featuredProducts: Array,
    newArrivals: Array,
    categories: Array,
    flashSale: Object,
});

const timeLeft = ref({ h: '00', m: '00', s: '00' });
let interval;

function updateCountdown() {
    if (!props.flashSale) return;
    const end = new Date(props.flashSale.ends_at).getTime();
    const diff = end - Date.now();
    if (diff <= 0) { clearInterval(interval); return; }
    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    timeLeft.value = {
        h: String(h).padStart(2, '0'),
        m: String(m).padStart(2, '0'),
        s: String(s).padStart(2, '0'),
    };
}

onMounted(() => {
    if (props.flashSale) { updateCountdown(); interval = setInterval(updateCountdown, 1000); }
});
onUnmounted(() => clearInterval(interval));

function categoryImage(cat) {
    return cat.image ? `/storage/${cat.image}` : '/images/placeholder-category.svg';
}

const categoryEmojis = {
    mode: '👗', vetements: '👕', fripe: '🧥', cosmetiques: '💄', beaute: '✨',
    alimentation: '🥗', nourriture: '🍔', chaussures: '👟', accessoires: '💍',
    electronique: '📱', maison: '🏠', sport: '⚽', enfants: '🧸',
    nutrition: '💊', sante: '🌿', parfum: '🌸',
};

function getCatEmoji(cat) {
    const key = Object.keys(categoryEmojis).find(k => (cat.slug || '').includes(k) || (cat.name || '').toLowerCase().includes(k));
    return key ? categoryEmojis[key] : '🛍️';
}

const services = [
    {
        icon: '✈️',
        title: 'Logistique & Transport',
        desc: 'Envoi de colis Paris ↔ Cotonou. Tarifs compétitifs, suivi en temps réel.',
        color: 'from-sezy-dark to-sezy',
        badge: 'Prochains vols disponibles',
        link: '/services/logistique',
        cta: 'Voir les vols',
    },
    {
        icon: '🛍️',
        title: 'Conciergerie Shopping',
        desc: 'On achète pour vous en France. Mode, électronique, cosmétiques et plus.',
        color: 'from-sezy-orange-dark to-sezy-orange',
        badge: 'Service premium',
        link: '/services/conciergerie',
        cta: 'Faire une demande',
    },
    {
        icon: '🎓',
        title: 'Études & Carrière',
        desc: 'Coaching académique, aide à l\'installation en France, recherche d\'emploi.',
        color: 'from-emerald-700 to-emerald-500',
        badge: 'Accompagnement personnalisé',
        link: '/services/etudes',
        cta: 'En savoir plus',
    },
];

// Next flight dates (from flyer - July 2025)
const nextFlights = [
    { route: 'Paris → Cotonou', price: '20€/kg', dates: ['02/07', '07/07', '12/07', '19/07', '24/07', '30/07'] },
    { route: 'Cotonou → Paris', price: '10 000 FR/kg', dates: ['06/07', '11/07', '16/07', '22/07', '26/07', '30/07'] },
];
</script>

<template>
    <ShopLayout>
        <!-- ═══════════════════════════════════════
             HERO — Inspired by flyer color palette
        ════════════════════════════════════════ -->
        <section class="relative overflow-hidden bg-sezy-dark text-white">
            <!-- Diagonal shape decoration -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-32 -right-32 w-[500px] h-[500px] bg-sezy/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 -left-20 w-80 h-80 bg-sezy-orange/10 rounded-full blur-3xl"></div>
                <!-- Diagonal white shape like the flyer -->
                <svg class="absolute bottom-0 right-0 w-1/2 h-full opacity-5" viewBox="0 0 400 400" fill="white">
                    <polygon points="400,0 400,400 100,400"/>
                </svg>
                <!-- Dots pattern -->
                <svg class="absolute inset-0 w-full h-full opacity-[0.04]" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="dots" x="0" y="0" width="30" height="30" patternUnits="userSpaceOnUse">
                            <circle cx="15" cy="15" r="1.5" fill="white"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#dots)"/>
                </svg>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-14 sm:py-20">
                <div class="grid lg:grid-cols-2 gap-10 items-center">
                    <!-- Left: Text -->
                    <div class="animate-fade-in-up">
                        <div class="inline-flex items-center gap-2 bg-sezy-orange/20 text-sezy-orange-light text-xs font-bold px-4 py-1.5 rounded-full mb-5 border border-sezy-orange/30 uppercase tracking-wide">
                            <span class="w-2 h-2 bg-sezy-orange rounded-full animate-pulse"></span>
                            Livraison disponible partout au Bénin
                        </div>

                        <h1 class="text-3xl sm:text-5xl font-extrabold leading-tight tracking-tight">
                            Votre passerelle de<br/>
                            <span class="text-sezy-orange">confiance</span> entre<br/>
                            <span class="text-white/90">l'Europe et l'Afrique</span>
                        </h1>

                        <p class="mt-5 text-white/70 max-w-lg text-base leading-relaxed">
                            Boutique en ligne, envois de colis Paris ↔ Cotonou, conciergerie shopping et accompagnement académique. Paiement via <strong class="text-white">MTN MoMo</strong>, <strong class="text-white">Moov</strong> ou <strong class="text-white">Wave</strong>.
                        </p>

                        <div class="flex flex-wrap gap-3 mt-8">
                            <Link :href="route('products.index')"
                                  class="inline-flex items-center gap-2 bg-sezy-orange hover:bg-sezy-orange-dark text-white font-bold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl transition-all active:scale-95">
                                🛍️ Découvrir la boutique
                            </Link>
                            <a href="/services/logistique"
                               class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm text-white border border-white/25 font-semibold px-6 py-3 rounded-xl hover:bg-white/20 transition-all">
                                ✈️ Nos services
                            </a>
                        </div>

                        <!-- Stats -->
                        <div class="flex flex-wrap gap-6 mt-10">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-lg">✈️</div>
                                <div>
                                    <p class="font-extrabold text-lg leading-none">6 vols/mois</p>
                                    <p class="text-white/50 text-xs">Paris ↔ Cotonou</p>
                                </div>
                            </div>
                            <div class="w-px bg-white/15 hidden sm:block"></div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-lg">🛍️</div>
                                <div>
                                    <p class="font-extrabold text-lg leading-none">500+ produits</p>
                                    <p class="text-white/50 text-xs">En stock</p>
                                </div>
                            </div>
                            <div class="w-px bg-white/15 hidden sm:block"></div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-lg">⭐</div>
                                <div>
                                    <p class="font-extrabold text-lg leading-none">1 200+ clients</p>
                                    <p class="text-white/50 text-xs">Satisfaits</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Plane illustration -->
                    <div class="hidden lg:flex items-center justify-center animate-float">
                        <svg viewBox="0 0 380 280" class="w-full max-w-sm drop-shadow-2xl" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Sky circles bg -->
                            <circle cx="190" cy="140" r="130" fill="white" opacity="0.04"/>
                            <circle cx="190" cy="140" r="100" fill="white" opacity="0.04"/>
                            <!-- Globe outline -->
                            <circle cx="190" cy="140" r="95" stroke="white" stroke-width="1" opacity="0.2"/>
                            <ellipse cx="190" cy="140" rx="50" ry="95" stroke="white" stroke-width="0.8" opacity="0.15"/>
                            <line x1="95" y1="140" x2="285" y2="140" stroke="white" stroke-width="0.8" opacity="0.15"/>
                            <ellipse cx="190" cy="140" rx="95" ry="30" stroke="white" stroke-width="0.8" opacity="0.15"/>
                            <!-- Plane body -->
                            <path d="M60 148 L220 118 L300 130 L310 138 L300 146 L220 158 Z" fill="white" opacity="0.95"/>
                            <!-- Nose -->
                            <path d="M300 138 Q330 138 322 144 L310 147 Z" fill="white" opacity="0.9"/>
                            <!-- Tail fins -->
                            <path d="M80 148 L70 118 L100 138 Z" fill="white" opacity="0.8"/>
                            <path d="M80 148 L70 178 L100 158 Z" fill="white" opacity="0.7"/>
                            <!-- Wings -->
                            <path d="M180 133 L160 80 L220 125 Z" fill="white" opacity="0.85"/>
                            <path d="M180 153 L160 200 L220 161 Z" fill="white" opacity="0.75"/>
                            <!-- Windows -->
                            <circle cx="240" cy="137" r="5" fill="#003f9e" opacity="0.4"/>
                            <circle cx="258" cy="135" r="5" fill="#003f9e" opacity="0.4"/>
                            <circle cx="275" cy="133" r="5" fill="#003f9e" opacity="0.35"/>
                            <!-- Orange accent on plane (logo) -->
                            <rect x="205" y="135" width="18" height="12" rx="3" fill="#F5821F" opacity="0.9"/>
                            <!-- Cloud left -->
                            <ellipse cx="50" cy="100" rx="30" ry="14" fill="white" opacity="0.12"/>
                            <ellipse cx="70" cy="95" rx="22" ry="12" fill="white" opacity="0.1"/>
                            <!-- Cloud right -->
                            <ellipse cx="340" cy="180" rx="25" ry="12" fill="white" opacity="0.1"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Wave bottom -->
            <div class="absolute bottom-0 left-0 right-0">
                <svg viewBox="0 0 1440 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                    <path d="M0 40L48 34C96 28 192 16 288 13.3C384 10.7 480 17.3 576 21.3C672 25.3 768 26.7 864 24C960 21.3 1056 14.7 1152 13.3C1248 12 1344 16 1392 18L1440 20V40H0Z" fill="rgb(249 250 251)"/>
                </svg>
            </div>
        </section>

        <!-- ═══════════════════════════════════════
             PROCHAINS VOLS (teaser strip)
        ════════════════════════════════════════ -->
        <section class="bg-gray-50 border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-5">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-sezy-dark rounded-xl flex items-center justify-center shrink-0">
                            <span class="text-xl">✈️</span>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900 text-sm">Prochains vols · Juillet 2025</p>
                            <p class="text-xs text-gray-500">Paris → Cotonou : <strong class="text-sezy-orange font-bold">20€/kg</strong> · Cotonou → Paris : <strong class="text-sezy-orange font-bold">10 000 FR/kg</strong></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <span v-for="d in ['02/07','07/07','12/07','19/07','24/07','30/07']" :key="d"
                              class="inline-flex items-center gap-1 bg-sezy-dark text-white text-xs font-semibold px-2.5 py-1 rounded-lg">
                            <svg class="w-3 h-3 text-sezy-orange" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            {{ d }}
                        </span>
                    </div>
                    <a href="/services/logistique" class="shrink-0 inline-flex items-center gap-1.5 bg-sezy-orange hover:bg-sezy-orange-dark text-white text-sm font-bold px-4 py-2 rounded-xl transition-all shadow-md">
                        Envoyer un colis →
                    </a>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════
             NOS SERVICES
        ════════════════════════════════════════ -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
            <div class="text-center mb-8">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900">Nos Services</h2>
                <p class="text-gray-500 text-sm mt-1.5 max-w-xl mx-auto">Plus qu'une boutique — votre passerelle complète entre la France et le Bénin</p>
            </div>
            <div class="grid sm:grid-cols-3 gap-5">
                <a v-for="s in services" :key="s.title" :href="s.link"
                   class="group relative overflow-hidden rounded-2xl p-6 text-white shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1"
                   :class="`bg-gradient-to-br ${s.color}`">
                    <!-- Decorative circle -->
                    <div class="absolute -top-6 -right-6 w-28 h-28 bg-white/10 rounded-full"></div>
                    <div class="absolute -bottom-4 -right-4 w-16 h-16 bg-white/5 rounded-full"></div>
                    <span class="text-4xl mb-4 block">{{ s.icon }}</span>
                    <div class="inline-flex items-center gap-1 bg-white/20 text-white text-[10px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-full mb-3">{{ s.badge }}</div>
                    <h3 class="text-lg font-extrabold leading-snug mb-2">{{ s.title }}</h3>
                    <p class="text-white/75 text-sm leading-relaxed mb-4">{{ s.desc }}</p>
                    <div class="inline-flex items-center gap-1.5 bg-white/20 hover:bg-white/30 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">
                        {{ s.cta }} <span class="text-base">→</span>
                    </div>
                </a>
            </div>
        </section>

        <!-- ═══════════════════════════════════════
             FLASH SALE
        ════════════════════════════════════════ -->
        <section v-if="flashSale" class="bg-gradient-to-r from-red-600 to-red-500 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-5">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-5">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/20 rounded-xl p-2.5"><span class="text-2xl">⚡</span></div>
                        <div>
                            <h2 class="text-xl font-extrabold">{{ flashSale.name }}</h2>
                            <p class="text-red-100 text-sm">Offres limitées dans le temps</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-red-100 text-sm font-medium">Se termine dans :</span>
                        <div class="flex gap-1.5">
                            <div v-for="(val, unit) in { H: timeLeft.h, MIN: timeLeft.m, SEC: timeLeft.s }" :key="unit"
                                 class="bg-white/20 rounded-lg px-2.5 py-1.5 text-center min-w-[48px]">
                                <div class="font-mono font-bold text-lg leading-none">{{ val }}</div>
                                <div class="text-[9px] text-red-100">{{ unit }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    <Link v-for="fp in flashSale.products" :key="fp.id"
                          :href="route('products.show', fp.product.slug)"
                          class="bg-white rounded-xl overflow-hidden hover:shadow-lg transition group">
                        <div class="aspect-square bg-gray-50 overflow-hidden">
                            <img :src="fp.product.primary_image ? `/storage/${fp.product.primary_image.path}` : '/images/placeholder-product.svg'"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy" />
                        </div>
                        <div class="p-3">
                            <p class="text-sm font-medium text-gray-800 line-clamp-1">{{ fp.product.title }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <p class="font-bold text-red-600">{{ new Intl.NumberFormat('fr-FR').format(fp.flash_price) }} FCFA</p>
                                <p class="text-xs text-gray-400 line-through">{{ new Intl.NumberFormat('fr-FR').format(fp.product.price) }}</p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════
             CATEGORIES
        ════════════════════════════════════════ -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900">Nos catégories</h2>
                    <p class="text-gray-500 text-sm mt-0.5">Tout ce dont vous avez besoin, en un seul endroit</p>
                </div>
                <Link :href="route('products.index')" class="text-sm text-sezy-dark font-semibold hover:underline hidden sm:block">Tout voir →</Link>
            </div>
            <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-3 sm:gap-4">
                <Link v-for="cat in categories" :key="cat.id"
                      :href="route('products.index', { category: cat.slug })"
                      class="flex flex-col items-center gap-2 group">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-sezy-50 to-sezy-100 overflow-hidden flex items-center justify-center group-hover:shadow-md group-hover:scale-105 transition duration-200 border border-sezy-100/50">
                        <img v-if="cat.image" :src="`/storage/${cat.image}`" :alt="cat.name" class="w-full h-full object-cover" loading="lazy"
                             @error="$event.target.src='/images/placeholder-category.svg'" />
                        <span v-else class="text-2xl sm:text-3xl">{{ getCatEmoji(cat) }}</span>
                    </div>
                    <span class="text-xs text-center font-semibold text-gray-700 line-clamp-2 group-hover:text-sezy-dark transition">{{ cat.name }}</span>
                </Link>
            </div>
        </section>

        <!-- ═══════════════════════════════════════
             FEATURED PRODUCTS
        ════════════════════════════════════════ -->
        <section v-if="featuredProducts?.length" class="bg-gradient-to-b from-white to-sezy-50/30 py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900">Nos coups de cœur ❤️</h2>
                        <p class="text-gray-500 text-sm mt-0.5">Sélectionnés avec soin pour vous</p>
                    </div>
                    <Link :href="route('products.index', { sort: 'featured' })" class="text-sm text-sezy-dark font-semibold hover:underline hidden sm:block">Voir tout →</Link>
                </div>
                <ProductGrid :products="featuredProducts" />
            </div>
        </section>

        <!-- ═══════════════════════════════════════
             NEW ARRIVALS
        ════════════════════════════════════════ -->
        <section v-if="newArrivals?.length" class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900">Nouveautés ✨</h2>
                    <p class="text-gray-500 text-sm mt-0.5">Les dernières arrivées sur SEZY</p>
                </div>
                <Link :href="route('products.index', { sort: 'newest' })" class="text-sm text-sezy-dark font-semibold hover:underline hidden sm:block">Voir tout →</Link>
            </div>
            <ProductGrid :products="newArrivals" />
        </section>

        <!-- ═══════════════════════════════════════
             WHY SEZY — Trust signals redesigned
        ════════════════════════════════════════ -->
        <section class="bg-sezy-dark text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="text-center mb-8">
                    <h2 class="text-xl sm:text-2xl font-bold">Pourquoi choisir SEZY ?</h2>
                    <p class="text-white/60 text-sm mt-1">Sécurité, rapidité et économies garanties</p>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-5">
                    <div v-for="item in [
                        { icon: '🔒', title: 'Paiement sécurisé', desc: 'SSL · Mobile Money vérifié' },
                        { icon: '✈️', title: 'Vols réguliers', desc: '6 rotations par mois' },
                        { icon: '⭐', title: 'Avis vérifiés', desc: 'Acheteurs confirmés' },
                        { icon: '💬', title: 'Support 7j/7', desc: 'WhatsApp · Email' },
                    ]" :key="item.title"
                         class="flex flex-col items-center text-center gap-3 group">
                        <div class="w-14 h-14 bg-white/10 group-hover:bg-sezy-orange/20 rounded-2xl flex items-center justify-center text-2xl transition-colors duration-200">
                            {{ item.icon }}
                        </div>
                        <div>
                            <p class="font-bold text-sm">{{ item.title }}</p>
                            <p class="text-white/50 text-xs mt-0.5">{{ item.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════
             CONTACTS / SOCIAL CTA
        ════════════════════════════════════════ -->
        <section class="bg-gradient-to-r from-sezy-orange-dark to-sezy-orange text-white py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold mb-1">Nous contacter</h2>
                        <p class="text-white/80 text-sm">Disponible sur WhatsApp · Agontikon, Cotonou</p>
                        <div class="flex flex-wrap gap-3 mt-3">
                            <a href="https://wa.me/22901420873" target="_blank"
                               class="inline-flex items-center gap-2 bg-white text-sezy-orange-dark font-bold text-sm px-4 py-2 rounded-xl hover:bg-white/90 transition shadow">
                                📱 +229 01 42 08 73 61
                            </a>
                            <a href="https://wa.me/22901663864" target="_blank"
                               class="inline-flex items-center gap-2 bg-white/20 text-white font-semibold text-sm px-4 py-2 rounded-xl hover:bg-white/30 transition border border-white/30">
                                📱 +229 01 66 38 64 17
                            </a>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <a href="https://instagram.com/SEZY BENIN" target="_blank"
                           class="w-11 h-11 bg-white/15 hover:bg-white/25 border border-white/20 rounded-xl flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="https://tiktok.com/@Agence Sezy" target="_blank"
                           class="w-11 h-11 bg-white/15 hover:bg-white/25 border border-white/20 rounded-xl flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                        </a>
                        <a href="mailto:agencesezy@gmail.com"
                           class="w-11 h-11 bg-white/15 hover:bg-white/25 border border-white/20 rounded-xl flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </ShopLayout>
</template>
