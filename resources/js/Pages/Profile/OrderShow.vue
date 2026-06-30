<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ order: Object });

const steps = [
    { key: 'pending',   label: 'Commandé',     icon: '🛒' },
    { key: 'confirmed', label: 'Confirmé',      icon: '✅' },
    { key: 'preparing', label: 'En préparation', icon: '📦' },
    { key: 'shipped',   label: 'Expédié',        icon: '🚚' },
    { key: 'delivered', label: 'Livré',           icon: '🎉' },
];

const cancelledSteps = ['cancelled', 'refunded'];
const isCancelled = computed(() => cancelledSteps.includes(props.order.status));

const currentStepIndex = computed(() => {
    if (isCancelled.value) return -1;
    return steps.findIndex(s => s.key === props.order.status);
});

const statusLabels = {
    pending: 'En attente', confirmed: 'Confirmée', preparing: 'En préparation',
    shipped: 'Expédiée', delivered: 'Livrée', cancelled: 'Annulée', refunded: 'Remboursée',
};

const paymentLabels = {
    pending: 'En attente', paid: 'Payé', failed: 'Échoué', refunded: 'Remboursé',
};

const paymentColors = {
    pending: 'bg-amber-100 text-amber-700',
    paid:    'bg-emerald-100 text-emerald-700',
    failed:  'bg-red-100 text-red-700',
    refunded: 'bg-blue-100 text-blue-700',
};

function fmt(v) {
    return new Intl.NumberFormat('fr-FR').format(v ?? 0) + ' FCFA';
}

const canDownloadInvoice = computed(() =>
    ['paid', 'refunded'].includes(props.order.payment_status)
);
</script>

<template>
    <ShopLayout>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
            <Link :href="route('orders.index')" class="text-sm text-sezy-dark hover:underline mb-5 inline-flex items-center gap-1">
                ← Mes commandes
            </Link>

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ order.order_number }}</h1>
                    <p class="text-sm text-gray-400 mt-0.5">
                        Passée le {{ new Date(order.created_at).toLocaleDateString('fr-FR', { dateStyle: 'long' }) }}
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span :class="['text-xs font-semibold px-3 py-1.5 rounded-full', paymentColors[order.payment_status] ?? 'bg-gray-100 text-gray-600']">
                        💳 {{ paymentLabels[order.payment_status] }}
                    </span>
                    <a v-if="canDownloadInvoice"
                       :href="route('orders.invoice', order.id)"
                       class="flex items-center gap-1.5 text-xs font-semibold text-sezy-dark border border-sezy-dark rounded-xl px-3 py-1.5 hover:bg-sezy-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Télécharger la facture
                    </a>
                </div>
            </div>

            <!-- Timeline visuelle -->
            <div class="card p-5 mb-5">
                <h2 class="font-bold text-sm text-gray-800 mb-5">Suivi de commande</h2>

                <!-- Cancelled/Refunded state -->
                <div v-if="isCancelled" class="flex items-center gap-3 p-4 bg-red-50 rounded-xl">
                    <span class="text-2xl">{{ order.status === 'refunded' ? '💸' : '❌' }}</span>
                    <div>
                        <p class="font-semibold text-red-700">Commande {{ statusLabels[order.status]?.toLowerCase() }}</p>
                        <p class="text-xs text-red-400 mt-0.5">Contactez-nous à agencesezy@gmail.com pour toute question.</p>
                    </div>
                </div>

                <!-- Progress steps -->
                <div v-else class="relative">
                    <div class="absolute top-5 left-5 right-5 h-0.5 bg-gray-200 z-0" />
                    <div class="flex justify-between relative z-10">
                        <div v-for="(step, i) in steps" :key="step.key" class="flex flex-col items-center gap-1.5 w-16">
                            <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-lg transition-all duration-500 border-2',
                                         i <= currentStepIndex
                                           ? 'bg-sezy-dark border-sezy-dark shadow-md scale-110'
                                           : 'bg-white border-gray-200']">
                                <span v-if="i < currentStepIndex" class="text-white text-base">✓</span>
                                <span v-else>{{ step.icon }}</span>
                            </div>
                            <span :class="['text-xs text-center leading-tight',
                                           i <= currentStepIndex ? 'font-semibold text-sezy-dark' : 'text-gray-400']">
                                {{ step.label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- History log -->
                <div v-if="order.status_histories?.length" class="mt-5 pt-4 border-t border-gray-100">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase mb-3">Historique</h3>
                    <div class="space-y-2.5">
                        <div v-for="h in order.status_histories" :key="h.id" class="flex gap-3 text-xs">
                            <div class="w-2 h-2 rounded-full bg-sezy-dark mt-1.5 shrink-0" />
                            <div>
                                <p class="font-medium text-gray-700">{{ statusLabels[h.status] ?? h.status }}</p>
                                <p v-if="h.note" class="text-gray-400 mt-0.5">{{ h.note }}</p>
                                <p class="text-gray-400">{{ new Date(h.created_at).toLocaleString('fr-FR') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Articles -->
            <div class="card p-5 mb-5">
                <h2 class="font-bold text-sm text-gray-800 mb-4">Articles commandés</h2>
                <div class="space-y-3">
                    <div v-for="item in order.items" :key="item.id" class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-xs font-bold shrink-0">
                                {{ item.quantity }}×
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ item.product_title }}</p>
                                <p v-if="item.variant_label" class="text-xs text-gray-400">{{ item.variant_label }}</p>
                            </div>
                        </div>
                        <span class="font-semibold text-gray-900">{{ fmt(item.line_total) }}</span>
                    </div>
                </div>

                <!-- Totals -->
                <div class="border-t border-gray-100 mt-4 pt-4 space-y-1.5 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Sous-total</span>
                        <span>{{ fmt(order.subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Livraison</span>
                        <span>{{ order.shipping_fee > 0 ? fmt(order.shipping_fee) : 'Gratuite' }}</span>
                    </div>
                    <div v-if="order.discount_amount > 0" class="flex justify-between text-emerald-600">
                        <span>Remise{{ order.coupon_code ? ` (${order.coupon_code})` : '' }}</span>
                        <span>-{{ fmt(order.discount_amount) }}</span>
                    </div>
                    <div v-if="order.loyalty_points_used > 0" class="flex justify-between text-sezy-dark">
                        <span>Points fidélité</span>
                        <span>-{{ fmt(order.loyalty_points_used) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-base pt-2 border-t border-gray-100">
                        <span>Total</span>
                        <span class="text-sezy-dark">{{ fmt(order.total) }}</span>
                    </div>
                </div>

                <div v-if="order.loyalty_points_earned > 0" class="mt-3 flex items-center gap-2 bg-sezy-50 rounded-xl px-3 py-2 text-sm text-sezy-dark">
                    ⭐ Vous avez gagné <strong>{{ order.loyalty_points_earned }} points SEZY</strong> avec cette commande.
                </div>
            </div>

            <!-- Livraison -->
            <div class="card p-5">
                <h2 class="font-bold text-sm text-gray-800 mb-3">Informations de livraison</h2>
                <div class="text-sm text-gray-600">
                    <p v-if="order.delivery_method === 'click_and_collect' && order.pickup_point">
                        📍 Retrait : <strong>{{ order.pickup_point.name }}</strong> — {{ order.pickup_point.address }}
                    </p>
                    <p v-else>
                        🚚 {{ order.shipping_address }}, {{ order.shipping_district }}, {{ order.shipping_city }}
                    </p>
                    <p v-if="order.customer_phone" class="mt-1 text-gray-400 text-xs">Tél : {{ order.customer_phone }}</p>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>
