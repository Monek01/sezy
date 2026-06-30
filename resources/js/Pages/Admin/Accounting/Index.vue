<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Line, Bar, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS, Title, Tooltip, Legend, LineElement, PointElement,
    CategoryScale, LinearScale, ArcElement, BarElement,
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale, ArcElement, BarElement);

const props = defineProps({
    summary: Object,
    monthlyRevenue: Array,
    topProducts: Array,
    recentTransactions: Array,
    periodFilter: { type: String, default: 'month' },
});

const period = ref(props.periodFilter ?? 'month');

function applyPeriod() {
    router.get(route('admin.accounting.index'), { period: period.value }, { preserveState: true, replace: true });
}

function formatPrice(v) {
    return new Intl.NumberFormat('fr-FR').format(v ?? 0) + ' FCFA';
}

function formatPercent(v) {
    const num = Number(v ?? 0);
    const sign = num >= 0 ? '+' : '';
    return `${sign}${num.toFixed(1)}%`;
}

const revenueChartData = computed(() => ({
    labels: (props.monthlyRevenue ?? []).map(r => r.month),
    datasets: [
        {
            label: 'Chiffre d\'affaires',
            data: (props.monthlyRevenue ?? []).map(r => r.revenue),
            borderColor: '#004aad',
            backgroundColor: 'rgba(49,128,208,0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#004aad',
        },
        {
            label: 'Coût des ventes',
            data: (props.monthlyRevenue ?? []).map(r => r.cost),
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239,68,68,0.05)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#ef4444',
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: true, position: 'top' } },
    scales: { y: { beginAtZero: true } },
};

const topProductsChartData = computed(() => ({
    labels: (props.topProducts ?? []).slice(0, 5).map(p => p.title?.substring(0, 20) + '...'),
    datasets: [{
        label: 'Revenus',
        data: (props.topProducts ?? []).slice(0, 5).map(p => p.revenue),
        backgroundColor: ['#004aad', '#3180d0', '#5a9be0', '#a3c9f0', '#dceeff'],
        borderRadius: 6,
    }],
}));

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } },
};

const kpis = computed(() => [
    {
        label: 'Chiffre d\'affaires',
        value: formatPrice(props.summary?.revenue ?? 0),
        change: props.summary?.revenue_change ?? 0,
        icon: '💰',
        color: 'text-sezy-dark',
        bg: 'bg-sezy-50',
    },
    {
        label: 'Coût des ventes',
        value: formatPrice(props.summary?.cost ?? 0),
        change: props.summary?.cost_change ?? 0,
        icon: '🏷️',
        color: 'text-red-600',
        bg: 'bg-red-50',
    },
    {
        label: 'Marge brute',
        value: formatPrice(props.summary?.gross_profit ?? 0),
        change: props.summary?.gross_profit_change ?? 0,
        icon: '📈',
        color: 'text-emerald-600',
        bg: 'bg-emerald-50',
    },
    {
        label: 'Taux de marge',
        value: `${Number(props.summary?.margin_rate ?? 0).toFixed(1)}%`,
        change: props.summary?.margin_rate_change ?? 0,
        icon: '📊',
        color: 'text-amber-600',
        bg: 'bg-amber-50',
    },
    {
        label: 'Commandes payées',
        value: props.summary?.paid_orders ?? 0,
        change: props.summary?.orders_change ?? 0,
        icon: '🛍️',
        color: 'text-purple-600',
        bg: 'bg-purple-50',
    },
    {
        label: 'Panier moyen',
        value: formatPrice(props.summary?.avg_basket ?? 0),
        change: props.summary?.avg_basket_change ?? 0,
        icon: '🛒',
        color: 'text-blue-600',
        bg: 'bg-blue-50',
    },
    {
        label: 'Remboursements',
        value: formatPrice(props.summary?.refunds ?? 0),
        change: null,
        icon: '↩️',
        color: 'text-gray-600',
        bg: 'bg-gray-50',
    },
    {
        label: 'Revenue net',
        value: formatPrice((props.summary?.revenue ?? 0) - (props.summary?.refunds ?? 0)),
        change: null,
        icon: '✅',
        color: 'text-sezy-dark',
        bg: 'bg-sezy-50',
    },
]);

const txStatusMap = {
    paid: { label: 'Payé', cls: 'bg-emerald-100 text-emerald-700' },
    pending: { label: 'En attente', cls: 'bg-amber-100 text-amber-700' },
    refunded: { label: 'Remboursé', cls: 'bg-red-100 text-red-700' },
    failed: { label: 'Échoué', cls: 'bg-gray-100 text-gray-600' },
};

const paymentMethodMap = {
    mtn_momo: 'MTN MoMo',
    moov_money: 'Moov Money',
    wave: 'Wave',
    card: 'Carte bancaire',
    cash_on_delivery: 'Paiement à la livraison',
};
</script>

