<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';
import { CheckCircleIcon, ClockIcon, XCircleIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    order: Object,
});

const paymentStatus = ref(props.order.payment_status);
const orderStatus = ref(props.order.status);
let pollInterval;

async function pollStatus() {
    try {
        const { data } = await axios.get(route('checkout.payment.status', props.order.id));
        paymentStatus.value = data.payment_status;
        orderStatus.value = data.order_status;

        if (data.payment_status === 'paid' || data.payment_status === 'failed') {
            clearInterval(pollInterval);
        }
    } catch (e) {
        // silencieux : on continue à réessayer
    }
}

onMounted(() => {
    if (paymentStatus.value === 'pending') {
        pollInterval = setInterval(pollStatus, 3000);
        pollStatus();
    }
});
onUnmounted(() => clearInterval(pollInterval));

function formatPrice(value) {
    return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
}

const statusConfig = computed(() => ({
    paid: { icon: CheckCircleIcon, color: 'text-green-600', bg: 'bg-green-50', label: 'Paiement confirmé !' },
    pending: { icon: ClockIcon, color: 'text-amber-600', bg: 'bg-amber-50', label: 'En attente de confirmation...' },
    failed: { icon: XCircleIcon, color: 'text-red-600', bg: 'bg-red-50', label: 'Paiement échoué' },
}[paymentStatus.value]));
</script>

<template>
    <ShopLayout>
        <div class="max-w-2xl mx-auto px-4 sm:px-6 py-10">
            <div class="flex items-center gap-2 mb-8 text-sm font-medium justify-center">
                <span class="text-gray-400">1. Livraison</span>
                <span class="text-gray-300">—</span>
                <span class="text-gray-400">2. Paiement</span>
                <span class="text-gray-300">—</span>
                <span class="text-sezy-dark">3. Confirmation</span>
            </div>

            <div :class="['card p-8 text-center', statusConfig.bg]">
                <component :is="statusConfig.icon" :class="['w-16 h-16 mx-auto mb-4', statusConfig.color]" />
                <h1 class="text-xl font-bold mb-1">{{ statusConfig.label }}</h1>
                <p v-if="paymentStatus === 'pending'" class="text-sm text-gray-500">
                    Validez la transaction sur votre téléphone si une demande Mobile Money vous a été envoyée.
                </p>
                <p class="text-sm text-gray-500 mt-2">Commande <strong>{{ order.order_number }}</strong></p>
            </div>

            <div class="card p-5 mt-6">
                <h2 class="font-bold mb-3">Détails de la commande</h2>
                <div class="space-y-2">
                    <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ item.quantity }}× {{ item.product_title }}</span>
                        <span class="font-medium">{{ formatPrice(item.line_total) }}</span>
                    </div>
                </div>
                <div class="border-t pt-3 mt-3 flex justify-between font-bold">
                    <span>Total</span>
                    <span class="text-sezy-dark">{{ formatPrice(order.total) }}</span>
                </div>
            </div>

            <div v-if="order.delivery_method === 'click_and_collect' && order.pickup_point" class="card p-5 mt-4">
                <h3 class="font-semibold text-sm mb-1">Point de retrait</h3>
                <p class="text-sm text-gray-600">{{ order.pickup_point.name }} — {{ order.pickup_point.address }}</p>
            </div>

            <div class="flex gap-3 mt-6">
                <Link :href="route('orders.index')" class="btn-secondary flex-1 text-center">Mes commandes</Link>
                <Link :href="route('home')" class="btn-primary flex-1 text-center">Continuer mes achats</Link>
            </div>
        </div>
    </ShopLayout>
</template>
