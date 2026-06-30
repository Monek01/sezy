<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    cart: Object,
    shipping: Object,
    subtotal: Number,
    shippingFee: Number,
    total: Number,
    paymentMethods: Array,
    loyaltyPoints: Number,
});

const form = useForm({
    payment_method: '',
    payer_phone: props.shipping?.phone ?? '',
    coupon_code: '',
    loyalty_points_to_use: 0,
});

const useLoyaltyPoints = ref(false);

const methodLogos = {
    mtn_momo: '🟡',
    moov_money: '🔵',
    wave: '🟣',
    card: '💳',
};

const finalTotal = computed(() => {
    const pointsUsed = useLoyaltyPoints.value ? Math.min(form.loyalty_points_to_use || 0, props.loyaltyPoints) : 0;
    return Math.max(0, props.total - pointsUsed);
});

function formatPrice(value) {
    return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
}

function submit() {
    if (!useLoyaltyPoints.value) form.loyalty_points_to_use = 0;
    form.post(route('checkout.payment.store'));
}
</script>

<template>
    <ShopLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
            <div class="flex items-center gap-2 mb-8 text-sm font-medium">
                <span class="text-gray-400">1. Livraison</span>
                <span class="text-gray-300">—</span>
                <span class="text-sezy-dark">2. Paiement</span>
                <span class="text-gray-300">—</span>
                <span class="text-gray-400">3. Confirmation</span>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <form @submit.prevent="submit" class="lg:col-span-2 space-y-6">
                    <div class="card p-5">
                        <h2 class="font-bold mb-4">Choisissez votre moyen de paiement</h2>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <label v-for="method in paymentMethods" :key="method.key"
                                   class="border rounded-lg p-4 cursor-pointer flex items-center gap-3"
                                   :class="form.payment_method === method.key ? 'border-sezy-dark bg-sezy-50' : 'border-gray-200'">
                                <input type="radio" :value="method.key" v-model="form.payment_method" class="text-sezy" />
                                <span class="text-xl">{{ methodLogos[method.key] }}</span>
                                <span class="text-sm font-medium">{{ method.label }}</span>
                            </label>
                        </div>
                        <p v-if="form.errors.payment_method" class="text-xs text-red-600 mt-2">{{ form.errors.payment_method }}</p>

                        <div v-if="form.payment_method && form.payment_method !== 'card'" class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Numéro de téléphone Mobile Money *</label>
                            <input v-model="form.payer_phone" type="tel" placeholder="+229 ..." class="input-field" required />
                            <p class="text-xs text-gray-400 mt-1">Vous recevrez une demande de validation sur ce numéro.</p>
                            <p v-if="form.errors.payer_phone" class="text-xs text-red-600 mt-1">{{ form.errors.payer_phone }}</p>
                        </div>
                    </div>

                    <div class="card p-5">
                        <h2 class="font-bold mb-3">Code promo</h2>
                        <input v-model="form.coupon_code" type="text" placeholder="Entrez un code" class="input-field" />
                    </div>

                    <div v-if="loyaltyPoints > 0" class="card p-5">
                        <label class="flex items-center gap-2 mb-2">
                            <input type="checkbox" v-model="useLoyaltyPoints" class="rounded text-sezy" />
                            <span class="font-bold">Utiliser mes points de fidélité ({{ loyaltyPoints }} disponibles)</span>
                        </label>
                        <input v-if="useLoyaltyPoints" v-model.number="form.loyalty_points_to_use" type="number" :max="loyaltyPoints" min="0" class="input-field" placeholder="Nombre de points à utiliser" />
                    </div>

                    <button type="submit" class="btn-primary w-full" :disabled="form.processing || !form.payment_method">
                        Payer {{ formatPrice(finalTotal) }}
                    </button>
                </form>

                <div class="card p-5 h-fit">
                    <h2 class="font-bold mb-4">Récapitulatif</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-600">Sous-total</span><span>{{ formatPrice(subtotal) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Livraison</span><span>{{ formatPrice(shippingFee) }}</span></div>
                        <div v-if="useLoyaltyPoints && form.loyalty_points_to_use" class="flex justify-between text-sezy-dark">
                            <span>Points utilisés</span><span>-{{ formatPrice(form.loyalty_points_to_use) }}</span>
                        </div>
                    </div>
                    <div class="border-t pt-3 mt-3 flex justify-between font-bold text-lg">
                        <span>Total</span>
                        <span class="text-sezy-dark">{{ formatPrice(finalTotal) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>
