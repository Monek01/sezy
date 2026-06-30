<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';

const props = defineProps({
    settings: { type: Object, default: () => ({}) },
});

const toast = useToast();
const activeTab = ref('general');

const tabs = [
    { id: 'general', label: '🏪 Général', icon: null },
    { id: 'social', label: '📱 Réseaux sociaux', icon: null },
    { id: 'contact', label: '📬 Contact', icon: null },
    { id: 'payment', label: '💳 Paiement', icon: null },
    { id: 'shipping', label: '🚚 Livraison', icon: null },
    { id: 'notifications', label: '🔔 Notifications', icon: null },
];

const form = useForm({
    // Général
    site_name: props.settings.site_name ?? 'SEZY',
    site_tagline: props.settings.site_tagline ?? 'La plateforme e-commerce du Bénin',
    site_description: props.settings.site_description ?? '',
    currency: props.settings.currency ?? 'FCFA',
    timezone: props.settings.timezone ?? 'Africa/Porto-Novo',
    language: props.settings.language ?? 'fr',

    // Social
    instagram_url: props.settings.instagram_url ?? 'https://instagram.com/sezy',
    facebook_url: props.settings.facebook_url ?? 'https://facebook.com/sezy',
    whatsapp_number: props.settings.whatsapp_number ?? '+22900000000',
    tiktok_url: props.settings.tiktok_url ?? 'https://tiktok.com/@sezy',

    // Contact
    contact_email: props.settings.contact_email ?? 'agencesezy@gmail.com',
    support_email: props.settings.support_email ?? 'agencesezy@gmail.com',
    contact_phone: props.settings.contact_phone ?? '',
    contact_address: props.settings.contact_address ?? 'Cotonou, Bénin',

    // Paiement
    mtn_momo_enabled: props.settings.mtn_momo_enabled ?? true,
    moov_money_enabled: props.settings.moov_money_enabled ?? true,
    wave_enabled: props.settings.wave_enabled ?? true,
    card_enabled: props.settings.card_enabled ?? false,
    paydunya_api_key: props.settings.paydunya_api_key ?? '',
    paydunya_mode: props.settings.paydunya_mode ?? 'test',

    // Livraison
    free_shipping_threshold: props.settings.free_shipping_threshold ?? 15000,
    standard_shipping_fee: props.settings.standard_shipping_fee ?? 1500,
    express_shipping_fee: props.settings.express_shipping_fee ?? 3000,
    click_collect_enabled: props.settings.click_collect_enabled ?? true,
    click_collect_delay_days: props.settings.click_collect_delay_days ?? 7,

    // Notifications
    notif_new_order: props.settings.notif_new_order ?? true,
    notif_low_stock: props.settings.notif_low_stock ?? true,
    notif_new_review: props.settings.notif_new_review ?? true,
    low_stock_threshold: props.settings.low_stock_threshold ?? 5,
    admin_notif_email: props.settings.admin_notif_email ?? 'agencesezy@gmail.com',
});

function save() {
    form.post(route('admin.settings.update'), {
        onSuccess: () => toast.success('Paramètres enregistrés avec succès !'),
        onError: () => toast.error('Erreur lors de l\'enregistrement.'),
    });
}
</script>

