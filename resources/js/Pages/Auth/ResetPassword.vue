<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({ email: String, token: String });

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post(route('password.store'));
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-sezy-50 px-4">
        <div class="card w-full max-w-md p-8">
            <Link :href="route('home')" class="block text-center text-3xl font-extrabold text-sezy-dark mb-6">SEZY</Link>
            <h1 class="text-xl font-bold text-center mb-6">Nouveau mot de passe</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input v-model="form.email" type="email" class="input-field bg-gray-50" readonly />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                    <input v-model="form.password" type="password" class="input-field" required autofocus />
                    <p v-if="form.errors.password" class="text-xs text-red-600 mt-1">{{ form.errors.password }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                    <input v-model="form.password_confirmation" type="password" class="input-field" required />
                </div>
                <button type="submit" class="btn-primary w-full" :disabled="form.processing">Réinitialiser</button>
            </form>
        </div>
    </div>
</template>
