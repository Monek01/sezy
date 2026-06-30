<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({ customers: Object, filters: Object });

const form = reactive({ q: props.filters.q ?? '' });

function search() {
    router.get(route('admin.customers.index'), { ...form }, { preserveState: true });
}

function toggleBlock(customer) {
    if (confirm(`${customer.is_blocked ? 'Débloquer' : 'Bloquer'} ce client ?`)) {
        router.post(route('admin.customers.toggle-block', customer.id));
    }
}
</script>

<template>
    <AdminLayout>
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Clients</h1>

        <div class="card p-4 mb-4">
            <input v-model="form.q" @keyup.enter="search" type="text" placeholder="Nom, email, téléphone..." class="input-field max-w-sm" />
        </div>

        <div class="card overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="text-left p-3">Client</th>
                        <th class="text-left p-3">Contact</th>
                        <th class="text-left p-3">Commandes</th>
                        <th class="text-left p-3">Points</th>
                        <th class="text-left p-3">Statut</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="c in customers.data" :key="c.id">
                        <td class="p-3 font-medium">{{ c.first_name }} {{ c.last_name }}</td>
                        <td class="p-3 text-gray-500">{{ c.email }}<br><span class="text-xs">{{ c.phone }}</span></td>
                        <td class="p-3">{{ c.orders_count }}</td>
                        <td class="p-3">{{ c.loyalty_points }} pts <span class="text-xs text-gray-400 capitalize">({{ c.loyalty_tier }})</span></td>
                        <td class="p-3">
                            <span :class="['text-xs px-2 py-1 rounded-full', c.is_blocked ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700']">
                                {{ c.is_blocked ? 'Bloqué' : 'Actif' }}
                            </span>
                        </td>
                        <td class="p-3 text-right space-x-3">
                            <Link :href="route('admin.customers.show', c.id)" class="text-sezy-dark hover:underline">Détails</Link>
                            <button @click="toggleBlock(c)" class="text-red-500 hover:underline">{{ c.is_blocked ? 'Débloquer' : 'Bloquer' }}</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="customers.last_page > 1" class="flex justify-center gap-1 mt-6">
            <Link v-for="link in customers.links" :key="link.label" :href="link.url ?? '#'"
                  :class="['px-3 py-2 rounded-lg text-sm', link.active ? 'bg-sezy-dark text-white' : 'bg-white text-gray-600', !link.url && 'opacity-40 pointer-events-none']"
                  v-html="link.label" />
        </div>
    </AdminLayout>
</template>
