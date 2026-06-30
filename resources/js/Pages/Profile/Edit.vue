<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ user: Object });

const profileForm = useForm({
    first_name: props.user.first_name,
    last_name: props.user.last_name,
    email: props.user.email,
    phone: props.user.phone,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function updateProfile() {
    profileForm.patch(route('profile.update'));
}

function updatePassword() {
    passwordForm.put(route('profile.password'), { onSuccess: () => passwordForm.reset() });
}
</script>

<template>
    <ShopLayout>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
            <h1 class="text-2xl font-bold mb-6">Mon compte</h1>

            <nav class="flex gap-4 mb-6 text-sm font-medium border-b">
                <span class="pb-3 border-b-2 border-sezy-dark text-sezy-dark">Profil</span>
                <Link :href="route('orders.index')" class="pb-3 text-gray-500 hover:text-sezy-dark">Mes commandes</Link>
                <Link :href="route('wishlist.index')" class="pb-3 text-gray-500 hover:text-sezy-dark">Liste de souhaits</Link>
                <Link :href="route('adresses.index')" class="pb-3 text-gray-500 hover:text-sezy-dark">Adresses</Link>
            </nav>

            <div class="card p-5 mb-6 bg-sezy-50/50 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Points de fidélité SEZY</p>
                    <p class="text-2xl font-bold text-sezy-dark">{{ user.loyalty_points }} pts</p>
                </div>
                <span class="capitalize bg-sezy-dark text-white text-xs font-bold px-3 py-1.5 rounded-full">{{ user.loyalty_tier }}</span>
            </div>

            <form @submit.prevent="updateProfile" class="card p-5 space-y-4 mb-6">
                <h2 class="font-bold">Informations personnelles</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                        <input v-model="profileForm.first_name" type="text" class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                        <input v-model="profileForm.last_name" type="text" class="input-field" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input v-model="profileForm.email" type="email" class="input-field" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                    <input v-model="profileForm.phone" type="tel" class="input-field" />
                </div>
                <button type="submit" class="btn-primary" :disabled="profileForm.processing">Enregistrer</button>
            </form>

            <form @submit.prevent="updatePassword" class="card p-5 space-y-4">
                <h2 class="font-bold">Changer le mot de passe</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe actuel</label>
                    <input v-model="passwordForm.current_password" type="password" class="input-field" />
                    <p v-if="passwordForm.errors.current_password" class="text-xs text-red-600 mt-1">{{ passwordForm.errors.current_password }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                    <input v-model="passwordForm.password" type="password" class="input-field" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer</label>
                    <input v-model="passwordForm.password_confirmation" type="password" class="input-field" />
                </div>
                <button type="submit" class="btn-primary" :disabled="passwordForm.processing">Mettre à jour</button>
            </form>
        </div>
    </ShopLayout>
</template>
