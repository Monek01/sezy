<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { PencilIcon, PlusIcon, ArrowDownTrayIcon, ArrowUpTrayIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ products: Object, categories: Array, filters: Object });

const form = reactive({
    q: props.filters.q ?? '',
    status: props.filters.status ?? '',
    category_id: props.filters.category_id ?? '',
});

const importInput = ref(null);

function applyFilters() {
    router.get(route('admin.products.index'), { ...form }, { preserveState: true });
}

function importCsv(e) {
    const file = e.target.files[0];
    if (!file) return;
    const data = new FormData();
    data.append('file', file);
    router.post(route('admin.products.import'), data);
}

function formatPrice(value) {
    return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
}

const statusLabels = { draft: 'Brouillon', published: 'Publié', archived: 'Archivé' };
const statusColors = { draft: 'bg-gray-100 text-gray-600', published: 'bg-green-100 text-green-700', archived: 'bg-red-100 text-red-700' };
</script>

<template>
    <AdminLayout>
        <div class="flex flex-wrap gap-3 justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Produits</h1>
            <div class="flex gap-2 flex-wrap">
                <a :href="route('admin.products.export')" class="btn-secondary">
                    <ArrowDownTrayIcon class="w-5 h-5" /> Export CSV
                </a>
                <button class="btn-secondary" @click="importInput.click()">
                    <ArrowUpTrayIcon class="w-5 h-5" /> Import CSV
                </button>
                <input ref="importInput" type="file" accept=".csv" class="hidden" @change="importCsv" />
                <Link :href="route('admin.products.create')" class="btn-primary">
                    <PlusIcon class="w-5 h-5" /> Nouveau produit
                </Link>
            </div>
        </div>

        <div class="card p-4 mb-4 flex flex-wrap gap-3">
            <input v-model="form.q" @keyup.enter="applyFilters" type="text" placeholder="Rechercher..." class="input-field max-w-xs" />
            <select v-model="form.status" @change="applyFilters" class="input-field max-w-xs">
                <option value="">Tous statuts</option>
                <option value="draft">Brouillon</option>
                <option value="published">Publié</option>
                <option value="archived">Archivé</option>
            </select>
            <select v-model="form.category_id" @change="applyFilters" class="input-field max-w-xs">
                <option value="">Toutes catégories</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
        </div>

        <div class="card overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="text-left p-3">Produit</th>
                        <th class="text-left p-3">SKU</th>
                        <th class="text-left p-3">Catégorie</th>
                        <th class="text-left p-3">Prix</th>
                        <th class="text-left p-3">Stock</th>
                        <th class="text-left p-3">Statut</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="p in products.data" :key="p.id">
                        <td class="p-3 flex items-center gap-3">
                            <img :src="p.primary_image ? `/storage/${p.primary_image.path}` : '/images/placeholder-product.svg'"
                                 class="w-10 h-10 rounded object-cover"
                                 @error="$event.target.src='/images/placeholder-product.svg'" />
                            <span class="font-medium">{{ p.title }}</span>
                        </td>
                        <td class="p-3 text-gray-500">{{ p.sku }}</td>
                        <td class="p-3 text-gray-500">{{ p.category?.name }}</td>
                        <td class="p-3 font-medium">{{ formatPrice(p.price) }}</td>
                        <td class="p-3" :class="p.stock <= p.low_stock_threshold ? 'text-red-600 font-bold' : ''">{{ p.stock }}</td>
                        <td class="p-3"><span :class="['text-xs px-2 py-1 rounded-full', statusColors[p.status]]">{{ statusLabels[p.status] }}</span></td>
                        <td class="p-3 text-right">
                            <Link :href="route('admin.products.edit', p.id)" class="text-gray-400 hover:text-sezy-dark"><PencilIcon class="w-5 h-5" /></Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="products.last_page > 1" class="flex justify-center gap-1 mt-6">
            <Link v-for="link in products.links" :key="link.label" :href="link.url ?? '#'"
                  :class="['px-3 py-2 rounded-lg text-sm', link.active ? 'bg-sezy-dark text-white' : 'bg-white text-gray-600', !link.url && 'opacity-40 pointer-events-none']"
                  v-html="link.label" />
        </div>
    </AdminLayout>
</template>
