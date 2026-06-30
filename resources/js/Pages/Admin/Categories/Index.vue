<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { PencilIcon, TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ categories: Array });

const showForm = ref(false);
const editingId = ref(null);

const form = useForm({
    parent_id: null,
    name: '',
    description: '',
    is_active: true,
    meta_title: '',
    meta_description: '',
});

function edit(category) {
    editingId.value = category.id;
    form.parent_id = category.parent_id;
    form.name = category.name;
    form.description = category.description;
    form.is_active = category.is_active;
    showForm.value = true;
}

function submit() {
    if (editingId.value) {
        form.put(route('admin.categories.update', editingId.value), { onSuccess: resetForm });
    } else {
        form.post(route('admin.categories.store'), { onSuccess: resetForm });
    }
}

function resetForm() {
    form.reset();
    editingId.value = null;
    showForm.value = false;
}

function destroy(category) {
    if (confirm(`Supprimer la catégorie "${category.name}" ?`)) {
        router.delete(route('admin.categories.destroy', category.id));
    }
}
</script>

<template>
    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Catégories</h1>
            <button class="btn-primary" @click="showForm = !showForm">
                <PlusIcon class="w-5 h-5" /> Nouvelle catégorie
            </button>
        </div>

        <form v-if="showForm" @submit.prevent="submit" class="card p-5 space-y-4 mb-6">
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                    <input v-model="form.name" type="text" class="input-field" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie parente</label>
                    <select v-model="form.parent_id" class="input-field">
                        <option :value="null">— Aucune (catégorie racine) —</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea v-model="form.description" class="input-field" rows="2"></textarea>
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" v-model="form.is_active" class="rounded text-sezy" /> Catégorie active
            </label>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary" :disabled="form.processing">Enregistrer</button>
                <button type="button" class="btn-secondary" @click="resetForm">Annuler</button>
            </div>
        </form>

        <div class="card divide-y">
            <div v-for="cat in categories" :key="cat.id" class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium">{{ cat.name }} <span class="text-xs text-gray-400">({{ cat.products_count }} produits)</span></p>
                        <span :class="['text-xs', cat.is_active ? 'text-green-600' : 'text-gray-400']">{{ cat.is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <div class="flex gap-3">
                        <button @click="edit(cat)" class="text-gray-400 hover:text-sezy-dark"><PencilIcon class="w-5 h-5" /></button>
                        <button @click="destroy(cat)" class="text-gray-400 hover:text-red-500"><TrashIcon class="w-5 h-5" /></button>
                    </div>
                </div>
                <div v-if="cat.children?.length" class="mt-3 ml-4 space-y-2">
                    <div v-for="child in cat.children" :key="child.id" class="flex items-center justify-between text-sm text-gray-600">
                        <span>↳ {{ child.name }}</span>
                        <div class="flex gap-3">
                            <button @click="edit(child)" class="text-gray-400 hover:text-sezy-dark"><PencilIcon class="w-4 h-4" /></button>
                            <button @click="destroy(child)" class="text-gray-400 hover:text-red-500"><TrashIcon class="w-4 h-4" /></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
