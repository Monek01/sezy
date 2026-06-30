<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    user:          Object,
    pointsHistory: Array,
    totalEarned:   Number,
    totalUsed:     Number,
});

const currentPoints = computed(() => (props.user?.loyalty_points ?? 0));

const tierConfig = {
    bronze:   { label: 'Bronze',   color: 'text-amber-700', bg: 'bg-amber-50',  border: 'border-amber-200', icon: '🥉', next: 'Argent', threshold: 500 },
    silver:   { label: 'Argent',   color: 'text-gray-600',  bg: 'bg-gray-50',   border: 'border-gray-200',  icon: '🥈', next: 'Or',     threshold: 2000 },
    gold:     { label: 'Or',       color: 'text-yellow-600',bg: 'bg-yellow-50', border: 'border-yellow-200',icon: '🥇', next: 'Premium', threshold: 5000 },
    platinum: { label: 'Premium',  color: 'text-sezy-dark', bg: 'bg-sezy-50',   border: 'border-sezy-100',  icon: '💎', next: null,     threshold: null },
};

const tier = computed(() => tierConfig[props.user?.loyalty_tier ?? 'bronze'] ?? tierConfig.bronze);

const progressPct = computed(() => {
    if (!tier.value.threshold) return 100;
    return Math.min(100, Math.round((currentPoints.value / tier.value.threshold) * 100));
});

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v ?? 0) + ' FCFA'; }
</script>