<template>
    <AdminLayout>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900">Comptabilité</h1>
                <p class="text-gray-500 text-sm mt-0.5">Suivi financier et rapports de ventes</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <select v-model="period" @change="applyPeriod" class="input-field text-sm w-auto">
                    <option value="today">Aujourd'hui</option>
                    <option value="week">Cette semaine</option>
                    <option value="month">Ce mois</option>
                    <option value="quarter">Ce trimestre</option>
                    <option value="year">Cette année</option>
                </select>
                <a :href="route('admin.accounting.export-pdf') + '?period=' + period"
                   target="_blank"
                   class="btn-secondary text-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    PDF
                </a>
                <a :href="route('admin.accounting.export-csv') + '?period=' + period"
                   class="btn-secondary text-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    CSV
                </a>
            </div>
        </div>

        <!-- KPI cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
            <div v-for="kpi in kpis" :key="kpi.label"
                 class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-gray-400 font-medium">{{ kpi.label }}</p>
                        <p class="text-lg font-extrabold mt-1" :class="kpi.color">{{ kpi.value }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" :class="kpi.bg">
                        {{ kpi.icon }}
                    </div>
                </div>
                <div v-if="kpi.change !== null" class="mt-2 flex items-center gap-1">
                    <span class="text-xs font-semibold" :class="kpi.change >= 0 ? 'text-emerald-600' : 'text-red-500'">
                        {{ formatPercent(kpi.change) }}
                    </span>
                    <span class="text-xs text-gray-400">vs période préc.</span>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 lg:col-span-2 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-800">Évolution CA & Coûts</h2>
                    <span class="text-xs text-gray-400">12 derniers mois</span>
                </div>
                <div class="h-64">
                    <Line :data="revenueChartData" :options="chartOptions" v-if="monthlyRevenue?.length" />
                    <div v-else class="h-full flex items-center justify-center text-gray-300 text-sm">Aucune donnée disponible</div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-800">Top 5 produits</h2>
                    <span class="text-xs text-gray-400">par revenu</span>
                </div>
                <div class="h-64">
                    <Bar :data="topProductsChartData" :options="barOptions" v-if="topProducts?.length" />
                    <div v-else class="h-full flex items-center justify-center text-gray-300 text-sm">Aucune donnée</div>
                </div>
            </div>
        </div>

        <!-- Profit summary card -->
        <div class="bg-gradient-to-r from-sezy-900 to-sezy-dark rounded-2xl p-6 text-white mb-8">
            <div class="grid grid-cols-3 gap-6 text-center">
                <div>
                    <p class="text-white/60 text-xs font-medium uppercase tracking-wide">Revenus bruts</p>
                    <p class="text-2xl font-extrabold mt-1">{{ formatPrice(summary?.revenue ?? 0) }}</p>
                </div>
                <div>
                    <p class="text-white/60 text-xs font-medium uppercase tracking-wide">Coût total</p>
                    <p class="text-2xl font-extrabold mt-1 text-red-300">- {{ formatPrice(summary?.cost ?? 0) }}</p>
                </div>
                <div class="border-l border-white/20 pl-6">
                    <p class="text-white/60 text-xs font-medium uppercase tracking-wide">Marge brute</p>
                    <p class="text-2xl font-extrabold mt-1 text-emerald-300">{{ formatPrice(summary?.gross_profit ?? 0) }}</p>
                    <p class="text-white/50 text-xs mt-1">{{ Number(summary?.margin_rate ?? 0).toFixed(1) }}% de marge</p>
                </div>
            </div>
        </div>

        <!-- Transactions table -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-bold text-gray-800">Transactions récentes</h2>
                <a href="/admin/commandes" class="text-sm text-sezy-dark hover:underline font-medium">Voir toutes les commandes →</a>
            </div>

            <div v-if="recentTransactions?.length" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-2.5 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">N° Commande</th>
                            <th class="text-left py-2.5 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Client</th>
                            <th class="text-left py-2.5 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Paiement</th>
                            <th class="text-right py-2.5 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Montant</th>
                            <th class="text-right py-2.5 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Coût</th>
                            <th class="text-right py-2.5 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Marge</th>
                            <th class="text-center py-2.5 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Statut</th>
                            <th class="text-right py-2.5 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="tx in recentTransactions" :key="tx.id"
                            class="hover:bg-gray-50/50 transition">
                            <td class="py-3 px-3">
                                <a :href="`/admin/commandes/${tx.id}`" class="font-mono font-semibold text-sezy-dark hover:underline">
                                    {{ tx.order_number }}
                                </a>
                            </td>
                            <td class="py-3 px-3 text-gray-700">{{ tx.customer_name }}</td>
                            <td class="py-3 px-3 text-gray-500 text-xs">{{ paymentMethodMap[tx.payment_method] ?? tx.payment_method }}</td>
                            <td class="py-3 px-3 text-right font-semibold text-gray-800">{{ formatPrice(tx.total) }}</td>
                            <td class="py-3 px-3 text-right text-red-500 text-xs">{{ formatPrice(tx.cost) }}</td>
                            <td class="py-3 px-3 text-right">
                                <span class="font-semibold text-emerald-600 text-xs">
                                    {{ formatPrice((tx.total ?? 0) - (tx.cost ?? 0)) }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex text-[11px] font-semibold px-2.5 py-0.5 rounded-full"
                                      :class="txStatusMap[tx.payment_status]?.cls ?? 'bg-gray-100 text-gray-500'">
                                    {{ txStatusMap[tx.payment_status]?.label ?? tx.payment_status }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right text-gray-400 text-xs">
                                {{ new Date(tx.created_at).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: '2-digit' }) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="text-center py-12 text-gray-400">
                <div class="text-4xl mb-3">💰</div>
                <p class="font-medium">Aucune transaction pour la période sélectionnée</p>
            </div>
        </div>
    </AdminLayout>
</template>
