<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { TrashIcon, PencilIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ addresses: Array });

const showForm = ref(false);
const editingId = ref(null);

const form = useForm({
    label: 'Domicile', full_name: '', phone: '', city: '', district: '',
    address_line: '', landmark: '', is_default: false,
});

function edit(address) {
    editingId.value = address.id;
    form.label = address.label;
    form.full_name = address.full_name;
    form.phone = address.phone;
    form.city = address.city;
    form.district = address.district;
    form.address_line = address.address_line;
    form.landmark = address.landmark;
    form.is_default = address.is_default;
    showForm.value = true;
}

function submit() {
    if (editingId.value) {
        form.put(route('adresses.update', editingId.value), { onSuccess: resetForm });
    } else {
        form.post(route('adresses.store'), { onSuccess: resetForm });
    }
}

function resetForm() {
    form.reset();
    editingId.value = null;
    showForm.value = false;
}

function destroy(address) {
    if (confirm('Supprimer cette adresse ?')) {
        router.delete(route('adresses.destroy', address.id));
    }
}
</script>

<template>
    <ShopLayout>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
            <h1 class="text-2xl font-bold mb-6">Carnet d'adresses</h1>

            <nav class="flex gap-4 mb-6 text-sm font-medium border-b">
                <Link :href="route('profile.edit')" class="pb-3 text-gray-500 hover:text-sezy-dark">Profil</Link>
                <Link :href="route('orders.index')" class="pb-3 text-gray-500 hover:text-sezy-dark">Mes commandes</Link>
                <Link :href="route('wishlist.index')" class="pb-3 text-gray-500 hover:text-sezy-dark">Liste de souhaits</Link>
                <span class="pb-3 border-b-2 border-sezy-dark text-sezy-dark">Adresses</span>
            </nav>

            <div class="space-y-3 mb-6">
                <div v-for="addr in addresses" :key="addr.id" class="card p-4 flex justify-between">
                    <div>
                        <p class="font-semibold text-sm flex items-center gap-2">
                            {{ addr.label }}
                            <span v-if="addr.is_default" class="text-[10px] bg-sezy-50 text-sezy-dark px-2 py-0.5 rounded-full">Par défaut</span>
                        </p>
                        <p class="text-sm text-gray-600">{{ addr.full_name }} · {{ addr.phone }}</p>
                        <p class="text-sm text-gray-500">{{ addr.address_line }}, {{ addr.district }}, {{ addr.city }}</p>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button @click="edit(addr)" class="text-gray-400 hover:text-sezy-dark"><PencilIcon class="w-5 h-5" /></button>
                        <button @click="destroy(addr)" class="text-gray-400 hover:text-red-500"><TrashIcon class="w-5 h-5" /></button>
                    </div>
                </div>
            </div>

            <button v-if="!showForm" @click="showForm = true" class="btn-secondary">+ Ajouter une adresse</button>

            <form v-else @submit.prevent="submit" class="card p-5 space-y-4 mt-4">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Libellé</label>
                        <input v-model="form.label" type="text" class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet *</label>
                        <input v-model="form.full_name" type="text" class="input-field" required />
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone *</label>
                        <input v-model="form.phone" type="tel" class="input-field" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ville *</label>
                        <input v-model="form.city" type="text" class="input-field" required />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quartier</label>
                    <input v-model="form.district" type="text" class="input-field" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse complète *</label>
                    <textarea v-model="form.address_line" class="input-field" rows="2" required></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Point de repère</label>
                    <input v-model="form.landmark" type="text" class="input-field" />
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="form.is_default" class="rounded text-sezy" /> Adresse par défaut
                </label>
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary" :disabled="form.processing">Enregistrer</button>
                    <button type="button" class="btn-secondary" @click="resetForm">Annuler</button>
                </div>
            </form>
        </div>
    </ShopLayout>
</template>
