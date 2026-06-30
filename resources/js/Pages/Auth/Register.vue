<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    referral_code: new URLSearchParams(window.location.search).get('ref') ?? '',
});

function submit() {
    form.post(route('register'), { onFinish: () => form.reset('password', 'password_confirmation') });
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-sezy-50 px-4 py-10">
        <div class="card w-full max-w-md p-8">
            <Link :href="route('home')" class="block text-center text-3xl font-extrabold text-sezy-dark mb-6">SEZY</Link>
            <h1 class="text-xl font-bold text-center mb-1">Créer un compte</h1>
            <p class="text-sm text-gray-500 text-center mb-6">Rejoignez SEZY en quelques secondes</p>

            <a :href="route('auth.google')" class="w-full flex items-center justify-center gap-2 border border-gray-200 rounded-lg py-2.5 mb-4 text-sm font-medium hover:bg-gray-50">
                <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                Continuer avec Google
            </a>

            <div class="flex items-center gap-3 my-4">
                <div class="flex-1 h-px bg-gray-200" />
                <span class="text-xs text-gray-400">ou</span>
                <div class="flex-1 h-px bg-gray-200" />
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                        <input v-model="form.first_name" type="text" class="input-field" required />
                        <p v-if="form.errors.first_name" class="text-xs text-red-600 mt-1">{{ form.errors.first_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                        <input v-model="form.last_name" type="text" class="input-field" required />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input v-model="form.email" type="email" class="input-field" required />
                    <p v-if="form.errors.email" class="text-xs text-red-600 mt-1">{{ form.errors.email }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                    <input v-model="form.phone" type="tel" placeholder="+229 ..." class="input-field" required />
                    <p v-if="form.errors.phone" class="text-xs text-red-600 mt-1">{{ form.errors.phone }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                    <input v-model="form.password" type="password" class="input-field" required />
                    <p v-if="form.errors.password" class="text-xs text-red-600 mt-1">{{ form.errors.password }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                    <input v-model="form.password_confirmation" type="password" class="input-field" required />
                </div>
                <div v-if="form.referral_code">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Code de parrainage</label>
                    <input v-model="form.referral_code" type="text" class="input-field" />
                </div>
                <button type="submit" class="btn-primary w-full" :disabled="form.processing">Créer mon compte</button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Déjà inscrit ? <Link :href="route('login')" class="text-sezy-dark font-medium hover:underline">Se connecter</Link>
            </p>
        </div>
    </div>
</template>
