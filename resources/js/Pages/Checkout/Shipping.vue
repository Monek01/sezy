<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    cart: Object,
    addresses: Array,
    pickupPoints: Array,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const defaultAddress = props.addresses?.find((a) => a.is_default) ?? props.addresses?.[0];

const form = useForm({
    full_name: user.value?.full_name ?? '',
    email: user.value?.email ?? '',
    phone: defaultAddress?.phone ?? '',
    delivery_method: 'home_delivery',
    city: defaultAddress?.city ?? '',
    district: defaultAddress?.district ?? '',
    address_line: defaultAddress?.address_line ?? '',
    landmark: defaultAddress?.landmark ?? '',
    pickup_point_id: '',
    customer_note: '',
});

function useAddress(address) {
    form.full_name = address.full_name;
    form.phone = address.phone;
    form.city = address.city;
    form.district = address.district;
    form.address_line = address.address_line;
    form.landmark = address.landmark;
}

function submit() {
    form.post(route('checkout.shipping.store'));
}

const subtotal = computed(() => props.cart.items.reduce((sum, item) => sum + item.unit_price * item.quantity, 0));
function formatPrice(value) {
    return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
}
</script>

<template>
    <ShopLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
            <!-- Stepper -->
            <div class="flex items-center gap-2 mb-8 text-sm font-medium">
                <span class="text-sezy-dark">1. Livraison</span>
                <span class="text-gray-300">—</span>
                <span class="text-gray-400">2. Paiement</span>
                <span class="text-gray-300">—</span>
                <span class="text-gray-400">3. Confirmation</span>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <form @submit.prevent="submit" class="lg:col-span-2 space-y-6">
                    <div v-if="addresses?.length" class="card p-4">
                        <p class="text-sm font-semibold mb-2">Adresses enregistrées</p>
                        <div class="grid sm:grid-cols-2 gap-2">
                            <button v-for="addr in addresses" :key="addr.id" type="button"
                                    @click="useAddress(addr)"
                                    class="text-left text-sm border border-gray-200 rounded-lg p-3 hover:border-sezy">
                                <p class="font-medium">{{ addr.label }}</p>
                                <p class="text-gray-500 text-xs">{{ addr.address_line }}, {{ addr.city }}</p>
                            </button>
                        </div>
                    </div>

                    <div class="card p-5 space-y-4">
                        <h2 class="font-bold">Vos informations</h2>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet *</label>
                                <input v-model="form.full_name" type="text" class="input-field" required />
                                <p v-if="form.errors.full_name" class="text-xs text-red-600 mt-1">{{ form.errors.full_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone *</label>
                                <input v-model="form.phone" type="tel" placeholder="+229 ..." class="input-field" required />
                                <p v-if="form.errors.phone" class="text-xs text-red-600 mt-1">{{ form.errors.phone }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input v-model="form.email" type="email" class="input-field" required />
                            <p v-if="form.errors.email" class="text-xs text-red-600 mt-1">{{ form.errors.email }}</p>
                        </div>
                    </div>

                    <div class="card p-5 space-y-4">
                        <h2 class="font-bold">Mode de livraison</h2>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <label class="border rounded-lg p-3 cursor-pointer" :class="form.delivery_method === 'home_delivery' ? 'border-sezy-dark bg-sezy-50' : 'border-gray-200'">
                                <input type="radio" value="home_delivery" v-model="form.delivery_method" class="mr-2" />
                                Livraison à domicile
                            </label>
                            <label class="border rounded-lg p-3 cursor-pointer" :class="form.delivery_method === 'click_and_collect' ? 'border-sezy-dark bg-sezy-50' : 'border-gray-200'">
                                <input type="radio" value="click_and_collect" v-model="form.delivery_method" class="mr-2" />
                                Click & Collect (gratuit)
                            </label>
                        </div>

                        <template v-if="form.delivery_method === 'home_delivery'">
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ville *</label>
                                    <input v-model="form.city" type="text" class="input-field" required />
                                    <p v-if="form.errors.city" class="text-xs text-red-600 mt-1">{{ form.errors.city }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Quartier</label>
                                    <input v-model="form.district" type="text" class="input-field" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Adresse complète *</label>
                                <textarea v-model="form.address_line" class="input-field" rows="2" required></textarea>
                                <p v-if="form.errors.address_line" class="text-xs text-red-600 mt-1">{{ form.errors.address_line }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Point de repère</label>
                                <input v-model="form.landmark" type="text" placeholder="Ex: près de la pharmacie..." class="input-field" />
                            </div>
                        </template>

                        <template v-else>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Point de retrait *</label>
                                <select v-model="form.pickup_point_id" class="input-field" required>
                                    <option value="" disabled>Choisir un point de retrait</option>
                                    <option v-for="p in pickupPoints" :key="p.id" :value="p.id">{{ p.name }} — {{ p.city }}</option>
                                </select>
                                <p v-if="form.errors.pickup_point_id" class="text-xs text-red-600 mt-1">{{ form.errors.pickup_point_id }}</p>
                            </div>
                        </template>
                    </div>

                    <div class="card p-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Note pour la commande (optionnel)</label>
                        <textarea v-model="form.customer_note" class="input-field" rows="2"></textarea>
                    </div>

                    <button type="submit" class="btn-primary w-full" :disabled="form.processing">
                        Continuer vers le paiement
                    </button>
                </form>

                <div class="card p-5 h-fit">
                    <h2 class="font-bold mb-4">Votre commande</h2>
                    <div class="space-y-3 mb-4 max-h-64 overflow-y-auto">
                        <div v-for="item in cart.items" :key="item.id" class="flex justify-between text-sm">
                            <span class="text-gray-600 truncate pr-2">{{ item.quantity }}× {{ item.product?.title }}</span>
                            <span class="font-medium shrink-0">{{ formatPrice(item.unit_price * item.quantity) }}</span>
                        </div>
                    </div>
                    <div class="border-t pt-3 flex justify-between font-bold">
                        <span>Sous-total</span>
                        <span class="text-sezy-dark">{{ formatPrice(subtotal) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>
