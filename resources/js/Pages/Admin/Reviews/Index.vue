<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';

const props = defineProps({
    reviews: Object,
    counts:  Object,
    filters: Object,
});

const toast = useToast();
const statusFilter = ref(props.filters?.status ?? '');
const ratingFilter = ref(props.filters?.rating ?? '');
const search = ref(props.filters?.q ?? '');

function applyFilters() {
    router.get(route('admin.reviews.index'), {
        status: statusFilter.value,
        rating: ratingFilter.value,
        q: search.value,
    }, { preserveState: true, replace: true });
}

function approve(id) {
    router.patch(route('admin.reviews.approve', id), {}, {
        onSuccess: () => toast.success('Avis approuvé.'),
    });
}
function reject(id) {
    router.patch(route('admin.reviews.reject', id), {}, {
        onSuccess: () => toast.success('Avis rejeté.'),
    });
}
function destroy(id) {
    if (!confirm('Supprimer cet avis ?')) return;
    router.delete(route('admin.reviews.destroy', id), {
        onSuccess: () => toast.success('Avis supprimé.'),
    });
}

function stars(n) {
    return '★'.repeat(n) + '☆'.repeat(5 - n);
}

const statusLabels = { pending: 'En attente', approved: 'Approuvé', rejected: 'Rejeté' };
const statusColors = {
    pending:  'bg-amber-100 text-amber-700',
    approved: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-red-100 text-red-700',
};
</script>

<template>
    <AdminLayout>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Avis clients</h1>
                <p class="text-sm text-gray-500 mt-0.5">Modérez les avis avant publication</p>
            </div>
            <!-- Counters -->
            <div class="flex gap-3 text-sm">
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-3 py-2 text-center">
                    <p class="font-bold text-amber-700 text-lg leading-none">{{ counts.pending ?? 0 }}</p>
                    <p class="text-xs text-amber-600">En attente</p>
                </div>
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-3 py-2 text-center">
                    <p class="font-bold text-emerald-700 text-lg leading-none">{{ counts.approved ?? 0 }}</p>
                    <p class="text-xs text-emerald-600">Approuvés</p>
                </div>
                <div class="bg-red-50 border border-red-200 rounded-xl px-3 py-2 text-center">
                    <p class="font-bold text-red-700 text-lg leading-none">{{ counts.rejected ?? 0 }}</p>
                    <p class="text-xs text-red-600">Rejetés</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card p-4 mb-5 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-xs text-gray-500 mb-1">Rechercher</label>
                <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Produit, client, commentaire…" class="input-field text-sm" />
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Statut</label>
                <select v-model="statusFilter" class="input-field text-sm">
                    <option value="">Tous</option>
                    <option value="pending">En attente</option>
                    <option value="approved">Approuvés</option>
                    <option value="rejected">Rejetés</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Note</label>
                <select v-model="ratingFilter" class="input-field text-sm">
                    <option value="">Toutes</option>
                    <option v-for="n in [5,4,3,2,1]" :key="n" :value="n">{{ n }} étoile{{ n > 1 ? 's' : '' }}</option>
                </select>
            </div>
            <button @click="applyFilters" class="btn-primary text-sm">Filtrer</button>
        </div>

        <!-- Table -->
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Produit</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Client</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Note</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Commentaire</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Statut</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="review in reviews.data" :key="review.id" class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800 text-xs line-clamp-1">{{ review.product?.title ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                {{ review.user ? (review.user.first_name + ' ' + review.user.last_name) : 'Invité' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-amber-400 text-sm">{{ stars(review.rating) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-xs text-gray-600 line-clamp-2 max-w-xs">{{ review.comment || '—' }}</p>
                                <div v-if="review.photos?.length" class="flex gap-1 mt-1">
                                    <img v-for="photo in review.photos.slice(0,3)" :key="photo" :src="`/storage/${photo}`" class="w-8 h-8 rounded object-cover" />
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">
                                {{ new Date(review.created_at).toLocaleDateString('fr-FR') }}
                            </td>
                            <td class="px-4 py-3">
                                <span :class="['text-xs font-semibold px-2 py-1 rounded-full', statusColors[review.status]]">
                                    {{ statusLabels[review.status] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1.5">
                                    <button v-if="review.status !== 'approved'"
                                            @click="approve(review.id)"
                                            class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-medium hover:bg-emerald-100 transition">
                                        ✓ Approuver
                                    </button>
                                    <button v-if="review.status !== 'rejected'"
                                            @click="reject(review.id)"
                                            class="px-2.5 py-1 bg-red-50 text-red-700 rounded-lg text-xs font-medium hover:bg-red-100 transition">
                                        ✗ Rejeter
                                    </button>
                                    <button @click="destroy(review.id)" class="px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs hover:bg-gray-200 transition">
                                        Suppr.
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!reviews.data.length">
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400 text-sm">Aucun avis trouvé.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div v-if="reviews.last_page > 1" class="flex justify-center gap-1 p-4 border-t border-gray-100">
                <a v-for="link in reviews.links" :key="link.label"
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
