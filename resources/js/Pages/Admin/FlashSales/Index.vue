<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useToast } from 'vue-toastification';

const props = defineProps({
    flashSales: Object,
    products:   Array,
});

const toast = useToast();
const showModal = ref(false);
const editSale = ref(null);

const form = useForm({
    title:     '',
    starts_at: '',
    ends_at:   '',
    is_active: false,
    items:     [],
});

function openCreate() {
    editSale.value = null;
    form.reset();
    form.items = [];
    showModal.value = true;
}

function openEdit(sale) {
    editSale.value = sale;
    form.title     = sale.title;
    form.starts_at = sale.starts_at?.slice(0, 16);
    form.ends_at   = sale.ends_at?.slice(0, 16);
    form.is_active = sale.is_active;
    form.items     = [];
    showModal.value = true;
}

function addItem() {
    form.items.push({ product_id: '', discount_price: '', qty_limit: '' });
}
function removeItem(i) {
    form.items.splice(i, 1);
}

function submit() {
    if (editSale.value) {
        form.put(route('admin.flash-sales.update', editSale.value.id), {
            onSuccess: () => { showModal.value = false; toast.success('Vente flash mise à jour.'); },
        });
    } else {
        form.post(route('admin.flash-sales.store'), {
            onSuccess: () => { showModal.value = false; toast.success('Vente flash créée.'); },
        });
    }
}

function toggle(sale) {
    router.patch(route('admin.flash-sales.toggle', sale.id), {}, {
        onSuccess: () => toast.success(sale.is_active ? 'Désactivée.' : 'Activée.'),
    });
}

function destroy(id) {
    if (!confirm('Supprimer cette vente flash ?')) return;
    router.delete(route('admin.flash-sales.destroy', id), {
        onSuccess: () => toast.success('Vente flash supprimée.'),
    });
}

function fmt(v) { return new Intl.NumberFormat('fr-FR').format(v ?? 0) + ' FCFA'; }
function fmtDate(d) { return d ? new Date(d).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' }) : '—'; }

function productName(id) {
    return props.products.find(p => p.id === parseInt(id))?.title ?? '—';
}

function isActive(sale) {
    const now = Date.now();
    return sale.is_active && new Date(sale.starts_at) <= now && new Date(sale.ends_at) >= now;
}
</script>

<template>
    <AdminLayout>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Ventes flash</h1>
                <p class="text-sm text-gray-500 mt-0.5">Créez et gérez vos promotions temporaires</p>
            </div>
            <button @click="openCreate" class="btn-primary">+ Nouvelle vente flash</button>
        </div>

        <!-- Liste -->
        <div class="space-y-4">
            <div v-for="sale in flashSales.data" :key="sale.id" class="card p-5">
                <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h2 class="font-bold text-gray-900">{{ sale.title }}</h2>
                            <span v-if="isActive(sale)" class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" /> En cours
                            </span>
                            <span v-else-if="sale.is_active" class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">Planifiée</span>
                            <span v-else class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-xs font-semibold">Inactive</span>
                        </div>
                        <p class="text-xs text-gray-400">{{ fmtDate(sale.starts_at) }} → {{ fmtDate(sale.ends_at) }}</p>
                        <!-- Products -->
                        <div v-if="sale.products?.length" class="mt-3 flex flex-wrap gap-2">
                            <div v-for="p in sale.products" :key="p.id"
                                 class="flex items-center gap-2 bg-gray-50 border border-gray-100 rounded-lg px-3 py-1.5 text-xs">
                                <span class="font-medium">{{ p.product?.title }}</span>
                                <span class="text-sezy-dark font-bold">{{ fmt(p.discount_price) }}</span>
                                <span v-if="p.qty_limit" class="text-gray-400">/ {{ p.qty_limit }} max</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button @click="toggle(sale)"
                                :class="['px-3 py-1.5 rounded-lg text-xs font-medium transition',
                                         sale.is_active ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100']">
                            {{ sale.is_active ? 'Désactiver' : 'Activer' }}
                        </button>
                        <button @click="openEdit(sale)" class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg text-xs hover:bg-gray-200 transition">Modifier</button>
                        <button @click="destroy(sale.id)" class="px-3 py-1.5 bg-red-50 text-red-600 rounded-lg text-xs hover:bg-red-100 transition">Supprimer</button>
                    </div>
                </div>
            </div>
            <div v-if="!flashSales.data.length" class="card p-12 text-center">
                <p class="text-4xl mb-3">⚡</p>
                <p class="font-semibold text-gray-700">Aucune vente flash</p>
                <p class="text-sm text-gray-400 mt-1">Créez votre première vente flash pour booster vos ventes.</p>
                <button @click="openCreate" class="btn-primary mt-4">Créer une vente flash</button>
            </div>
        </div>

        <!-- Modal -->
        <Transition name="fade">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showModal = false" />
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                    <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between z-10">
                        <h2 class="font-bold text-lg">{{ editSale ? 'Modifier la vente flash' : 'Nouvelle vente flash' }}</h2>
                        <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 text-xl leading-none">×</button>
                    </div>
                    <form @submit.prevent="submit" class="p-6 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                            <input v-model="form.title" type="text" class="input-field" required placeholder="Ex : Vente flash week-end" />
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Début *</label>
                                <input v-model="form.starts_at" type="datetime-local" class="input-field" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fin *</label>
                                <input v-model="form.ends_at" type="datetime-local" class="input-field" required />
                            </div>
                        </div>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" v-model="form.is_active" class="rounded text-sezy" /> Activer immédiatement
                        </label>

                        <div v-if="!editSale">
                            <div class="flex items-center justify-between mb-3">
                                <label class="text-sm font-medium text-gray-700">Produits en promotion</label>
                                <button type="button" @click="addItem" class="text-xs text-sezy-dark hover:underline font-semibold">+ Ajouter un produit</button>
                            </div>
                            <div v-for="(item, i) in form.items" :key="i" class="flex gap-2 mb-2 items-start">
                                <select v-model="item.product_id" class="input-field flex-1 text-sm" required>
                                    <option value="" disabled>Choisir un produit…</option>
                                    <option v-for="p in products" :key="p.id" :value="p.id">{{ p.title }} ({{ new Intl.NumberFormat('fr-FR').format(p.price) }} FCFA)</option>
                                </select>
                                <input v-model="item.discount_price" type="number" min="0" class="input-field w-32 text-sm" placeholder="Prix promo" required />
                                <input v-model="item.qty_limit" type="number" min="1" class="input-field w-24 text-sm" placeholder="Qté max" />
                                <button type="button" @click="removeItem(i)" class="text-red-400 hover:text-red-600 text-lg leading-none mt-2">×</button>
                            </div>
                            <p v-if="!form.items.length" class="text-xs text-gray-400 py-2">Aucun produit ajouté. Cliquez sur "+ Ajouter un produit".</p>
                        </div>

                        <p v-if="form.errors.items" class="text-xs text-red-600">{{ form.errors.items }}</p>

                        <div class="flex gap-3 justify-end pt-2">
                            <button type="button" @click="showModal = false" class="btn-secondary text-sm">Annuler</button>
                            <button type="submit" :disabled="form.processing" class="btn-primary text-sm">
                                {{ form.processing ? 'Enregistrement…' : (editSale ? 'Mettre à jour' : 'Créer') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
