<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    logs:    Object,
    actions: Array,
    filters: Object,
});

const actionFilter = ref(props.filters?.action ?? '');
const dateFilter   = ref(props.filters?.date ?? '');

function applyFilters() {
    router.get(route('admin.activity-log.index'), {
        action: actionFilter.value,
        date:   dateFilter.value,
    }, { preserveState: true, replace: true });
}

function actionBadgeColor(action) {
    if (action.startsWith('order'))    return 'bg-blue-100 text-blue-700';
    if (action.startsWith('product'))  return 'bg-emerald-100 text-emerald-700';
    if (action.startsWith('review'))   return 'bg-amber-100 text-amber-700';
    if (action.startsWith('flash'))    return 'bg-orange-100 text-orange-700';
    if (action.includes('delet'))      return 'bg-red-100 text-red-700';
    return 'bg-gray-100 text-gray-600';
}

function actionLabel(action) {
    const map = {
        'order.status_updated':  'Statut commande',
        'order.refunded':        'Remboursement',
        'product.created':       'Produit créé',
        'product.updated':       'Produit modifié',
        'product.deleted':       'Produit supprimé',
        'review.approved':       'Avis approuvé',
        'review.rejected':       'Avis rejeté',
        'review.deleted':        'Avis supprimé',
        'flash_sale.created':    'Vente flash créée',
        'flash_sale.updated':    'Vente flash modif.',
        'flash_sale.deleted':    'Vente flash suppr.',
    };
    return map[action] ?? action;
}
</script>

<template>
    <AdminLayout>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Journal d'activité</h1>
                <p class="text-sm text-gray-500 mt-0.5">Toutes les actions effectuées sur la plateforme</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="card p-4 mb-5 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Module</label>
                <select v-model="actionFilter" class="input-field text-sm">
                    <option value="">Tous les modules</option>
                    <option v-for="mod in actions" :key="mod" :value="mod">{{ mod.charAt(0).toUpperCase() + mod.slice(1) }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Date</label>
                <input v-model="dateFilter" type="date" class="input-field text-sm" />
            </div>
            <button @click="applyFilters" class="btn-primary text-sm">Filtrer</button>
            <button @click="actionFilter=''; dateFilter=''; applyFilters()" class="btn-secondary text-sm">Réinitialiser</button>
        </div>

        <!-- Table -->
        <div class="card overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Action</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Utilisateur</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Détails</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">IP</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="log in logs.data" :key="log.id" class="border-b border-gray-50 hover:bg-gray-50/50">
                        <td class="px-4 py-3">
                            <span :class="['text-xs font-semibold px-2.5 py-1 rounded-full', actionBadgeColor(log.action)]">
                                {{ actionLabel(log.action) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            <span v-if="log.user">{{ log.user.first_name }} {{ log.user.last_name }}</span>
                            <span v-else class="text-gray-400">Système</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 max-w-xs">
                            <div v-if="log.new_values && Object.keys(log.new_values).length" class="font-mono text-xs line-clamp-1">
                                {{ JSON.stringify(log.new_values) }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400 font-mono">{{ log.ip_address }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">
                            {{ new Date(log.created_at).toLocaleString('fr-FR') }}
                        </td>
                    </tr>
                    <tr v-if="!logs.data.length">
                        <td colspan="5" class="px-4 py-12 text-center text-gray-400 text-sm">Aucune activité enregistrée.</td>
                    </tr>
                </tbody>
            </table>
            <div v-if="logs.last_page > 1" class="flex justify-center gap-1 p-4 border-t border-gray-100">
                <a v-for="link in logs.links" :key="link.label"
                   :href="link.url ?? '#'"
                   @click.prevent="link.url && router.get(link.url)"
                   :class="['px-3 py-1.5 rounded-lg text-xs transition',
                            link.active ? 'bg-sezy-dark text-white' : 'text-gray-600 hover:bg-gray-100',
                            !link.url ? 'opacity-40 pointer-events-none' : '']"
                   v-html="link.label" />
            </div>
        </div>
    </AdminLayout>
</template>
