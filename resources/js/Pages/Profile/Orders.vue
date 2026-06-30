<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    orders:  Object,
    filters: Object,
});

const statusFilter = ref(props.filters?.status ?? '');

const statusLabels = {
    pending: 'En attente', confirmed: 'Confirmée', preparing: 'En préparation',
    shipped: 'Expédiée', delivered: 'Livrée', cancelled: 'Annulée', refunded: 'Remboursée',
};
const statusColors = {
    pending:  'bg-amber-100 text-amber-700',
    confirmed:'bg-blue-100 text-blue-700',
    preparing:'bg-purple-100 text-purple-700',
    shipped:  'bg-indigo-100 text-indigo-700',
    delivered:'bg-emerald-100 text-emerald-700',
    cancelled:'bg-red-100 text-red-700',
    refunded: 'bg-gray-100 text-gray-600',
};

const statusOptions = [
    { value: '', label: 'Toutes' },
    { value: 'pending',   label: '⏳ En attente' },
    { value: 'confirmed', label: '✅ Confirmées' },
    { value: 'preparing', label: '📦 En préparation' },
    { value: 'shipped',   label: '🚚 Expédiées' },
    { value: 'delivered', label: '🎉 Livrées' },
    { value: 'cancelled', label: '❌ Annulées' },
];

function applyFilter(val) {
    statusFilter.value = val;
    router.get(route('orders.index'), { status: val }, { preserveState: true, replace: true });
}

function fmt(v) {
    return new Intl.NumberFormat('fr-FR').format(v ?? 0) + ' FCFA';
}
</script>

<template>
    <ShopLayout>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
            <h1 class="text-2xl font-bold mb-1">Mes commandes</h1>
            <p class="text-sm text-gray-400 mb-6">Suivez et gérez toutes vos commandes SEZY</p>

            <!-- Profile nav -->
            <nav class="flex gap-4 mb-6 text-sm font-medium border-b overflow-x-auto">
                <Link :href="route('profile.edit')" class="pb-3 whitespace-nowrap text-gray-500 hover:text-sezy-dark">Profil</Link>
                <span class="pb-3 border-b-2 border-sezy-dark text-sezy-dark whitespace-nowrap">Mes commandes</span>
                <Link :href="route('profile.loyalty')" class="pb-3 whitespace-nowrap text-gray-500 hover:text-sezy-dark">Points fidélité</Link>
                <Link :href="route('wishlist.index')" class="pb-3 whitespace-nowrap text-gray-500 hover:text-sezy-dark">Liste de souhaits</Link>
                <Link :href="route('adresses.index')" class="pb-3 whitespace-nowrap text-gray-500 hover:text-sezy-dark">Adresses</Link>
            </nav>

            <!-- Status filter chips -->
            <div class="flex gap-2 flex-wrap mb-5">
                <button v-for="opt in statusOptions" :key="opt.value"
                        @click="applyFilter(opt.value)"
                        :class="['px-3 py-1.5 rounded-full text-xs font-medium transition border',
                                 statusFilter === opt.value
                                   ? 'bg-sezy-dark text-white border-sezy-dark'
                                   : 'bg-white text-gray-600 border-gray-200 hover:border-sezy-dark hover:text-sezy-dark']">
                    {{ opt.label }}
                </button>
            </div>

            <!-- Empty state -->
            <div v-if="!orders.data.length" class="text-center py-16">
                <p class="text-4xl mb-3">📭</p>
                <p class="font-semibold text-gray-700">Aucune commande trouvée</p>
                <p class="text-sm text-gray-400 mt-1">{{ statusFilter ? 'Essayez un autre filtre.' : 'Vous n\'avez pas encore passé de commande.' }}</p>
                <Link :href="route('products.index')" class="btn-primary mt-4 inline-flex">Découvrir la boutique</Link>
            </div>

            <!-- Orders list -->
            <div v-else class="space-y-3">
                <Link v-for="order in orders.data" :key="order.id"
                      :href="route('orders.show', order.id)"
                      class="card p-4 flex items-center justify-between hover:shadow-md hover:border-sezy-100 transition-all duration-200 block">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-gray-900">{{ order.order_number }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ new Date(order.created_at).toLocaleDateString('fr-FR', { dateStyle: 'medium' }) }}
                            · {{ order.items?.length ?? 0 }} article(s)
                        </p>
                    </div>
                    <div class="text-right ml-4 shrink-0">
                        <p class="font-bold text-sezy-dark text-sm">{{ fmt(order.total) }}</p>
                        <span :class="['text-xs font-medium px-2 py-0.5 rounded-full mt-1 inline-block', statusColors[order.status] ?? 'bg-gray-100 text-gray-600']">
                            {{ statusLabels[order.status] }}
                        </span>
                    </div>
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="orders.last_page > 1" class="flex justify-center gap-1 mt-6">
                <a v-for="link in orders.links" :key="link.label"
                   :href="link.url ?? '#'"
                   @click.prevent="link.url && router.get(link.url)"
                   :class="['px-3 py-1.5 rounded-lg text-xs transition',
                            link.active ? 'bg-sezy-dark text-white' : 'text-gray-600 hover:bg-gray-100',
                            !link.url ? 'opacity-40 pointer-events-none' : '']"
                   v-html="link.label" />
            </div>
        </div>
    </ShopLayout>
</template>