<template>
    <ShopLayout>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
            <h1 class="text-2xl font-bold mb-1">Points fidélité</h1>
            <p class="text-sm text-gray-400 mb-6">Votre programme de récompenses SEZY</p>

            <!-- Profile nav -->
            <nav class="flex gap-4 mb-6 text-sm font-medium border-b overflow-x-auto">
                <Link :href="route('profile.edit')" class="pb-3 whitespace-nowrap text-gray-500 hover:text-sezy-dark">Profil</Link>
                <Link :href="route('orders.index')" class="pb-3 whitespace-nowrap text-gray-500 hover:text-sezy-dark">Mes commandes</Link>
                <span class="pb-3 border-b-2 border-sezy-dark text-sezy-dark whitespace-nowrap">Points fidélité</span>
                <Link :href="route('wishlist.index')" class="pb-3 whitespace-nowrap text-gray-500 hover:text-sezy-dark">Liste de souhaits</Link>
                <Link :href="route('adresses.index')" class="pb-3 whitespace-nowrap text-gray-500 hover:text-sezy-dark">Adresses</Link>
            </nav>

            <!-- Points balance card -->
            <div :class="['card p-6 mb-6 border-2', tier.border]">
                <div class="flex items-start justify-between">
                    <div>
                        <div :class="['inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-bold mb-3', tier.bg, tier.color]">
                            {{ tier.icon }} Niveau {{ tier.label }}
                        </div>
                        <p class="text-4xl font-extrabold text-gray-900">{{ currentPoints.toLocaleString('fr-FR') }}</p>
                        <p class="text-sm text-gray-400 mt-1">points disponibles</p>
                    </div>
                    <div class="text-right text-sm text-gray-500">
                        <p>Total gagné : <strong class="text-emerald-600">{{ totalEarned.toLocaleString('fr-FR') }} pts</strong></p>
                        <p>Total utilisé : <strong class="text-gray-700">{{ totalUsed.toLocaleString('fr-FR') }} pts</strong></p>
                    </div>
                </div>

                <!-- Progress to next tier -->
                <div v-if="tier.next" class="mt-5">
                    <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                        <span>Progression vers {{ tier.next }}</span>
                        <span class="font-semibold">{{ currentPoints.toLocaleString('fr-FR') }} / {{ tier.threshold?.toLocaleString('fr-FR') }} pts</span>
                    </div>
                    <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-sezy to-sezy-dark rounded-full transition-all duration-700"
                             :style="{ width: progressPct + '%' }" />
                    </div>
                    <p class="text-xs text-gray-400 mt-1">
                        Encore {{ Math.max(0, tier.threshold - currentPoints).toLocaleString('fr-FR') }} points pour atteindre le niveau {{ tier.next }}
                    </p>
                </div>
                <div v-else class="mt-4 bg-sezy-50 rounded-xl px-4 py-3 text-sm text-sezy-dark font-medium">
                    🎉 Vous êtes au niveau maximum ! Profitez de tous vos avantages Premium.
                </div>
            </div>

            <!-- How it works -->
            <div class="card p-5 mb-6">
                <h2 class="font-bold text-sm text-gray-800 mb-4">Comment ça marche ?</h2>
                <div class="grid sm:grid-cols-3 gap-4">
                    <div class="text-center p-3 bg-gray-50 rounded-xl">
                        <p class="text-2xl mb-2">🛍️</p>
                        <p class="font-semibold text-sm">Achetez</p>
                        <p class="text-xs text-gray-500 mt-1">Gagnez des points à chaque commande payée</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-xl">
                        <p class="text-2xl mb-2">⭐</p>
                        <p class="font-semibold text-sm">Cumulez</p>
                        <p class="text-xs text-gray-500 mt-1">Plus vous achetez, plus vous montez de niveau</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-xl">
                        <p class="text-2xl mb-2">💸</p>
                        <p class="font-semibold text-sm">Profitez</p>
                        <p class="text-xs text-gray-500 mt-1">Utilisez vos points en réduction lors du paiement</p>
                    </div>
                </div>
            </div>

            <!-- Tiers table -->
            <div class="card p-5 mb-6">
                <h2 class="font-bold text-sm text-gray-800 mb-4">Niveaux de fidélité</h2>
                <div class="space-y-2">
                    <div v-for="([key, t]) in Object.entries(tierConfig)" :key="key"
                         :class="['flex items-center justify-between p-3 rounded-xl border',
                                  (user.loyalty_tier ?? 'bronze') === key ? `${t.border} ${t.bg}` : 'border-gray-100']">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">{{ t.icon }}</span>
                            <div>
                                <p :class="['font-semibold text-sm', t.color]">{{ t.label }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ t.threshold ? `Dès ${t.threshold.toLocaleString('fr-FR')} points` : 'Niveau ultime' }}
                                </p>
                            </div>
                        </div>
                        <span v-if="(user.loyalty_tier ?? 'bronze') === key"
                              class="text-xs font-semibold px-2.5 py-1 bg-sezy-dark text-white rounded-full">
                            Votre niveau
                        </span>
                    </div>
                </div>
            </div>

            <!-- History -->
            <div class="card p-5">
                <h2 class="font-bold text-sm text-gray-800 mb-4">Historique des points</h2>
                <div v-if="pointsHistory.length" class="space-y-2">
                    <div v-for="entry in pointsHistory" :key="entry.order_number"
                         class="flex items-center justify-between py-2.5 border-b border-gray-50 text-sm last:border-0">
                        <div>
                            <p class="font-medium text-gray-800">{{ entry.order_number }}</p>
                            <p class="text-xs text-gray-400">{{ new Date(entry.date).toLocaleDateString('fr-FR', { dateStyle: 'medium' }) }} · {{ fmt(entry.total) }}</p>
                        </div>
                        <div class="text-right">
                            <p v-if="entry.earned > 0" class="text-emerald-600 font-semibold text-xs">+{{ entry.earned }} pts</p>
                            <p v-if="entry.used > 0" class="text-orange-500 font-semibold text-xs">-{{ entry.used }} pts utilisés</p>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-gray-400">
                    <p class="text-3xl mb-2">📋</p>
                    <p class="text-sm">Aucune activité de points pour l'instant.</p>
                    <Link :href="route('products.index')" class="text-sm text-sezy-dark hover:underline mt-1 inline-block">Commencer à acheter →</Link>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>
