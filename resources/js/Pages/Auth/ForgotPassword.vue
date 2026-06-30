<script setup>
import { useForm, Link } from '@inertiajs/vue3';

defineProps({ status: String });

const form = useForm({ email: '' });

function submit() {
    form.post(route('password.email'));
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-sezy-50 px-4">
        <div class="card w-full max-w-md p-8">
            <Link :href="route('home')" class="block text-center text-3xl font-extrabold text-sezy-dark mb-6">SEZY</Link>
            <h1 class="text-xl font-bold text-center mb-1">Mot de passe oublié</h1>
            <p class="text-sm text-gray-500 text-center mb-6">
                Recevez un lien de réinitialisation par email (valable 24h).
            </p>

            <p v-if="status" class="text-sm text-green-600 bg-green-50 rounded-lg p-3 mb-4">{{ status }}</p>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input v-model="form.email" type="email" class="input-field" required autofocus />
                    <p v-if="form.errors.email" class="text-xs text-red-600 mt-1">{{ form.errors.email }}</p>
                </div>
                <button type="submit" class="btn-primary w-full" :disabled="form.processing">Envoyer le lien</button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                <Link :href="route('login')" class="text-sezy-dark font-medium hover:underline">Retour à la connexion</Link>
            </p>
        </div>
    </div>
</template>
