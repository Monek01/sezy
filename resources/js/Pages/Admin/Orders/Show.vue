<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ order: Object, statuses: Object });

const statusForm = useForm({ status: props.order.status, note: '' });
const refundForm = useForm({ amount: props.order.total, reason: '' });
const showRefundForm = ref(false);

function updateStatus() {
    statusForm.patch(route('admin.orders.status', props.order.id), { onSuccess: () => (statusForm.note = '') });
}

function submitRefund() {
    refundForm.post(route('admin.orders.refund', props.order.id), { onSuccess: () => (showRefundForm.value = false) });
}

function formatPrice(value) {
    return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
}
</script>

<template>
    <AdminLayout>
        <Link :href="route('admin.orders.index')" class="text-sm text-sezy-dark hover:underline mb-4 inline-block">← Retour aux commandes</Link>

        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ order.order_number }}</h1>
            <div class="flex gap-2">
                <a :href="route('admin.orders.invoice', order.id)" class="btn-secondary">Facture PDF</a>
                <a :href="route('admin.orders.preparation-slip', order.id)" class="btn-secondary">Bon de préparation</a>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="card p-5">
                    <h2 class="font-bold mb-3">Articles</h2>
                    <div class="space-y-2">
                        <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm">
                            <span>{{ item.quantity }}× {{ item.product_title }} <span v-if="item.variant_label" class="text-gray-400">({{ item.variant_label }})</span></span>
                            <span class="font-medium">{{ formatPrice(item.line_total) }}</span>
                        </div>
                    </div>
                    <div class="border-t pt-3 mt-3 space-y-1 text-sm">
                        <div class="flex justify-between text-gray-500"><span>Sous-total</span><span>{{ formatPrice(order.subtotal) }}</span></div>
                        <div class="flex justify-between text-gray-500"><span>Livraison</span><span>{{ formatPrice(order.shipping_fee) }}</span></div>
                        <div class="flex justify-between font-bold text-base mt-1"><span>Total</span><span class="text-sezy-dark">{{ formatPrice(order.total) }}</span></div>
                    </div>
                </div>

                <div class="card p-5">
                    <h2 class="font-bold mb-3">Historique</h2>
                    <div class="space-y-3">
                        <div v-for="h in order.status_histories" :key="h.id" class="flex gap-3 text-sm">
                            <div class="w-2 h-2 rounded-full bg-sezy-dark mt-1.5 shrink-0" />
                            <div>
                                <p class="font-medium">{{ statuses[h.status] ?? h.status }} <span v-if="h.changed_by" class="text-xs text-gray-400">par {{ h.changed_by.first_name }}</span></p>
                                <p v-if="h.note" class="text-xs text-gray-500">{{ h.note }}</p>
                                <p class="text-xs text-gray-400">{{ new Date(h.created_at).toLocaleString('fr-FR') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="card p-5">
                    <h2 class="font-bold mb-3">Client</h2>
                    <p class="text-sm">{{ order.customer_name }}</p>
                    <p class="text-sm text-gray-500">{{ order.customer_phone }}</p>
                    <p class="text-sm text-gray-500">{{ order.customer_email }}</p>
                </div>

                <div class="card p-5">
                    <h2 class="font-bold mb-3">Livraison</h2>
                    <p class="text-sm text-gray-600" v-if="order.delivery_method === 'click_and_collect' && order.pickup_point">
                        Retrait : {{ order.pickup_point.name }}
                    </p>
                    <p class="text-sm text-gray-600" v-else>
                        {{ order.shipping_address }}, {{ order.shipping_district }}, {{ order.shipping_city }}
                    </p>
                </div>

                <form @submit.prevent="updateStatus" class="card p-5 space-y-3">
                    <h2 class="font-bold">Mettre à jour le statut</h2>
                    <select v-model="statusForm.status" class="input-field">
                        <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                    </select>
                    <textarea v-model="statusForm.note" placeholder="Note (optionnel)" class="input-field" rows="2"></textarea>
                    <button type="submit" class="btn-primary w-full" :disabled="statusForm.processing">Mettre à jour</button>
                </form>

                <div class="card p-5">
                    <button v-if="!showRefundForm" class="btn-secondary w-full" @click="showRefundForm = true">Effectuer un remboursement</button>
                    <form v-else @submit.prevent="submitRefund" class="space-y-3">
                        <h2 class="font-bold">Remboursement</h2>
                        <input v-model="refundForm.amount" type="number" :max="order.total" class="input-field" />
                        <textarea v-model="refundForm.reason" placeholder="Motif" class="input-field" rows="2"></textarea>
                        <button type="submit" class="btn-primary w-full" :disabled="refundForm.processing">Confirmer</button>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
