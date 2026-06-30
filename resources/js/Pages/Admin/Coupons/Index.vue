<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ coupons: Object });

const showForm = ref(false);
const form = useForm({
    code: '', type: 'percentage', value: '', min_order_amount: 0,
    usage_limit: '', starts_at: '', expires_at: '', is_active: true,
});

function submit() {
    form.post(route('admin.coupons.store'), { onSuccess: () => { form.reset(); showForm.value = false; } });
}

function destroy(coupon) {
    if (confirm(`Supprimer le code "${coupon.code}" ?`)) {
        router.delete(route('admin.coupons.destroy', coupon.id));
    }
}
</script>

<template>
    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Codes promo</h1>
            <button class="btn-primary" @click="showForm = !showForm"><PlusIcon class="w-5 h-5" /> Nouveau code</button>
        </div>

        <form v-if="showForm" @submit.prevent="submit" class="card p-5 space-y-4 mb-6">
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Code *</label>
                    <input v-model="form.code" type="text" class="input-field uppercase" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select v-model="form.type" class="input-field">
                        <option value="percentage">Pourcentage (%)</option>
                        <option value="fixed">Montant fixe (FCFA)</option>
                    </select>
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valeur *</label>
                    <input v-model="form.value" type="number" class="input-field" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Montant minimum commande</label>
                    <input v-model="form.min_order_amount" type="number" class="input-field" />
                </div>
            </div>
            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Limite d'utilisation</label>
                    <input v-model="form.usage_limit" type="number" class="input-field" placeholder="Illimité" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Début</label>
                    <input v-model="form.starts_at" type="datetime-local" class="input-field" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expiration</label>
                    <input v-model="form.expires_at" type="datetime-local" class="input-field" />
                </div>
            </div>
            <button type="submit" class="btn-primary" :disabled="form.processing">Créer</button>
        </form>

        <div class="card overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="text-left p-3">Code</th>
                        <th class="text-left p-3">Valeur</th>
                        <th class="text-left p-3">Utilisations</th>
                        <th class="text-left p-3">Expiration</th>
                        <th class="text-left p-3">Statut</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="c in coupons.data" :key="c.id">
                        <td class="p-3 font-mono font-medium">{{ c.code }}</td>
                        <td class="p-3">{{ c.type === 'percentage' ? c.value + '%' : c.value + ' FCFA' }}</td>
                        <td class="p-3">{{ c.used_count }} / {{ c.usage_limit ?? '∞' }}</td>
                        <td class="p-3 text-gray-500">{{ c.expires_at ? new Date(c.expires_at).toLocaleDateString('fr-FR') : '—' }}</td>
                        <td class="p-3"><span :class="['text-xs px-2 py-1 rounded-full', c.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">{{ c.is_active ? 'Actif' : 'Inactif' }}</span></td>
                        <td class="p-3 text-right">
                            <button @click="destroy(c)" class="text-gray-400 hover:text-red-500"><TrashIcon class="w-5 h-5" /></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
