<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ customer: Object });

const pointsForm = useForm({ points: 0, reason: '' });
const showPointsForm = ref(false);

function submitPoints() {
    pointsForm.post(route('admin.customers.loyalty-points', props.customer.id), {
        onSuccess: () => { pointsForm.reset(); showPointsForm.value = false; },
    });
}

function formatPrice(value) {
    return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
}
</script>

<template>
    <AdminLayout>
        <Link :href="route('admin.customers.index')" class="text-sm text-sezy-dark hover:underline mb-4 inline-block">← Retour aux clients</Link>

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="card p-5">
                    <h1 class="text-xl font-bold">{{ customer.first_name }} {{ customer.last_name }}</h1>
                    <p class="text-sm text-gray-500">{{ customer.email }} · {{ customer.phone }}</p>
                </div>

                <div class="card p-5">
                    <h2 class="font-bold mb-3">Historique des commandes</h2>
                    <div class="space-y-2">
                        <Link v-for="order in customer.orders" :key="order.id" :href="route('admin.orders.show', order.id)" class="flex justify-between text-sm py-2 border-b last:border-0">
                            <span>{{ order.order_number }}</span>
                            <span class="font-medium">{{ formatPrice(order.total) }}</span>
                        </Link>
                        <p v-if="!customer.orders?.length" class="text-sm text-gray-400">Aucune commande.</p>
                    </div>
                </div>

                <div class="card p-5">
                    <h2 class="font-bold mb-3">Adresses</h2>
                    <div v-for="addr in customer.addresses" :key="addr.id" class="text-sm py-2 border-b last:border-0">
                        {{ addr.address_line }}, {{ addr.city }}
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="card p-5 bg-sezy-50/50">
                    <p class="text-sm text-gray-500">Points de fidélité</p>
                    <p class="text-2xl font-bold text-sezy-dark">{{ customer.loyalty_points }} pts</p>
                    <span class="capitalize text-xs bg-sezy-dark text-white px-2 py-1 rounded-full">{{ customer.loyalty_tier }}</span>
                </div>

                <div class="card p-5">
                    <button v-if="!showPointsForm" class="btn-secondary w-full" @click="showPointsForm = true">Ajuster les points</button>
                    <form v-else @submit.prevent="submitPoints" class="space-y-3">
                        <input v-model="pointsForm.points" type="number" placeholder="Points (+/-)" class="input-field" />
                        <textarea v-model="pointsForm.reason" placeholder="Motif" class="input-field" rows="2"></textarea>
                        <button type="submit" class="btn-primary w-full" :disabled="pointsForm.processing">Confirmer</button>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
