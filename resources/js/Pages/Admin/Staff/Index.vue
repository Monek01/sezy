<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ staff: Array, roles: Object });

const showForm = ref(false);
const form = useForm({ first_name: '', last_name: '', email: '', role: '', password: '' });

function submit() {
    form.post(route('admin.staff.store'), { onSuccess: () => { form.reset(); showForm.value = false; } });
}

function updateRole(member, role) {
    router.patch(route('admin.staff.update', member.id), { role });
}

function destroy(member) {
    if (confirm(`Supprimer le compte de ${member.first_name} ?`)) {
        router.delete(route('admin.staff.destroy', member.id));
    }
}
</script>

<template>
    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Équipe</h1>
                <p class="text-sm text-gray-500">Gestion des accès back-office — réservé Super Admin</p>
            </div>
            <button class="btn-primary" @click="showForm = !showForm"><PlusIcon class="w-5 h-5" /> Nouveau compte</button>
        </div>

        <form v-if="showForm" @submit.prevent="submit" class="card p-5 space-y-4 mb-6">
            <div class="grid sm:grid-cols-2 gap-4">
                <input v-model="form.first_name" type="text" placeholder="Prénom" class="input-field" required />
                <input v-model="form.last_name" type="text" placeholder="Nom" class="input-field" required />
            </div>
            <input v-model="form.email" type="email" placeholder="Email" class="input-field" required />
            <select v-model="form.role" class="input-field" required>
                <option value="" disabled>Rôle...</option>
                <option v-for="(label, key) in roles" :key="key" :value="key">{{ label }}</option>
            </select>
            <input v-model="form.password" type="password" placeholder="Mot de passe temporaire" class="input-field" required />
            <p class="text-xs text-amber-600">2FA obligatoire à activer dès la première connexion.</p>
            <button type="submit" class="btn-primary" :disabled="form.processing">Créer le compte</button>
        </form>

        <div class="card overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="text-left p-3">Nom</th>
                        <th class="text-left p-3">Email</th>
                        <th class="text-left p-3">Rôle</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="m in staff" :key="m.id">
                        <td class="p-3 font-medium">{{ m.first_name }} {{ m.last_name }}</td>
                        <td class="p-3 text-gray-500">{{ m.email }}</td>
                        <td class="p-3">
                            <select :value="m.role" @change="updateRole(m, $event.target.value)" class="input-field text-sm py-1">
                                <option v-for="(label, key) in roles" :key="key" :value="key">{{ label }}</option>
                            </select>
                        </td>
                        <td class="p-3 text-right">
                            <button @click="destroy(m)" class="text-gray-400 hover:text-red-500"><TrashIcon class="w-5 h-5" /></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
