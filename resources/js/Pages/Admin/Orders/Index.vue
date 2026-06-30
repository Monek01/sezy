<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({ orders: Object, statuses: Object, filters: Object });

const form = reactive({
    status: props.filters.status ?? '',
    q: props.filters.q ?? '',
});

function applyFilters() {
    router.get(route('admin.orders.index'), { ...form }, { preserveState: true });
}

function formatPrice(value) {
    return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
}

const statusColors = {
    pending: 'bg-gray-100 text-gray-700', confirmed: 'bg-blue-100 text-blue-700',
    preparing: 'bg-amber-100 text-amber-700', shipped: 'bg-purple-100 text-purple-700',
    delivered: 'bg-green-100 text-green-700', cancelled: 'bg-red-100 text-red-700',
    refunded: 'bg-gray-100 text-gray-700',
};
</script>

<template>
    <AdminLayout>
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Commandes</h1>

        <div class="card p-4 mb-4 flex flex-wrap gap-3">
            <input v-model="form.q" @keyup.enter="applyFilters" type="text" placeholder="N° commande, nom, téléphone..." class="input-field max-w-sm" />
            <select v-model="form.status" @change="applyFilters" class="input-field max-w-xs">
                <option value="">Tous statuts</option>
                <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
            </select>
        </div>

        <div class="card overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="text-left p-3">Commande</th>
                        <th class="text-left p-3">Client</th>
                        <th class="text-left p-3">Date</th>
                        <th class="text-left p-3">Total</th>
                        <th class="text-left p-3">Paiement</th>
                        <th class="text-left p-3">Statut</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="order in orders.data" :key="order.id">
                        <td class="p-3 font-medium">{{ order.order_number }}</td>
                        <td class="p-3">{{ order.customer_name }}</td>
                        <td class="p-3 text-gray-500">{{ new Date(order.created_at).toLocaleDateString('fr-FR') }}</td>
                        <td class="p-3 font-medium">{{ formatPrice(order.total) }}</td>
                        <td class="p-3 text-gray-500 capitalize">{{ order.payment_status }}</td>
                        <td class="p-3"><span :class="['text-xs px-2 py-1 rounded-full', statusColors[order.status]]">{{ statuses[order.status] }}</span></td>
                        <td class="p-3 text-right">
                            <Link :href="route('admin.orders.show', order.id)" class="text-sezy-dark hover:underline">Détails</Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="orders.last_page > 1" class="flex justify-center gap-1 mt-6">
            <Link v-for="link in orders.links" :key="link.label" :href="link.url ?? '#'"
                  :class="['px-3 py-2 rounded-lg text-sm', link.active ? 'bg-sezy-dark text-white' : 'bg-white text-gray-600', !link.url && 'opacity-40 pointer-events-none']"
                  v-html="link.label" />
        </div>
    </AdminLayout>
</template>