<template>
    <AdminLayout>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900">Paramètres</h1>
                <p class="text-gray-500 text-sm mt-0.5">Configuration générale de la plateforme SEZY</p>
            </div>
            <button @click="save" :disabled="form.processing" class="btn-primary flex items-center gap-2">
                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                {{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}
            </button>
        </div>

        <!-- Mobile tab select -->
        <select v-model="activeTab" class="sm:hidden input-field mb-4 text-sm">
            <option v-for="tab in tabs" :key="tab.id" :value="tab.id">{{ tab.label }}</option>
        </select>

        <div class="flex gap-6">
            <!-- Tabs sidebar -->
            <nav class="hidden sm:flex flex-col w-52 shrink-0 gap-1">
                <button v-for="tab in tabs" :key="tab.id"
                        @click="activeTab = tab.id"
                        class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-sm font-medium text-left transition"
                        :class="activeTab === tab.id ? 'bg-sezy-dark text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'">
                    {{ tab.label }}
                </button>
            </nav>

            <!-- Content -->
            <div class="flex-1 min-w-0 space-y-5">

                <!-- Général -->
                <template v-if="activeTab === 'general'">
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm space-y-5">
                        <h2 class="font-bold text-gray-800 text-base">Informations générales</h2>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom du site *</label>
                                <input v-model="form.site_name" type="text" class="input-field" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Slogan</label>
                                <input v-model="form.site_tagline" type="text" class="input-field" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description (SEO)</label>
                            <textarea v-model="form.site_description" class="input-field" rows="3"></textarea>
                        </div>
                        <div class="grid sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Devise</label>
                                <select v-model="form.currency" class="input-field">
                                    <option value="FCFA">FCFA</option>
                                    <option value="EUR">EUR</option>
                                    <option value="USD">USD</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fuseau horaire</label>
                                <select v-model="form.timezone" class="input-field">
                                    <option value="Africa/Porto-Novo">Porto-Novo (GMT+1)</option>
                                    <option value="Africa/Abidjan">Abidjan (GMT)</option>
                                    <option value="Africa/Lagos">Lagos (GMT+1)</option>
                                    <option value="Europe/Paris">Paris (GMT+1/+2)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Langue</label>
                                <select v-model="form.language" class="input-field">
                                    <option value="fr">Français</option>
                                    <option value="en">English</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Réseaux sociaux -->
                <template v-if="activeTab === 'social'">
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm space-y-5">
                        <h2 class="font-bold text-gray-800 text-base">Réseaux sociaux</h2>
                        <div class="grid gap-4">
                            <div>
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-1">
                                    <svg class="w-5 h-5 text-pink-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                    Instagram
                                </label>
                                <input v-model="form.instagram_url" type="url" class="input-field" placeholder="https://instagram.com/votre-compte" />
                            </div>
                            <div>
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-1">
                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    Facebook
                                </label>
                                <input v-model="form.facebook_url" type="url" class="input-field" placeholder="https://facebook.com/votre-page" />
                            </div>
                            <div>
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-1">
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    WhatsApp (numéro avec indicatif)
                                </label>
                                <input v-model="form.whatsapp_number" type="tel" class="input-field" placeholder="+22900000000" />
                            </div>
                            <div>
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-1">
                                    <svg class="w-5 h-5 text-gray-800" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                                    TikTok
                                </label>
                                <input v-model="form.tiktok_url" type="url" class="input-field" placeholder="https://tiktok.com/@votre-compte" />
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Contact -->
                <template v-if="activeTab === 'contact'">
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm space-y-5">
                        <h2 class="font-bold text-gray-800 text-base">Informations de contact</h2>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email de contact</label>
                                <input v-model="form.contact_email" type="email" class="input-field" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email support SAV</label>
                                <input v-model="form.support_email" type="email" class="input-field" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                                <input v-model="form.contact_phone" type="tel" class="input-field" placeholder="+229 XX XX XX XX" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                                <input v-model="form.contact_address" type="text" class="input-field" placeholder="Cotonou, Bénin" />
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Paiement -->
                <template v-if="activeTab === 'payment'">
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm space-y-5">
                        <h2 class="font-bold text-gray-800 text-base">Modes de paiement</h2>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <label class="flex items-center justify-between p-4 rounded-xl border-2 cursor-pointer transition"
                                   :class="form.mtn_momo_enabled ? 'border-yellow-400 bg-yellow-50' : 'border-gray-200'">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">📱</span>
                                    <div>
                                        <p class="font-semibold text-sm">MTN Mobile Money</p>
                                        <p class="text-xs text-gray-400">Bénin, Nigeria</p>
                                    </div>
                                </div>
                                <input type="checkbox" v-model="form.mtn_momo_enabled" class="rounded text-sezy w-4 h-4" />
                            </label>
                            <label class="flex items-center justify-between p-4 rounded-xl border-2 cursor-pointer transition"
                                   :class="form.moov_money_enabled ? 'border-blue-400 bg-blue-50' : 'border-gray-200'">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">💳</span>
                                    <div>
                                        <p class="font-semibold text-sm">Moov Money</p>
                                        <p class="text-xs text-gray-400">Bénin</p>
                                    </div>
                                </div>
                                <input type="checkbox" v-model="form.moov_money_enabled" class="rounded text-sezy w-4 h-4" />
                            </label>
                            <label class="flex items-center justify-between p-4 rounded-xl border-2 cursor-pointer transition"
                                   :class="form.wave_enabled ? 'border-cyan-400 bg-cyan-50' : 'border-gray-200'">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">🌊</span>
                                    <div>
                                        <p class="font-semibold text-sm">Wave</p>
                                        <p class="text-xs text-gray-400">Afrique de l'Ouest</p>
                                    </div>
                                </div>
                                <input type="checkbox" v-model="form.wave_enabled" class="rounded text-sezy w-4 h-4" />
                            </label>
                            <label class="flex items-center justify-between p-4 rounded-xl border-2 cursor-pointer transition"
                                   :class="form.card_enabled ? 'border-sezy bg-sezy-50' : 'border-gray-200'">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">💳</span>
                                    <div>
                                        <p class="font-semibold text-sm">Carte bancaire</p>
                                        <p class="text-xs text-gray-400">Via PayDunya</p>
                                    </div>
                                </div>
                                <input type="checkbox" v-model="form.card_enabled" class="rounded text-sezy w-4 h-4" />
                            </label>
                        </div>

                        <div class="border-t border-gray-100 pt-5">
                            <h3 class="font-semibold text-sm text-gray-700 mb-3">Configuration PayDunya</h3>
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Clé API</label>
                                    <input v-model="form.paydunya_api_key" type="password" class="input-field" placeholder="sk_live_..." />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Mode</label>
                                    <select v-model="form.paydunya_mode" class="input-field">
                                        <option value="test">Test (sandbox)</option>
                                        <option value="live">Production (live)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Livraison -->
                <template v-if="activeTab === 'shipping'">
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm space-y-5">
                        <h2 class="font-bold text-gray-800 text-base">Options de livraison</h2>
                        <div class="grid sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Livraison gratuite dès (FCFA)</label>
                                <input v-model="form.free_shipping_threshold" type="number" class="input-field" />
                                <p class="text-xs text-gray-400 mt-1">0 = jamais gratuite</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Frais standard (FCFA)</label>
                                <input v-model="form.standard_shipping_fee" type="number" class="input-field" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Frais express (FCFA)</label>
                                <input v-model="form.express_shipping_fee" type="number" class="input-field" />
                            </div>
                        </div>
                        <div class="border-t border-gray-100 pt-4 space-y-3">
                            <label class="flex items-center justify-between p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                                <div>
                                    <p class="font-medium text-sm">Click & Collect</p>
                                    <p class="text-xs text-gray-400">Retrait en point de collecte</p>
                                </div>
                                <input type="checkbox" v-model="form.click_collect_enabled" class="rounded text-sezy w-4 h-4" />
                            </label>
                            <div v-if="form.click_collect_enabled" class="pl-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Délai de conservation (jours)</label>
                                <input v-model="form.click_collect_delay_days" type="number" class="input-field w-32" min="1" max="30" />
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Notifications -->
                <template v-if="activeTab === 'notifications'">
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm space-y-5">
                        <h2 class="font-bold text-gray-800 text-base">Notifications administrateur</h2>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email de notification admin</label>
                            <input v-model="form.admin_notif_email" type="email" class="input-field max-w-md" />
                        </div>
                        <div class="space-y-3">
                            <label class="flex items-center justify-between p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                                <div>
                                    <p class="font-medium text-sm">🛍️ Nouvelle commande</p>
                                    <p class="text-xs text-gray-400">Notifier à chaque nouvelle commande</p>
                                </div>
                                <input type="checkbox" v-model="form.notif_new_order" class="rounded text-sezy w-4 h-4" />
                            </label>
                            <label class="flex items-center justify-between p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                                <div>
                                    <p class="font-medium text-sm">⚠️ Stock critique</p>
                                    <p class="text-xs text-gray-400">Alerte quand un produit atteint le seuil critique</p>
                                </div>
                                <input type="checkbox" v-model="form.notif_low_stock" class="rounded text-sezy w-4 h-4" />
                            </label>
                            <label class="flex items-center justify-between p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                                <div>
                                    <p class="font-medium text-sm">⭐ Nouvel avis</p>
                                    <p class="text-xs text-gray-400">Notifier à chaque avis client soumis</p>
                                </div>
                                <input type="checkbox" v-model="form.notif_new_review" class="rounded text-sezy w-4 h-4" />
                            </label>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Seuil stock critique (unités)</label>
                            <input v-model="form.low_stock_threshold" type="number" class="input-field w-32" min="1" />
                        </div>
                    </div>
                </template>

                <!-- Save button mobile -->
                <div class="sm:hidden">
                    <button @click="save" :disabled="form.processing" class="btn-primary w-full">
                        {{ form.processing ? 'Enregistrement...' : 'Enregistrer les paramètres' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
