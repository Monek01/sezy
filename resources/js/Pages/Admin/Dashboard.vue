<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { Line, Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale, ArcElement, Filler } from 'chart.js';
import { computed, ref } from 'vue';

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale, ArcElement, Filler);

const props = defineProps({
    kpis:             Object,
    salesChart:       Array,
    salesByCategory:  Array,
    topClients:       Array,
    recentOrders:     Array,
    lowStockProducts: Array,
    pendingReviews:   Number,
    periodFilter:     String,
});

const period = ref(props.periodFilter ?? '14days');

const periodOptions = [
    { value: '7days',  label: '7 jours' },
    { value: '14days', label: '14 jours' },
    { value: '30days', label: '30 jours' },
    { value: 'month',  label: 'Ce mois' },
    { value: 'year',   label: 'Cette année' },
];

function applyPeriod(val) {
    period.value = val;
    router.get(route('admin.dashboard'), { period: val }, { preserveState: true, replace: true });
}

function fmt(v) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(v ?? 0)) + ' FCFA';
}
function fmtPct(v) {
    const n = v ?? 0;
    return (n >= 0 ? '+' : '') + n + '%';
}
function pctClass(v) {
    return (v ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-500';
}

const salesChartData = computed(() => ({
    labels: props.salesChart.map((d) => new Date(d.date).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' })),
    datasets: [{
        label: "CA (FCFA)",
        data: props.salesChart.map((d) => d.revenue),
        borderColor: '#004aad',
        backgroundColor: 'rgba(49,128,208,0.12)',
        fill: true,
        tension: 0.4,
        pointRadius: 3,
        pointBackgroundColor: '#004aad',
    }],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ' + new Intl.NumberFormat('fr-FR').format(ctx.raw) + ' FCFA' } } },
    scales: { y: { ticks: { callback: v => new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(v) } } },
};

const categoryChartData = computed(() => ({
    labels: props.salesByCategory.map((c) => c.name),
    datasets: [{
        data: props.salesByCategory.map((c) => c.total),
        backgroundColor: ['#004aad','#3180d0','#5a9be0','#a3c9f0','#dceeff','#1f68b8'],
    }],
}));
const doughnutOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { font: { size: 11 } } } } };

const statusLabels = {
    pending: 'En attente', confirmed: 'Confirmée', preparing: 'En préparation',
    shipped: 'Expédiée', delivered: 'Livrée', cancelled: 'Annulée', refunded: 'Remboursée',
};
const statusColors = {
    pending: 'bg-amber-100 text-amber-700', confirmed: 'bg-blue-100 text-blue-700',
    preparing: 'bg-purple-100 text-purple-700', shipped: 'bg-indigo-100 text-indigo-700',
    delivered: 'bg-emerald-100 text-emerald-700', cancelled: 'bg-red-100 text-red-700',
    refunded: 'bg-gray-100 text-gray-600',
};
</script>

<template>
    <AdminLayout>
        <!-- Header + period filter -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Tableau de bord</h1>
                <p class="text-sm text-gray-500 mt-0.5">Vue d'ensemble de votre activité SEZY</p>
            </div>
            <div class="flex gap-1.5 flex-wrap">
                <button v-for="opt in periodOptions" :key="opt.value"
                        @click="applyPeriod(opt.value)"
                        :class="['px-3 py-1.5 rounded-lg text-xs font-medium transition border',
                                 period === opt.value ? 'bg-sezy-dark text-white border-sezy-dark' : 'bg-white text-gray-600 border-gray-200 hover:border-sezy-dark hover:text-sezy-dark']">
                    {{ opt.label }}
                </button>
            </div>
        </div>

        <!-- Alert: pending reviews -->
        <div v-if="pendingReviews > 0" class="mb-5 flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm">
            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
            <span class="text-amber-700 font-medium">{{ pendingReviews }} avis client(s) en attente de modération.</span>
            <Link :href="route('admin.reviews.index', { status: 'pending' })" class="ml-auto text-amber-700 hover:underline font-semibold text-xs">Modérer →</Link>
        </div>

        <!-- KPI cards — 4 principals -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
            <div class="card p-4">
                <p class="text-xs text-gray-400 mb-1">CA période</p>
                <p class="text-xl font-bold text-sezy-dark">{{ fmt(kpis.revenue) }}</p>
                <p :class="['text-xs font-medium mt-1', pctClass(kpis.revenue_change)]">{{ fmtPct(kpis.revenue_change) }} vs période préc.</p>
            </div>
            <div class="card p-4">
                <p class="text-xs text-gray-400 mb-1">Commandes</p>
                <p class="text-xl font-bold">{{ kpis.orders }}</p>
                <p :class="['text-xs font-medium mt-1', pctClass(kpis.orders_change)]">{{ fmtPct(kpis.orders_change) }} vs période préc.</p>
            </div>
            <div class="card p-4">
                <p class="text-xs text-gray-400 mb-1">Nouveaux clients</p>
                <p class="text-xl font-bold">{{ kpis.customers }}</p>
                <p :class="['text-xs font-medium mt-1', pctClass(kpis.customers_change)]">{{ fmtPct(kpis.customers_change) }} vs période préc.</p>
            </div>
            <div class="card p-4 border-2" :class="kpis.low_stock_count > 0 ? 'border-red-200' : 'border-gray-100'">
                <p class="text-xs mb-1" :class="kpis.low_stock_count > 0 ? 'text-red-400' : 'text-gray-400'">Stocks critiques</p>
                <p class="text-xl font-bold" :class="kpis.low_stock_count > 0 ? 'text-red-600' : 'text-gray-900'">{{ kpis.low_stock_count }}</p>
                <p class="text-xs text-gray-400 mt-1">produit(s) sous seuil</p>
            </div>
        </div>

        <!-- Secondary KPIs -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
            <div class="bg-white rounded-xl border border-gray-100 p-3">
                <p class="text-xs text-gray-400">CA aujourd'hui</p>
                <p class="font-bold text-sezy-dark mt-0.5">{{ fmt(kpis.revenue_today) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-3">
                <p class="text-xs text-gray-400">CA ce mois</p>
                <p class="font-bold text-sezy-dark mt-0.5">{{ fmt(kpis.revenue_month) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-3">
                <p class="text-xs text-gray-400">Commandes en attente</p>
                <p class="font-bold text-amber-600 mt-0.5">{{ kpis.orders_pending }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-3">
                <p class="text-xs text-gray-400">Panier moyen</p>
                <p class="font-bold mt-0.5">{{ fmt(kpis.average_basket) }}</p>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid lg:grid-cols-3 gap-6 mb-8">
            <div class="card p-5 lg:col-span-2">
                <h2 class="font-bold mb-4 text-sm text-gray-700">Évolution du CA</h2>
                <div class="h-60"><Line :data="salesChartData" :options="chartOptions" /></div>
            </div>
            <div class="card p-5">
                <h2 class="font-bold mb-4 text-sm text-gray-700">Répartition par catégorie</h2>
                <div class="h-60"><Doughnut :data="categoryChartData" :options="doughnutOptions" /></div>
            </div>
        </div>

        <!-- Bottom grid: orders, clients, low stock -->
        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Recent orders -->
            <div class="card p-5">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="font-bold text-sm text-gray-700">Commandes récentes</h2>
                    <Link :href="route('admin.orders.index')" class="text-xs text-sezy-dark hover:underline">Voir tout →</Link>
                </div>
                <div class="space-y-2">
                    <Link v-for="order in recentOrders" :key="order.id" :href="route('admin.orders.show', order.id)"
                          class="flex justify-between items-center py-2 border-b last:border-0 text-xs hover:bg-gray-50 -mx-1 px-1 rounded-lg transition">
                        <span class="font-medium text-gray-700">{{ order.order_number }}</span>
                        <span :class="['px-2 py-0.5 rounded-full font-medium', statusColors[order.status] ?? 'bg-gray-100 text-gray-600']">{{ statusLabels[order.status] }}</span>
                        <span class="font-bold text-sezy-dark">{{ new Intl.NumberFormat('fr-FR').format(order.total) }}</span>
                    </Link>
                    <p v-if="!recentOrders.length" class="text-xs text-gray-400 text-center py-4">Aucune commande.</p>
                </div>
            </div>

            <!-- Top clients -->
            <div class="card p-5">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="font-bold text-sm text-gray-700">Top clients</h2>
                    <Link :href="route('admin.customers.index')" class="text-xs text-sezy-dark hover:underline">Voir tout →</Link>
                </div>
                <div class="space-y-2">
                    <div v-for="(client, i) in topClients" :key="i" class="flex items-center gap-2.5 py-1.5 border-b last:border-0 text-xs">
                        <span class="w-5 h-5 rounded-full bg-sezy-100 text-sezy-dark text-xs font-bold flex items-center justify-center shrink-0">{{ i + 1 }}</span>
                        <span class="truncate flex-1 font-medium text-gray-700">{{ client.name }}</span>
                        <span class="text-gray-400 shrink-0">{{ client.orders }}×</span>
                        <span class="font-bold text-sezy-dark shrink-0">{{ new Intl.NumberFormat('fr-FR').format(client.total_spent) }}</span>
                    </div>
                    <p v-if="!topClients.length" class="text-xs text-gray-400 text-center py-4">Aucun client sur cette période.</p>
                </div>
            </div>

            <!-- Low stock -->
            <div class="card p-5">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="font-bold text-sm text-gray-700">Stocks critiques</h2>
                    <Link :href="route('admin.products.index')" class="text-xs text-sezy-dark hover:underline">Gérer →</Link>
                </div>
                <div class="space-y-2">
                    <div v-for="p in lowStockProducts" :key="p.id" class="flex justify-between items-center py-1.5 border-b last:border-0 text-xs">
                        <span class="truncate flex-1 text-gray-700">{{ p.title }}</span>
                        <span class="ml-2 font-bold px-2 py-0.5 rounded-full shrink-0"
                              :class="p.stock === 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'">
                            {{ p.stock === 0 ? 'Rupture' : p.stock + ' restant(s)' }}
                        </span>
                    </div>
                    <p v-if="!lowStockProducts.length" class="text-xs text-gray-400 text-center py-4">Tous les stocks sont OK.</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
